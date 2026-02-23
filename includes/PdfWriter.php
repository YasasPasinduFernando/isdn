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
    // cursor measured from top of page (in points)
    private $y = 0;
    private $x = 0;
    private $margin = 40;
    private $pageWidth = 595.28;
    private $pageHeight = 841.89;
    private $fontSize = 10;
    private $titleSize = 14;
    // header/footer text (simple left/center/right strings)
    private $header = ['left' => '', 'center' => '', 'right' => ''];
    private $footer = ['left' => '', 'center' => '', 'right' => ''];

    public function __construct()
    {
        // Start cursor at top margin
        $this->y = $this->margin;
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
        $this->y = $this->margin;
        $this->x = $this->margin;
        // Render header immediately on new page if present
        if (!empty($this->header['center']) || !empty($this->header['left']) || !empty($this->header['right'])) {
            // Save current y and x
            $prevY = $this->y;
            $prevX = $this->x;
            // Use title size for header center and smaller for left/right
            if (!empty($this->header['center'])) {
                $this->writeLine($this->header['center'], $this->titleSize);
            }
            if (!empty($this->header['left'])) {
                $this->writeText($this->header['left'], $this->fontSize);
            }
            if (!empty($this->header['right'])) {
                // right aligned header: attempt by moving x near right margin
                $this->x = $this->pageWidth - $this->margin - 150;
                $this->writeText($this->header['right'], $this->fontSize);
            }
            // restore x and y for body content (push a bit below header)
            $this->x = $prevX;
            $this->y = $prevY + ($this->titleSize * 1.5) + 6;
        }
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
        // PDF coordinate = pageHeight - y (y measured from top)
        $pdfY = $this->pageHeight - $this->y;
        $obj .= $this->x . " " . $pdfY . " Td\n";
        $obj .= "(" . $text . ") Tj\nET\n";
        // advance cursor down by approximate line height
        $this->y += $size * 1.6;
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
            // rectangle top-left should be at pdfY = pageHeight - y, but PDF rect expects lower-left, so compute lower-left y
            $pdfY = $this->pageHeight - $this->y;
            $lowerLeftY = $pdfY - $h;
            $obj .= sprintf("%.2f %.2f %.2f %.2f re S\n", $this->x, $lowerLeftY, $w, $h);
        }
        $obj .= "BT\n/F1 " . $this->fontSize . " Tf\n";
        // position text about 2pt right and vertically centered within cell
        $pdfTextY = $this->pageHeight - $this->y - ($h / 2) + ($this->fontSize / 2);
        $obj .= sprintf("%.2f %.2f Td\n", $this->x + 2, $pdfTextY);
        $obj .= "(" . substr($text, 0, 200) . ") Tj\nET\nq\n";
        $this->x += $w;
    }

    public function ln($h = 6)
    {
        $this->x = $this->margin;
        $this->y += $h;
    }

    public function checkPageBreak($need = 50)
    {
        // If cursor plus needed space goes beyond printable area, add a new page
        if ($this->y + $need > ($this->pageHeight - $this->margin)) {
            $this->finalizePage();
            $this->addPage();
        }
    }

    private function finalizePage()
    {
        if (!$this->currentPage) return;
        $idx = $this->currentPage - 1;
        // Render footer if provided
        if (!empty($this->footer['left']) || !empty($this->footer['center']) || !empty($this->footer['right'])) {
            // Temporarily set positions to bottom margin
            $prevY = $this->y;
            $prevX = $this->x;
            // place footer about margin from bottom
            $this->y = $this->pageHeight - $this->margin - 20;
            $this->x = $this->margin;
            if (!empty($this->footer['left'])) $this->writeText($this->footer['left'], $this->fontSize);
            if (!empty($this->footer['center'])) {
                // center footer roughly
                $this->x = ($this->pageWidth / 2) - 100;
                $this->writeText($this->footer['center'], $this->fontSize);
            }
            if (!empty($this->footer['right'])) {
                $this->x = $this->pageWidth - $this->margin - 150;
                $this->writeText($this->footer['right'], $this->fontSize);
            }
            // restore
            $this->x = $prevX;
            $this->y = $prevY;
        }
        $this->objects[$idx] .= "Q\n";
    }

    /**
     * Set header text. Each arg can be empty string.
     */
    public function setHeader($left = '', $center = '', $right = '')
    {
        $this->header = ['left' => $left, 'center' => $center, 'right' => $right];
    }

    /**
     * Set footer text. Each arg can be empty string.
     */
    public function setFooter($left = '', $center = '', $right = '')
    {
        $this->footer = ['left' => $left, 'center' => $center, 'right' => $right];
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
