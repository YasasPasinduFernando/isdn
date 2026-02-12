<?php
/**
 * Minimal PDF writer for report export (no external dependencies).
 * Outputs valid PDF 1.4 with Helvetica font for tables and titles.
 */
class PdfWriter
{
    private $buffer = '';
    private $objects = [];
    private $pages = [];
    private $currentPage = null;
    private $y = 0;
    private $x = 0;
    private $margin = 40;
    private $pageWidth = 595.28;
    private $pageHeight = 841.89;
    private $fontSize = 10;
    private $titleSize = 14;

    public function __construct()
    {
        $this->y = $this->pageHeight - $this->margin;
        $this->x = $this->margin;
    }

    private function addObject($content)
    {
        $this->objects[] = $content;
        return count($this->objects);
    }

    private function escape($s)
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    public function addPage()
    {
        $content = "q\nBT\n/F1 " . $this->fontSize . " Tf\n";
        $this->currentPage = $this->addObject($content);
        $this->pages[] = $this->currentPage;
        $this->y = $this->pageHeight - $this->margin;
        $this->x = $this->margin;
    }

    public function setFontSize($size)
    {
        $this->fontSize = $size;
    }

    public function setTitleSize($size)
    {
        $this->titleSize = $size;
    }

    public function writeText($text, $size = null)
    {
        $size = $size ?? $this->fontSize;
        $text = $this->escape($text);
        $idx = $this->currentPage ?: $this->addObject('');
        $obj = &$this->objects[$idx - 1];
        $obj .= "/F1 " . $size . " Tf\n";
        $obj .= $this->x . " " . ($this->pageHeight - $this->y) . " Td\n";
        $obj .= "(" . $text . ") Tj\nET\n";
        $this->y += $size * 1.5;
    }

    public function writeLine($text, $size = null)
    {
        $this->writeText($text, $size);
        $this->y += 4;
    }

    public function writeTitle($text)
    {
        $this->writeLine($text, $this->titleSize);
        $this->y += 6;
    }

    public function cell($w, $h, $text, $border = 0)
    {
        $text = $this->escape((string) $text);
        $idx = $this->currentPage ?: $this->addObject('');
        $obj = &$this->objects[$idx - 1];
        $obj .= "q\n";
        if ($border) {
            $obj .= "0.7 w\n";
            $obj .= sprintf("%.2f %.2f %.2f %.2f re S\n", $this->x, $this->pageHeight - $this->y, $w, $h);
        }
        $obj .= "BT\n/F1 " . $this->fontSize . " Tf\n";
        $obj .= sprintf("%.2f %.2f Td\n", $this->x + 2, $this->pageHeight - $this->y - $h + 2);
        $obj .= "(" . substr($text, 0, 50) . ") Tj\nET\nq\n";
        $this->x += $w;
    }

    public function ln($h = 6)
    {
        $this->x = $this->margin;
        $this->y += $h;
    }

    public function checkPageBreak($need = 50)
    {
        if ($this->y + $need > $this->pageHeight - $this->margin) {
            $this->finalizePage();
            $this->addPage();
        }
    }

    private function finalizePage()
    {
        if (!$this->currentPage) return;
        $idx = $this->currentPage - 1;
        $this->objects[$idx] .= "Q\n";
    }

    public function output($filename = 'report.pdf')
    {
        $this->finalizePage();

        $n = count($this->objects);
        $fontId = $n + 1;
        $pagesId = $n + 2;
        $catalogId = $n + 3;

        $out = "%PDF-1.4\n";
        $offsets = [];

        // Font
        $out .= "\n" . ($fontId) . " 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $offsets[$fontId] = strlen($out);

        // Pages array
        $kids = implode(' ', array_map(function ($p) { return $p . " 0 R"; }, $this->pages));
        $out .= "\n" . ($pagesId) . " 0 obj\n<< /Type /Pages /Kids [$kids] /Count " . count($this->pages) . " >>\nendobj\n";
        $offsets[$pagesId] = strlen($out);

        // Page objects
        $resources = "<< /Font << /F1 " . $fontId . " 0 R >> >>";
        foreach ($this->pages as $i => $pageNum) {
            $objNum = $pageNum;
            $content = $this->objects[$pageNum - 1];
            $stream = gzcompress($content);
            $len = strlen($stream);
            $out .= "\n" . $objNum . " 0 obj\n<< /Type /Page /Parent " . $pagesId . " 0 R /MediaBox [0 0 " . $this->pageWidth . " " . $this->pageHeight . "] /Resources " . $resources . " /Contents " . ($n + 4 + $i) . " 0 R >>\nendobj\n";
            $offsets[$objNum] = strlen($out);
            $streamObjs[] = ['id' => $n + 4 + $i, 'len' => $len, 'data' => $stream];
        }

        // Content streams
        foreach ($streamObjs as $s) {
            $out .= "\n" . $s['id'] . " 0 obj\n<< /Length " . $s['len'] . " /Filter /FlateDecode >>\nstream\n" . $s['data'] . "\nendstream\nendobj\n";
        }

        // Catalog
        $out .= "\n" . $catalogId . " 0 obj\n<< /Type /Catalog /Pages " . $pagesId . " 0 R >>\nendobj\n";
        $xref = "xref\n0 " . ($catalogId + 1) . "\n0000000000 65535 f \n";
        $start = strlen($out) + 24;
        for ($i = 1; $i <= $n; $i++) {
            if (isset($offsets[$i])) $xref .= sprintf("%010d 00000 n \n", $offsets[$i]);
            else $xref .= sprintf("%010d 00000 n \n", $start);
        }
        foreach ($streamObjs as $s) {
            $xref .= sprintf("%010d 00000 n \n", strlen($out));
            $out .= "\n" . $s['id'] . " 0 obj\n<< /Length " . $s['len'] . " /Filter /FlateDecode >>\nstream\n" . $s['data'] . "\nendstream\nendobj\n";
        }
        $xref .= sprintf("%010d 00000 n \n", strlen($out));
        $out .= "\n" . $catalogId . " 0 obj\n<< /Type /Catalog /Pages " . $pagesId . " 0 R >>\nendobj\n";

        $xrefOffset = strlen($out);
        $out .= $xref . "trailer\n<< /Size " . ($catalogId + 1) . " /Root " . $catalogId . " 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0');
        echo $out;
        exit;
    }
}
