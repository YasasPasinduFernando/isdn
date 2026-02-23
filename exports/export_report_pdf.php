<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/fpdf.php';
require_once __DIR__ . '/../models/ProductStock.php';
require_once __DIR__ . '/../models/StockTransfer.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$report = $_GET['report'] ?? 'current_stock';
$rdc = isset($_GET['rdc_id']) && is_numeric($_GET['rdc_id']) ? (int)$_GET['rdc_id'] : null;
$date = $_GET['date'] ?? '30';
// Optional filters from client
$category = isset($_GET['category']) ? trim($_GET['category']) : null;
$status = isset($_GET['status']) ? trim($_GET['status']) : null;

$productStock = new ProductStock($pdo);
$stockTransfer = new StockTransfer($pdo);

// Use FPDF for robust PDF generation (header/footer/pagination handled by subclass)
$generatedBy = ($_SESSION['username'] ?? ($_SESSION['name'] ?? 'System'));
$generatedAt = date('Y-m-d H:i:s');

if (!class_exists('ReportPdf')) {
    class ReportPdf extends FPDF
    {
        private $generatedAt = '';
        private $filterSummary = '';
        private $reportTitle = '';

        public function setMeta(string $generatedAt, string $filterSummary, string $reportTitle = ''): void
        {
            $this->generatedAt = $generatedAt;
            $this->filterSummary = $filterSummary;
            $this->reportTitle = $reportTitle;
        }

        public function Header(): void
        {
            $this->SetFillColor(13, 148, 136);
            $this->Rect(0, 0, $this->GetPageWidth(), 22, 'F');

            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Helvetica', 'B', 15);
            $this->SetXY(10, 6);
            $title = $this->reportTitle ?: 'ISDN Report';
            $this->Cell(0, 6, strtoupper($title), 0, 1, 'L');

            $this->SetFont('Helvetica', '', 8);
            $this->SetX(10);
            $this->Cell(0, 4, 'Generated: ' . $this->generatedAt, 0, 1, 'L');

            $this->SetTextColor(55, 65, 81);
            $this->SetFont('Helvetica', '', 8);
            $this->SetXY(10, 24);
            $this->Cell($this->GetPageWidth() - 20, 4, 'Filters: ' . $this->filterSummary, 0, 1, 'L');
            $this->Ln(3);
        }

        public function Footer(): void
        {
            $this->SetY(-10);
            $this->SetDrawColor(226, 232, 240);
            $this->Line(10, $this->GetY(), $this->GetPageWidth() - 10, $this->GetY());

            $this->SetY(-7);
            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(107, 114, 128);
            $this->Cell(0, 4, 'ISDN Internal Use Only', 0, 0, 'L');
            $this->Cell(0, 4, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
        }
    }
}

$pdf = new ReportPdf('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetMargins(10, 30, 10);
$pdf->SetAutoPageBreak(true, 14);

$reportTitle = ucfirst(str_replace('_', ' ', $report));
if ($rdc) {
    $stmt = $pdo->prepare('SELECT rdc_name FROM rdcs WHERE rdc_id = :id');
    $stmt->execute(['id' => $rdc]);
    $rr = $stmt->fetch(PDO::FETCH_ASSOC);
    $reportTitle .= ' - ' . ($rr['rdc_name'] ?? ('RDC ' . $rdc));
}
$filterPartsForHeader = [];
if (!empty($category)) $filterPartsForHeader[] = 'Category: ' . $category;
if (!empty($status)) $filterPartsForHeader[] = 'Status: ' . $status;
$filterSummary = empty($filterPartsForHeader) ? 'All' : implode(' | ', $filterPartsForHeader);
$pdf->setMeta($generatedAt, $filterSummary, $reportTitle);
$pdf->AddPage();

// helper to ensure there's enough space on the page for a block of height (mm)
function ensurePageSpace($pdf, $needed = 30) {
    // conservative bottom threshold (A4 height 297mm) minus margins/footer
    $threshold = 260;
    if ($pdf->GetY() + $needed > $threshold) {
        $pdf->AddPage();
    }
}

// helper to scale nominal column widths to printable page width (respecting margins)
function scaleWidths($pdf, array $nominalWidths): array {
    // Get available printable width (page width minus left and right margins)
    $pageW = $pdf->GetPageWidth();
    // We avoid accessing protected properties like lMargin/rMargin directly.
    // The margins are set above via SetMargins(10, 30, 10) so default to 10mm left/right.
    // If a different margin is required, update these defaults or pass explicit values.
    $left = 10;
    $right = 10;
    $available = max(10, $pageW - $left - $right);
    $sum = array_sum($nominalWidths);
    if ($sum <= 0) return $nominalWidths;
    $scale = $available / $sum;
    return array_map(fn($w) => $w * $scale, $nominalWidths);
}

if ($report === 'current_stock') {
    $title = 'Current Stock Report';
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 8, $title, 0, 1, 'L');
    $pdf->Ln(4);
    // header row (scaled to printable width)
    $pdf->SetFont('Helvetica', 'B', 10);
    $w = scaleWidths($pdf, [40, 80, 50, 30]);
    $pdf->Cell($w[0], 8, 'RDC', 1, 0, 'L');
    $pdf->Cell($w[1], 8, 'Product', 1, 0, 'L');
    $pdf->Cell($w[2], 8, 'Category', 1, 0, 'L');
    $pdf->Cell($w[3], 8, 'Current', 1, 1, 'R');
    $pdf->SetFont('Helvetica', '', 9);
    // Helper to apply category/status filters to a row
    $filterRow = function($row) use ($category, $status) {
        if ($category && $category !== 'all') {
            if (!isset($row['category']) || strcasecmp(trim($row['category']), $category) !== 0) return false;
        }
        if ($status && $status !== 'all') {
            if (!isset($row['status']) || strcasecmp(trim($row['status']), $status) !== 0) return false;
        }
        return true;
    };

    if ($rdc) {
        $rows = $productStock->getStocksByRdc($rdc);
        $stmt = $pdo->prepare('SELECT rdc_name FROM rdcs WHERE rdc_id = :id'); $stmt->execute(['id'=>$rdc]); $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $rdcName = $r['rdc_name'] ?? ('RDC ' . $rdc);
        foreach ($rows as $row) {
            if (!$filterRow($row)) continue;
                ensurePageSpace($pdf, 10);
                $pdf->Cell($w[0], 6, $rdcName, 1, 0, 'L');
                $pdf->Cell($w[1], 6, substr((string)($row['product_name'] ?? ''), 0, 40), 1, 0, 'L');
                $pdf->Cell($w[2], 6, substr((string)($row['category'] ?? ''), 0, 25), 1, 0, 'L');
                $pdf->Cell($w[3], 6, (string)$row['current_stock'], 1, 1, 'R');
        }
    } else {
        $stmt = $pdo->query('SELECT rdc_id, rdc_name FROM rdcs');
        $rds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rds as $R) {
            $rows = $productStock->getStocksByRdc((int)$R['rdc_id']);
            foreach ($rows as $row) {
                if (!$filterRow($row)) continue;
                ensurePageSpace($pdf, 10);
                $pdf->Cell($w[0], 6, $R['rdc_name'], 1, 0, 'L');
                $pdf->Cell($w[1], 6, substr((string)($row['product_name'] ?? ''), 0, 40), 1, 0, 'L');
                $pdf->Cell($w[2], 6, substr((string)($row['category'] ?? ''), 0, 25), 1, 0, 'L');
                $pdf->Cell($w[3], 6, (string)$row['current_stock'], 1, 1, 'R');
            }
        }
    }
    $filename = 'current_stock' . ($rdc ? '-rdc' . $rdc : '') . '-' . date('Ymd-His') . '.pdf';
    $pdf->Output('D', $filename);
    exit;
}

if ($report === 'low_stock_alerts') {
    $title = 'Low Stock Alerts Report';
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 8, $title, 0, 1, 'L');
    $pdf->Ln(4);
    $pdf->SetFont('Helvetica', 'B', 10);
    $w = scaleWidths($pdf, [40, 80, 40, 20, 20, 30, 30]);
    $pdf->Cell($w[0], 8, 'RDC', 1, 0, 'L');
    $pdf->Cell($w[1], 8, 'Product', 1, 0, 'L');
    $pdf->Cell($w[2], 8, 'Category', 1, 0, 'L');
    $pdf->Cell($w[3], 8, 'Current', 1, 0, 'R');
    $pdf->Cell($w[4], 8, 'Minimum', 1, 0, 'R');
    $pdf->Cell($w[5], 8, 'Shortage', 1, 0, 'R');
    $pdf->Cell($w[6], 8, 'Priority', 1, 1, 'C');
    $pdf->SetFont('Helvetica', '', 9);

    // statuses considered low
    $lowStatuses = ['LOW', 'CRITICAL', 'OUT_OF_STOCK'];

    // gather rdcs to iterate
    $rdcRows = [];
    if ($rdc) {
        $stmt = $pdo->prepare('SELECT rdc_id, rdc_name FROM rdcs WHERE rdc_id = :id');
        $stmt->execute(['id' => $rdc]);
        $rinfo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rinfo) $rdcRows[] = $rinfo;
    } else {
        $stmt = $pdo->query('SELECT rdc_id, rdc_name FROM rdcs');
        $rdcRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($rdcRows as $R) {
        $rows = $productStock->getStocksByRdc((int)$R['rdc_id']);
        foreach ($rows as $row) {
            // apply optional category filter
            if (!empty($category) && $category !== 'all') {
                if (!isset($row['category']) || strcasecmp(trim($row['category']), $category) !== 0) continue;
            }
            // apply optional status filter (if user explicitly selected a status like LOW/CRITICAL)
            if (!empty($status) && $status !== 'all') {
                if (!isset($row['status']) || strcasecmp(trim($row['status']), $status) !== 0) continue;
            }

            // only include rows with low statuses
            $rstatus = isset($row['status']) ? strtoupper(trim($row['status'])) : '';
            if (!in_array($rstatus, $lowStatuses, true)) continue;

            ensurePageSpace($pdf, 10);
            $shortage = '';
            $min = isset($row['minimum_level']) ? (int)$row['minimum_level'] : 0;
            $cur = isset($row['current_stock']) ? (int)$row['current_stock'] : 0;
            if ($min > $cur) $shortage = (string)($min - $cur);

            $pdf->Cell($w[0], 6, $R['rdc_name'], 1, 0, 'L');
            $pdf->Cell($w[1], 6, substr((string)($row['product_name'] ?? ''), 0, 40), 1, 0, 'L');
            $pdf->Cell($w[2], 6, substr((string)($row['category'] ?? ''), 0, 20), 1, 0, 'L');
            $pdf->Cell($w[3], 6, (string)$cur, 1, 0, 'R');
            $pdf->Cell($w[4], 6, (string)$min, 1, 0, 'R');
            $pdf->Cell($w[5], 6, $shortage, 1, 0, 'R');
            $pdf->Cell($w[6], 6, $rstatus, 1, 1, 'C');
        }
    }

    $filename = 'low_stock_alerts' . ($rdc ? '-rdc' . $rdc : '') . ($category ? '-cat' . preg_replace('/[^A-Za-z0-9]/','', $category) : '') . ($status ? '-status' . preg_replace('/[^A-Za-z0-9]/','', $status) : '') . '-' . date('Ymd-His') . '.pdf';
    $pdf->Output('D', $filename);
    exit;
}

if ($report === 'transfer_summary') {
    $title = 'Transfer Summary Report';
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 8, $title, 0, 1, 'L');
    $pdf->Ln(4);
    $pdf->SetFont('Helvetica', 'B', 10);
    $w = scaleWidths($pdf, [40, 40, 70, 30]);
    $pdf->Cell($w[0], 8, 'Transfer #', 1, 0, 'L');
    $pdf->Cell($w[1], 8, 'Date', 1, 0, 'L');
    $pdf->Cell($w[2], 8, 'From -> To', 1, 0, 'L');
    $pdf->Cell($w[3], 8, 'Items', 1, 1, 'R');
    $pdf->SetFont('Helvetica', '', 9);

    if ($rdc) {
        $rows = $stockTransfer->getTransfersForRdc($rdc, 1000);
    } else {
        $rows = [];
        $stmt = $pdo->query('SELECT rdc_id FROM rdcs');
        $rds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $seen = [];
        foreach ($rds as $R) {
            $res = $stockTransfer->getTransfersForRdc((int)$R['rdc_id'], 1000);
            foreach ($res as $rr) {
                if (!isset($seen[$rr['transfer_id']])) { $seen[$rr['transfer_id']] = true; $rows[] = $rr; }
            }
        }
    }
    // apply date filter
    if ($date !== 'all' && is_numeric($date)) {
        $cut = strtotime("-{$date} days");
        $rows = array_filter($rows, fn($r)=> strtotime($r['requested_date']) >= $cut);
    }

    // Optionally apply status filter to transfers if provided
    if ($status && $status !== 'all') {
        $rows = array_filter($rows, fn($r) => (isset($r['status']) && strcasecmp($r['status'], $status) === 0));
    }

    foreach ($rows as $r) {
        ensurePageSpace($pdf, 10);
        $pdf->Cell($w[0], 6, $r['transfer_number'], 1, 0, 'L');
        $pdf->Cell($w[1], 6, $r['requested_date'], 1, 0, 'L');
        $pdf->Cell($w[2], 6, substr($r['source_rdc'] . ' -> ' . $r['destination_rdc'], 0, 40), 1, 0, 'L');
        $pdf->Cell($w[3], 6, (string)$r['total_items'], 1, 1, 'R');
    }
    $filename = 'transfer_summary' . ($rdc ? '-rdc' . $rdc : '') . ($status ? '-status' . preg_replace('/[^A-Za-z0-9]/','', $status) : '') . '-' . date('Ymd-His') . '.pdf';
    $pdf->Output('D', $filename);
    exit;
}

if ($report === 'stock_valuation') {
    $title = 'Stock Valuation Report';
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 8, $title, 0, 1, 'L');
    $pdf->Ln(4);
    $pdf->SetFont('Helvetica', 'B', 10);
    $w = scaleWidths($pdf, [40, 80, 20, 30, 30]);
    $pdf->Cell($w[0], 8, 'RDC', 1, 0, 'L');
    $pdf->Cell($w[1], 8, 'Product', 1, 0, 'L');
    $pdf->Cell($w[2], 8, 'Qty', 1, 0, 'R');
    $pdf->Cell($w[3], 8, 'Unit Price', 1, 0, 'R');
    $pdf->Cell($w[4], 8, 'Total Value', 1, 1, 'R');
    $pdf->SetFont('Helvetica', '', 9);

    // iterate either single RDC or all
    $rds = [];
    if ($rdc) {
        $stmt = $pdo->prepare('SELECT rdc_id, rdc_name FROM rdcs WHERE rdc_id = :id');
        $stmt->execute(['id' => $rdc]);
        $rinfo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rinfo) $rds[] = $rinfo;
    } else {
        $stmt = $pdo->query('SELECT rdc_id, rdc_name FROM rdcs');
        $rds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $grandUnits = 0;
    $grandValue = 0.0;

    foreach ($rds as $R) {
        $rows = $productStock->getStocksByRdc((int)$R['rdc_id']);
        foreach ($rows as $row) {
            // apply optional category filter for valuation
            if ($category && $category !== 'all') {
                if (!isset($row['category']) || strcasecmp(trim($row['category']), $category) !== 0) continue;
            }
            ensurePageSpace($pdf, 10);
            $qty = (int)($row['current_stock'] ?? 0);
            $unit = (float)($row['unit_price'] ?? 0);
            $val = $qty * $unit;
            $grandUnits += $qty;
            $grandValue += $val;

            $pdf->Cell($w[0], 6, $R['rdc_name'], 1, 0, 'L');
            $pdf->Cell($w[1], 6, substr((string)($row['product_name'] ?? ''), 0, 40), 1, 0, 'L');
            $pdf->Cell($w[2], 6, (string)$qty, 1, 0, 'R');
            $pdf->Cell($w[3], 6, number_format($unit, 2), 1, 0, 'R');
            $pdf->Cell($w[4], 6, number_format($val, 2), 1, 1, 'R');
        }
    }

    // Grand total row
    ensurePageSpace($pdf, 12);
    $pdf->SetFont('Helvetica', 'B', 10);
    // merge first two columns for label
    $labelWidth = $w[0] + $w[1];
    $pdf->Cell($labelWidth, 8, 'GRAND TOTAL', 1, 0, 'R');
    $pdf->Cell($w[2], 8, (string)$grandUnits, 1, 0, 'R');
    $pdf->Cell($w[3], 8, '', 1, 0, 'R');
    $pdf->Cell($w[4], 8, number_format($grandValue, 2), 1, 1, 'R');
    $pdf->SetFont('Helvetica', '', 9);

    $filename = 'stock_valuation' . ($rdc ? '-rdc' . $rdc : '') . ($category ? '-cat' . preg_replace('/[^A-Za-z0-9]/','', $category) : '') . '-' . date('Ymd-His') . '.pdf';
    $pdf->Output('D', $filename);
    exit;
}

// fallback
$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(0, 8, 'No data for selected report.', 0, 1, 'L');
$pdf->Output('D', 'report-' . date('Ymd-His') . '.pdf');

?>
