<?php
/**
 * RDC-wise Delivery Efficiency Report
 *
 * Access: head_office_manager, system_admin only
 * Features: KPI summary, RDC comparison table, bar chart (Chart.js), CSV & PDF export
 *
 * Architecture:
 *   View (this file) → DeliveryReport model → MySQL aggregation
 *   Filters applied via GET parameters with prepared statements
 */
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/DeliveryReport.php';

// ── Role-based access control ────────────────────────────────
$allowedRoles = [USER_ROLE_HEAD_OFFICE_MANAGER, USER_ROLE_SYSTEM_ADMIN];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
    flash_message('Access denied. This report requires Head Office Manager or System Admin privileges.', 'error');
    redirect('/index.php?page=login');
}

// ── Initialize model and ensure indexes ──────────────────────
$report = new DeliveryReport($pdo);
$report->ensureIndexes(); // Idempotent index creation for performance

// ── Sanitize filters ─────────────────────────────────────────
$filters = [];
if (!empty($_GET['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}
if (!empty($_GET['rdc_id']) && is_numeric($_GET['rdc_id'])) {
    $filters['rdc_id'] = (int) $_GET['rdc_id'];
}
if (!empty($_GET['status']) && in_array($_GET['status'], ['completed', 'pending', 'on_time', 'delayed'])) {
    $filters['status'] = $_GET['status'];
}

// ── Fetch data ───────────────────────────────────────────────
$error = null;
try {
    $rdcData = $report->getRdcEfficiency($filters);
    $summary = $report->getOverallSummary($filters);
    $details = $report->getDeliveryDetails($filters, 50);
    $allRdcs = $report->getAllRdcs();
} catch (Exception $e) {
    $error = $e->getMessage();
    $rdcData = $details = $allRdcs = [];
    $summary = [
        'total_deliveries' => 0,
        'completed' => 0,
        'on_time' => 0,
        'delayed' => 0,
        'pending' => 0,
        'overall_efficiency' => 0,
        'avg_hours' => 0
    ];
}

// ── PDF Export (must run before any HTML output) ─────────────
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    require_once __DIR__ . '/../../includes/fpdf.php';
    if (!class_exists('DeliveryEfficiencyPdf')) {
        class DeliveryEfficiencyPdf extends FPDF
        {
            private string $generatedAt = '';
            private string $filterSummary = 'All deliveries';

            public function setMeta(string $generatedAt, string $filterSummary): void
            {
                $this->generatedAt = $generatedAt;
                $this->filterSummary = $filterSummary;
            }

            public function Header(): void
            {
                $this->SetFillColor(13, 148, 136);
                $this->Rect(0, 0, $this->GetPageWidth(), 22, 'F');

                $this->SetTextColor(255, 255, 255);
                $this->SetFont('Helvetica', 'B', 15);
                $this->SetXY(10, 6);
                $this->Cell(0, 6, 'ISDN DELIVERY EFFICIENCY REPORT', 0, 1, 'L');

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

    $rdcLookup = [];
    foreach ($allRdcs as $rdc) {
        $rdcLookup[(int) $rdc['rdc_id']] = $rdc['rdc_name'];
    }

    $statusLabels = [
        'completed' => 'Completed',
        'pending' => 'Pending',
        'on_time' => 'On-time',
        'delayed' => 'Delayed',
    ];

    $filterParts = [];
    if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
        $from = !empty($filters['start_date']) ? $filters['start_date'] : 'Any';
        $to = !empty($filters['end_date']) ? $filters['end_date'] : 'Any';
        $filterParts[] = 'Date: ' . $from . ' to ' . $to;
    }
    if (!empty($filters['rdc_id'])) {
        $rdcName = $rdcLookup[(int) $filters['rdc_id']] ?? ('RDC #' . (int) $filters['rdc_id']);
        $filterParts[] = 'RDC: ' . $rdcName;
    }
    if (!empty($filters['status'])) {
        $filterParts[] = 'Status: ' . ($statusLabels[$filters['status']] ?? $filters['status']);
    }
    $filterSummary = empty($filterParts) ? 'All deliveries' : implode(' | ', $filterParts);
    $generatedAt = date('Y-m-d H:i:s');

    $num = static function ($value): float {
        return is_numeric($value) ? (float) $value : 0.0;
    };
    $fmtInt = static function ($value): string {
        return number_format((int) round(is_numeric($value) ? (float) $value : 0.0));
    };
    $fmtPct = static function ($value): string {
        return number_format(is_numeric($value) ? (float) $value : 0.0, 1) . '%';
    };
    $fmtHours = static function ($value): string {
        return is_numeric($value) ? number_format((float) $value, 1) . ' h' : '-';
    };

    $rankedRdcs = $rdcData;
    usort($rankedRdcs, static function ($a, $b) use ($num) {
        return $num($b['efficiency_pct'] ?? 0) <=> $num($a['efficiency_pct'] ?? 0);
    });
    $bestRdc = $rankedRdcs[0] ?? null;
    $worstRdc = !empty($rankedRdcs) ? $rankedRdcs[count($rankedRdcs) - 1] : null;

    $pdf = new DeliveryEfficiencyPdf('L', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->SetMargins(10, 30, 10);
    $pdf->SetAutoPageBreak(true, 14);
    $pdf->setMeta($generatedAt, $filterSummary);
    $pdf->AddPage();

    $sectionTitle = static function (FPDF $pdfDoc, string $title): void {
        $pdfDoc->SetTextColor(30, 41, 59);
        $pdfDoc->SetFont('Helvetica', 'B', 11);
        $pdfDoc->Cell(0, 7, $title, 0, 1, 'L');
    };

    $drawKpiCard = static function (FPDF $pdfDoc, float $x, float $y, float $w, float $h, string $label, string $value, array $color): void {
        $pdfDoc->SetFillColor($color[0], $color[1], $color[2]);
        $pdfDoc->SetDrawColor(226, 232, 240);
        $pdfDoc->Rect($x, $y, $w, $h, 'DF');
        $pdfDoc->SetTextColor(255, 255, 255);
        $pdfDoc->SetXY($x + 3, $y + 3);
        $pdfDoc->SetFont('Helvetica', '', 8);
        $pdfDoc->Cell($w - 6, 4, $label, 0, 2, 'L');
        $pdfDoc->SetFont('Helvetica', 'B', 15);
        $pdfDoc->Cell($w - 6, 8, $value, 0, 0, 'L');
    };

    $drawKpiCard($pdf, 10, 34, 89, 17, 'TOTAL DELIVERIES', $fmtInt($summary['total_deliveries'] ?? 0), [15, 23, 42]);
    $drawKpiCard($pdf, 104, 34, 89, 17, 'ON-TIME DELIVERIES', $fmtInt($summary['on_time'] ?? 0), [16, 185, 129]);
    $drawKpiCard($pdf, 198, 34, 89, 17, 'DELAYED DELIVERIES', $fmtInt($summary['delayed'] ?? 0), [239, 68, 68]);
    $drawKpiCard($pdf, 10, 54, 89, 17, 'PENDING DELIVERIES', $fmtInt($summary['pending'] ?? 0), [245, 158, 11]);
    $drawKpiCard($pdf, 104, 54, 89, 17, 'OVERALL EFFICIENCY', $fmtPct($summary['overall_efficiency'] ?? 0), [59, 130, 246]);
    $drawKpiCard($pdf, 198, 54, 89, 17, 'AVG DELIVERY TIME', $fmtHours($summary['avg_hours'] ?? 0), [99, 102, 241]);

    $pdf->SetY(76);
    $sectionTitle($pdf, 'Performance Snapshot');
    $pdf->SetDrawColor(226, 232, 240);
    $pdf->SetFillColor(248, 250, 252);

    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetTextColor(30, 41, 59);

    $pdf->Rect(10, $pdf->GetY(), 136, 18, 'DF');
    $pdf->Rect(151, $pdf->GetY(), 136, 18, 'DF');

    $bestText = 'No RDC data available';
    if ($bestRdc) {
        $bestText = sprintf(
            '%s (%s)  |  Efficiency: %s  |  On-time: %s  |  Avg: %s',
            substr((string) ($bestRdc['rdc_name'] ?? 'Unknown'), 0, 32),
            (string) ($bestRdc['rdc_code'] ?? '-'),
            $fmtPct($bestRdc['efficiency_pct'] ?? 0),
            $fmtInt($bestRdc['on_time'] ?? 0),
            $fmtHours($bestRdc['avg_delivery_hours'] ?? null)
        );
    }

    $worstText = 'No RDC data available';
    if ($worstRdc) {
        $worstText = sprintf(
            '%s (%s)  |  Efficiency: %s  |  Delayed: %s  |  Pending: %s',
            substr((string) ($worstRdc['rdc_name'] ?? 'Unknown'), 0, 32),
            (string) ($worstRdc['rdc_code'] ?? '-'),
            $fmtPct($worstRdc['efficiency_pct'] ?? 0),
            $fmtInt($worstRdc['delayed'] ?? 0),
            $fmtInt($worstRdc['pending'] ?? 0)
        );
    }

    $pdf->SetXY(13, $pdf->GetY() + 3);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetTextColor(5, 150, 105);
    $pdf->Cell(28, 5, 'Top Performer', 0, 0, 'L');
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetTextColor(51, 65, 85);
    $pdf->Cell(104, 5, $bestText, 0, 0, 'L');

    $pdf->SetXY(154, $pdf->GetY());
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(34, 5, 'Needs Attention', 0, 0, 'L');
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetTextColor(51, 65, 85);
    $pdf->Cell(96, 5, $worstText, 0, 1, 'L');

    $pdf->Ln(12);
    $sectionTitle($pdf, 'RDC Comparison');

    $rdcWidths = [68, 21, 21, 21, 21, 28, 26, 20, 25];
    $rdcHeaders = ['RDC', 'Total', 'Completed', 'On-time', 'Delayed', 'Pending', 'Efficiency', 'Avg Hrs', 'Code'];

    $pdf->SetFillColor(15, 23, 42);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(226, 232, 240);
    $pdf->SetFont('Helvetica', 'B', 8);
    foreach ($rdcHeaders as $idx => $header) {
        $pdf->Cell($rdcWidths[$idx], 7, $header, 1, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 8);
    $rowIndex = 0;
    foreach ($rdcData as $r) {
        $fill = ($rowIndex % 2 === 0);
        if ($fill) {
            $pdf->SetFillColor(248, 250, 252);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }
        $pdf->SetTextColor(31, 41, 55);
        $pdf->Cell($rdcWidths[0], 6, substr((string) ($r['rdc_name'] ?? ''), 0, 34), 1, 0, 'L', true);
        $pdf->Cell($rdcWidths[1], 6, $fmtInt($r['total_deliveries'] ?? 0), 1, 0, 'R', true);
        $pdf->Cell($rdcWidths[2], 6, $fmtInt($r['completed'] ?? 0), 1, 0, 'R', true);
        $pdf->Cell($rdcWidths[3], 6, $fmtInt($r['on_time'] ?? 0), 1, 0, 'R', true);
        $pdf->Cell($rdcWidths[4], 6, $fmtInt($r['delayed'] ?? 0), 1, 0, 'R', true);
        $pdf->Cell($rdcWidths[5], 6, $fmtInt($r['pending'] ?? 0), 1, 0, 'R', true);

        $efficiency = $num($r['efficiency_pct'] ?? 0);
        if ($efficiency >= 80) {
            $pdf->SetTextColor(5, 150, 105);
        } elseif ($efficiency >= 50) {
            $pdf->SetTextColor(217, 119, 6);
        } else {
            $pdf->SetTextColor(220, 38, 38);
        }
        $pdf->Cell($rdcWidths[6], 6, $fmtPct($r['efficiency_pct'] ?? 0), 1, 0, 'R', true);

        $pdf->SetTextColor(31, 41, 55);
        $pdf->Cell($rdcWidths[7], 6, $fmtHours($r['avg_delivery_hours'] ?? null), 1, 0, 'R', true);
        $pdf->Cell($rdcWidths[8], 6, (string) ($r['rdc_code'] ?? '-'), 1, 1, 'C', true);
        $rowIndex++;
    }

    if (empty($rdcData)) {
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(array_sum($rdcWidths), 8, 'No RDC data available for selected filters.', 1, 1, 'C', true);
    }

    $pdf->Ln(5);
    $sectionTitle($pdf, 'Delivery Records (Sample)');

    $detailWidths = [28, 58, 35, 43, 43, 33, 25];
    $drawDetailHeader = static function (FPDF $pdfDoc, array $widths): void {
        $pdfDoc->SetFillColor(30, 41, 59);
        $pdfDoc->SetTextColor(255, 255, 255);
        $pdfDoc->SetFont('Helvetica', 'B', 8);
        $pdfDoc->Cell($widths[0], 7, 'Order #', 1, 0, 'C', true);
        $pdfDoc->Cell($widths[1], 7, 'RDC', 1, 0, 'C', true);
        $pdfDoc->Cell($widths[2], 7, 'Driver', 1, 0, 'C', true);
        $pdfDoc->Cell($widths[3], 7, 'Scheduled', 1, 0, 'C', true);
        $pdfDoc->Cell($widths[4], 7, 'Completed', 1, 0, 'C', true);
        $pdfDoc->Cell($widths[5], 7, 'Status', 1, 0, 'C', true);
        $pdfDoc->Cell($widths[6], 7, 'Duration', 1, 1, 'C', true);
    };

    $drawDetailHeader($pdf, $detailWidths);
    $pdf->SetFont('Helvetica', '', 8);
    $detailRow = 0;
    foreach ($details as $d) {
        if ($pdf->GetY() > 184) {
            $pdf->AddPage();
            $sectionTitle($pdf, 'Delivery Records (Continued)');
            $drawDetailHeader($pdf, $detailWidths);
            $detailRow = 0;
        }

        $fill = ($detailRow % 2 === 0);
        if ($fill) {
            $pdf->SetFillColor(248, 250, 252);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }
        $pdf->SetTextColor(31, 41, 55);

        $status = (string) ($d['delivery_status'] ?? '-');
        $pdf->Cell($detailWidths[0], 6, substr((string) ($d['order_number'] ?? ''), 0, 18), 1, 0, 'L', true);
        $pdf->Cell($detailWidths[1], 6, substr((string) ($d['rdc_name'] ?? '-'), 0, 33), 1, 0, 'L', true);
        $pdf->Cell($detailWidths[2], 6, substr((string) ($d['driver_name'] ?? '-'), 0, 19), 1, 0, 'L', true);
        $pdf->Cell($detailWidths[3], 6, !empty($d['scheduled_date']) ? date('M j, Y H:i', strtotime($d['scheduled_date'])) : '-', 1, 0, 'C', true);
        $pdf->Cell($detailWidths[4], 6, !empty($d['completed_date']) ? date('M j, Y H:i', strtotime($d['completed_date'])) : '-', 1, 0, 'C', true);

        if ($status === 'On-time') {
            $pdf->SetTextColor(5, 150, 105);
        } elseif ($status === 'Delayed') {
            $pdf->SetTextColor(220, 38, 38);
        } else {
            $pdf->SetTextColor(217, 119, 6);
        }
        $pdf->Cell($detailWidths[5], 6, $status, 1, 0, 'C', true);

        $pdf->SetTextColor(31, 41, 55);
        $pdf->Cell($detailWidths[6], 6, $fmtHours($d['duration_hours'] ?? null), 1, 1, 'R', true);
        $detailRow++;
    }

    if (empty($details)) {
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(array_sum($detailWidths), 8, 'No delivery records available for selected filters.', 1, 1, 'C', true);
    }

    $pdf->Output('D', 'delivery_efficiency_report_' . date('Y-m-d') . '.pdf');
    exit;
}

// ── CSV Export ───────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="delivery_efficiency_report_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    // UTF-8 BOM so Excel detects encoding correctly
    fwrite($out, "\xEF\xBB\xBF");

    $rdcLookup = [];
    foreach ($allRdcs as $rdc) {
        $rdcLookup[(int) $rdc['rdc_id']] = $rdc['rdc_name'];
    }
    $statusLabels = [
        'completed' => 'Completed',
        'pending' => 'Pending',
        'on_time' => 'On-time',
        'delayed' => 'Delayed',
    ];
    $filterParts = [];
    if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
        $from = !empty($filters['start_date']) ? $filters['start_date'] : 'Any';
        $to = !empty($filters['end_date']) ? $filters['end_date'] : 'Any';
        $filterParts[] = 'Date: ' . $from . ' to ' . $to;
    }
    if (!empty($filters['rdc_id'])) {
        $rdcName = $rdcLookup[(int) $filters['rdc_id']] ?? ('RDC #' . (int) $filters['rdc_id']);
        $filterParts[] = 'RDC: ' . $rdcName;
    }
    if (!empty($filters['status'])) {
        $filterParts[] = 'Status: ' . ($statusLabels[$filters['status']] ?? $filters['status']);
    }
    $filterSummary = empty($filterParts) ? 'All deliveries' : implode(' | ', $filterParts);

    $rankedRdcs = $rdcData;
    usort($rankedRdcs, static function ($a, $b) {
        return ((float) ($b['efficiency_pct'] ?? 0)) <=> ((float) ($a['efficiency_pct'] ?? 0));
    });
    $bestRdc = $rankedRdcs[0] ?? null;
    $worstRdc = !empty($rankedRdcs) ? $rankedRdcs[count($rankedRdcs) - 1] : null;

    // Report header
    fputcsv($out, ['ISDN Delivery Efficiency Report']);
    fputcsv($out, ['Generated At', date('Y-m-d H:i:s')]);
    fputcsv($out, ['Filter Summary', $filterSummary]);
    fputcsv($out, []);

    // KPI snapshot
    fputcsv($out, ['KPI Snapshot']);
    fputcsv($out, ['Metric', 'Value']);
    fputcsv($out, ['Total Deliveries', $summary['total_deliveries']]);
    fputcsv($out, ['Completed Deliveries', $summary['completed']]);
    fputcsv($out, ['On-time Deliveries', $summary['on_time']]);
    fputcsv($out, ['Delayed Deliveries', $summary['delayed']]);
    fputcsv($out, ['Pending Deliveries', $summary['pending']]);
    fputcsv($out, ['Overall Efficiency %', number_format((float) ($summary['overall_efficiency'] ?? 0), 1)]);
    fputcsv($out, ['Average Delivery Hours', number_format((float) ($summary['avg_hours'] ?? 0), 1)]);
    fputcsv($out, []);

    // Performance snapshot
    fputcsv($out, ['Performance Snapshot']);
    fputcsv($out, ['Type', 'RDC Name', 'Code', 'Efficiency %', 'On-time', 'Delayed', 'Pending', 'Avg Hours']);
    if ($bestRdc) {
        fputcsv($out, [
            'Top Performer',
            $bestRdc['rdc_name'] ?? '-',
            $bestRdc['rdc_code'] ?? '-',
            number_format((float) ($bestRdc['efficiency_pct'] ?? 0), 1),
            $bestRdc['on_time'] ?? 0,
            $bestRdc['delayed'] ?? 0,
            $bestRdc['pending'] ?? 0,
            $bestRdc['avg_delivery_hours'] ?? '-',
        ]);
    }
    if ($worstRdc) {
        fputcsv($out, [
            'Needs Attention',
            $worstRdc['rdc_name'] ?? '-',
            $worstRdc['rdc_code'] ?? '-',
            number_format((float) ($worstRdc['efficiency_pct'] ?? 0), 1),
            $worstRdc['on_time'] ?? 0,
            $worstRdc['delayed'] ?? 0,
            $worstRdc['pending'] ?? 0,
            $worstRdc['avg_delivery_hours'] ?? '-',
        ]);
    }
    fputcsv($out, []);

    // RDC comparison
    fputcsv($out, ['RDC Comparison']);
    fputcsv($out, ['RDC Name', 'Code', 'Total', 'Completed', 'On-time', 'Delayed', 'Pending', 'Efficiency %', 'Avg Hours', 'Performance Band']);
    foreach ($rdcData as $row) {
        $eff = (float) ($row['efficiency_pct'] ?? 0);
        $band = $eff >= 80 ? 'Good' : ($eff >= 50 ? 'Medium' : 'Needs Attention');
        fputcsv($out, [
            $row['rdc_name'],
            $row['rdc_code'],
            $row['total_deliveries'],
            $row['completed'],
            $row['on_time'],
            $row['delayed'],
            $row['pending'],
            number_format($eff, 1),
            $row['avg_delivery_hours'] ?? 0,
            $band,
        ]);
    }

    fputcsv($out, []);
    fputcsv($out, ['Delivery Records (Sample)']);
    fputcsv($out, ['Order #', 'RDC', 'Driver', 'Scheduled Date', 'Completed Date', 'Status', 'Duration (hrs)']);
    foreach ($details as $d) {
        $schedDate = !empty($d['scheduled_date']) ? date('Y-m-d', strtotime($d['scheduled_date'])) : '-';
        $compDate = !empty($d['completed_date']) ? date('Y-m-d', strtotime($d['completed_date'])) : '-';
        fputcsv($out, [
            $d['order_number'],
            $d['rdc_name'],
            $d['driver_name'] ?? '-',
            $schedDate,
            $compDate,
            $d['delivery_status'],
            $d['duration_hours'] ?? '-',
        ]);
    }

    fclose($out);
    exit;
}

// ── Page output (include header only after exports are handled) ─
require_once __DIR__ . '/../../includes/header.php';

// ── Chart data preparation ───────────────────────────────────
$chartData = [
    'labels' => array_column($rdcData, 'rdc_name'),
    'on_time' => array_map('intval', array_column($rdcData, 'on_time')),
    'delayed' => array_map('intval', array_column($rdcData, 'delayed')),
    'pending' => array_map('intval', array_column($rdcData, 'pending')),
    'efficiency' => array_map('floatval', array_column($rdcData, 'efficiency_pct')),
    'avg_hours' => array_map('floatval', array_column($rdcData, 'avg_delivery_hours')),
];

// Map data for Sri Lanka regional view: rdc_code => { efficiency, rdc_name, on_time, delayed, pending, perf_class }
$mapRdcData = [];
foreach ($rdcData as $r) {
    $eff = (float) ($r['efficiency_pct'] ?? 0);
    $perfClass = $eff >= 80 ? 'good' : ($eff >= 50 ? 'medium' : 'bad');
    $mapRdcData[$r['rdc_code'] ?? ''] = [
        'rdc_name' => $r['rdc_name'] ?? '',
        'efficiency' => $eff,
        'on_time' => (int) ($r['on_time'] ?? 0),
        'delayed' => (int) ($r['delayed'] ?? 0),
        'pending' => (int) ($r['pending'] ?? 0),
        'total' => (int) ($r['total_deliveries'] ?? 0),
        'perf_class' => $perfClass,
    ];
}

$statusBadge = [
    'On-time' => 'bg-green-100 text-green-700',
    'Delayed' => 'bg-red-100 text-red-700',
    'Pending' => 'bg-yellow-100 text-yellow-700',
];

$rankedRdcData = $rdcData;
usort($rankedRdcData, static function ($a, $b) {
    return ((float) ($b['efficiency_pct'] ?? 0)) <=> ((float) ($a['efficiency_pct'] ?? 0));
});
$bestRdcUi = $rankedRdcData[0] ?? null;
$worstRdcUi = !empty($rankedRdcData) ? $rankedRdcData[count($rankedRdcData) - 1] : null;
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <?php display_flash(); ?>

        <?php if ($error): ?>
            <div class="glass-card rounded-2xl p-5 mb-6 border-l-4 border-red-500">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-rounded text-red-500 text-2xl">error</span>
                    <div>
                        <p class="font-bold text-gray-800">Report Error</p>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=<?php echo $_SESSION['role'] === 'system_admin' ? 'system-admin-dashboard' : 'head-office-manager-dashboard'; ?>"
                    class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition"><span
                        class="material-symbols-rounded">arrow_back</span></a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Sales Performance Report</h1>
                    <p class="text-sm text-gray-500">RDC-wise Sales performance analysis</p>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <?php
                $exportCsvQs = http_build_query(array_merge($_GET, ['export' => 'csv']));
                $exportPdfQs = http_build_query(array_merge($_GET, ['export' => 'pdf']));
                ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?<?php echo $exportPdfQs; ?>"
                    class="px-5 py-2.5 rounded-full bg-gradient-to-r from-red-500 to-rose-600 text-white font-bold text-sm shadow-lg shadow-red-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-lg">picture_as_pdf</span> Export PDF
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?<?php echo $exportCsvQs; ?>"
                    class="px-5 py-2.5 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold text-sm shadow-lg shadow-green-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-lg">download</span> Export CSV
                </a>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="<?php echo BASE_PATH; ?>/index.php"
            class="glass-card rounded-2xl p-6 mb-8 grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6 items-end">

            <input type="hidden" name="page" value="delivery-report">

            <!-- From -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From</label>
                <input type="date" name="start_date"
                    value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>"
                    class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm w-full">
            </div>

            <!-- To -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">To</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>"
                    class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm w-full">
            </div>

            <!-- RDC -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">RDC</label>
                <select name="rdc_id"
                    class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm w-full">
                    <option value="">All RDCs</option>
                    <?php foreach ($allRdcs as $r): ?>
                        <option value="<?php echo $r['rdc_id']; ?>" <?php echo (($filters['rdc_id'] ?? '') == $r['rdc_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r['rdc_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons grouped -->
            <div class="flex items-end gap-3">

                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold text-sm shadow-md hover:scale-[1.02] transition flex items-center gap-1.5">
                    <span class="material-symbols-rounded text-base">filter_list</span>
                    Apply
                </button>

                <a href="<?php echo BASE_PATH; ?>/index.php?page=delivery-report"
                    class="px-4 py-2 rounded-xl text-gray-600 hover:text-gray-800 hover:bg-white/40 transition text-sm flex items-center gap-1 border border-white/30">
                    <span class="material-symbols-rounded text-base">refresh</span>
                    Reset
                </a>

            </div>

        </form>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-10">
            <!-- Total Revenue -->
            <div
                class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-teal-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Sales</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-2 font-['Outfit']">LKR 150,000
                            <?php ?>
                        </h3>
                        <p class="text-teal-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">payments</span> All time sales
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-teal-100/50 flex items-center justify-center text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">account_balance</span>
                    </div>
                </div>
            </div>
            <!-- Total Orders -->
            <div
                class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Orders</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            1495
                        </h3>
                        <p class="text-blue-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">shopping_cart</span> Island-wide
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-100/50 flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">receipt_long</span>
                    </div>
                </div>
            </div>
            <!-- Pending -->
            <div
                class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-yellow-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Payments Received</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            LKR 125,000
                        </h3>
                        <p class="text-gray-500 text-xs font-medium mt-2 flex items-center text-yellow-600">
                            <span class="material-symbols-rounded text-sm mr-1">payment_arrow_down</span> Cash + Card
                            Payments
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-yellow-100/50 flex items-center justify-center text-yellow-600 group-hover:bg-yellow-400 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class=" material-symbols-rounded">pending</span>
                    </div>
                </div>
            </div>
            <!-- Delivered -->
            <div
                class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Delivered</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            360
                        </h3>
                        <p class="text-green-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">check_circle</span> Successfully
                            Delivered
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-green-100/50 flex items-center justify-center text-green-600 group-hover:bg-green-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">done_all</span>
                    </div>
                </div>
            </div>
            <!-- Monthly Growth -->
            <div
                class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-purple-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Pending</p>
                        <h3 class="text-3xl font-bold mt-2 font-['Outfit'] ">
                            185
                        </h3>
                        <p class=" text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">hourglass_top</span>
                            Awaiting processing
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-purple-100/50 flex items-center justify-center text-purple-500 group-hover:bg-purple-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">clock_loader_60</span>
                    </div>
                </div>
            </div>
            <!-- Low Stock -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-red-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">

                            Total Cancelled</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            52
                        </h3>
                        <p class="text-red-500 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">warning</span>
                            Cancelled Orders
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-red-100/50 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">cancel</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-10">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-rounded text-emerald-600">trending_up</span>
                    <span class="material-symbols-rounded text-emerald-600">grocery</span>
                    <p class="text-sm font-bold text-emerald-700 uppercase tracking-wide">Top Selling Product</p>
                </div>
                <?php if ($bestRdcUi): ?>
                    <p class="text-lg font-bold text-gray-800">Basmati Rice 5kg</span></p>
                    <p class="text-sm text-gray-600 mt-1">Category: <span
                            class="font-semibold text-emerald-700"></span><span class="font-semibold">Grocery & Food
                            Items</span> | Avg Sale: <span class="font-semibold">60</span></p>
                <?php else: ?>
                    <p class="text-sm text-gray-500">No RDC data available for current filters.</p>
                <?php endif; ?>
            </div>
            <div class="rounded-2xl border border-yellow-200 bg-yellow-50/50 p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-rounded text-yellow-600">trending_up</span>
                    <span class="material-symbols-rounded text-yellow-600">person</span>
                    <p class="text-sm font-bold text-yellow-700 uppercase tracking-wide">
                        Top Purchasing Customer
                    </p>
                </div>

                <p class="text-lg font-bold text-gray-800">Sunil Traders</p>

                <p class="text-sm text-gray-600 mt-1">
                    RDC:
                    <span class="font-semibold text-yellow-700">Southern</span>
                    | Purchases:
                    <span class="font-semibold">LKR 120,000</span>
                </p>
            </div>
            <div class="rounded-2xl border border-purple-200 bg-purple-50/80 p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-rounded text-purple-600">person_celebrate</span>
                    <p class="text-sm font-bold text-purple-700 uppercase tracking-wide">
                        Top Performing Sales Rep.
                    </p>
                </div>

                <p class="text-lg font-bold text-gray-800"> Kamal Jayasuriya</p>

                <p class="text-sm text-gray-600 mt-1">
                    Orders:
                    <span class="font-semibold text-purple-700">150</span>
                    | Sales Amount:
                    <span class="font-semibold">LKR 150,000</span>
                </p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-8 mb-10">
            <!-- Efficiency % + Avg Hours -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-teal-500 text-2xl"><span
                            class="material-symbols-outlined">analytics
                        </span></span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Sales Performance</h2>
                </div>
                <div class="h-82">
                    <div class="rounded-2xl bg-white/40 border border-white/60 p-4">
                        <canvas id="rdcSalesWormChart" height="110"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- RDC Comparison Table -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-yellow-500 text-2xl">table_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">RDC wise Sales</h2>
                </div>
            </div>
            <?php if (empty($rdcData)): ?>
                <div class="text-center py-12">
                    <p class="text-sm text-gray-400">No delivery data available for the selected filters.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-4">RDC</th>
                                <th class="pb-3 pr-4 text-center">Total</th>
                                <th class="pb-3 pr-4 text-center">On-time</th>
                                <th class="pb-3 pr-4 text-center">Delayed</th>
                                <th class="pb-3 pr-4 text-center">Pending</th>
                                <th class="pb-3 pr-4 text-center">Efficiency</th>
                                <th class="pb-3 pr-4 text-center">Avg Hours</th>
                                <th class="pb-3">Performance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50">
                            <?php foreach ($rdcData as $r):
                                $eff = $r['efficiency_pct'] ?? 0;
                                $effColor = $eff >= 80 ? 'text-green-600' : ($eff >= 50 ? 'text-yellow-600' : 'text-red-600');
                                $barColor = $eff >= 80 ? 'from-green-400 to-green-500' : ($eff >= 50 ? 'from-yellow-400 to-yellow-500' : 'from-red-400 to-red-500');
                                ?>
                                <tr class="hover:bg-white/30 transition">
                                    <td class="py-4 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-xl bg-indigo-100/50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-rounded text-base">warehouse</span>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800">
                                                    <?php echo htmlspecialchars($r['rdc_name']); ?>
                                                </p>
                                                <p class="text-[10px] text-gray-400">
                                                    <?php echo htmlspecialchars($r['rdc_code']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-4 text-center font-bold text-gray-800">
                                        <?php echo $r['total_deliveries']; ?>
                                    </td>
                                    <td class="py-4 pr-4 text-center font-semibold text-green-600"><?php echo $r['on_time']; ?>
                                    </td>
                                    <td class="py-4 pr-4 text-center font-semibold text-red-600"><?php echo $r['delayed']; ?>
                                    </td>
                                    <td class="py-4 pr-4 text-center text-yellow-600"><?php echo $r['pending']; ?></td>
                                    <td class="py-4 pr-4 text-center font-bold <?php echo $effColor; ?>"><?php echo $eff; ?>%
                                    </td>
                                    <td class="py-4 pr-4 text-center text-gray-600">
                                        <?php echo $r['avg_delivery_hours'] ?? '-'; ?>h
                                    </td>
                                    <td class="py-4 min-w-[120px]">
                                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                                            <div class="bg-gradient-to-r <?php echo $barColor; ?> h-2.5 rounded-full transition-all duration-500"
                                                style="width: <?php echo min(100, $eff); ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-purple-500 text-2xl">grocery</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Top Selling Products</h2>
                </div>
            </div>
            <?php if (empty($rdcData)): ?>
                <div class="text-center py-12">
                    <p class="text-sm text-gray-400">No delivery data available for the selected filters.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-4">RDC</th>
                                <th class="pb-3 pr-4 text-center">Total</th>
                                <th class="pb-3 pr-4 text-center">On-time</th>
                                <th class="pb-3 pr-4 text-center">Delayed</th>
                                <th class="pb-3 pr-4 text-center">Pending</th>
                                <th class="pb-3 pr-4 text-center">Efficiency</th>
                                <th class="pb-3 pr-4 text-center">Avg Hours</th>
                                <th class="pb-3">Performance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50">
                            <?php foreach ($rdcData as $r):
                                $eff = $r['efficiency_pct'] ?? 0;
                                $effColor = $eff >= 80 ? 'text-green-600' : ($eff >= 50 ? 'text-yellow-600' : 'text-red-600');
                                $barColor = $eff >= 80 ? 'from-green-400 to-green-500' : ($eff >= 50 ? 'from-yellow-400 to-yellow-500' : 'from-red-400 to-red-500');
                                ?>
                                <tr class="hover:bg-white/30 transition">
                                    <td class="py-4 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-xl bg-indigo-100/50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-rounded text-base">warehouse</span>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800">
                                                    <?php echo htmlspecialchars($r['rdc_name']); ?>
                                                </p>
                                                <p class="text-[10px] text-gray-400">
                                                    <?php echo htmlspecialchars($r['rdc_code']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-4 text-center font-bold text-gray-800">
                                        <?php echo $r['total_deliveries']; ?>
                                    </td>
                                    <td class="py-4 pr-4 text-center font-semibold text-green-600"><?php echo $r['on_time']; ?>
                                    </td>
                                    <td class="py-4 pr-4 text-center font-semibold text-red-600"><?php echo $r['delayed']; ?>
                                    </td>
                                    <td class="py-4 pr-4 text-center text-yellow-600"><?php echo $r['pending']; ?></td>
                                    <td class="py-4 pr-4 text-center font-bold <?php echo $effColor; ?>"><?php echo $eff; ?>%
                                    </td>
                                    <td class="py-4 pr-4 text-center text-gray-600">
                                        <?php echo $r['avg_delivery_hours'] ?? '-'; ?>h
                                    </td>
                                    <td class="py-4 min-w-[120px]">
                                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                                            <div class="bg-gradient-to-r <?php echo $barColor; ?> h-2.5 rounded-full transition-all duration-500"
                                                style="width: <?php echo min(100, $eff); ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Detailed Delivery Records -->
        <?php if (!empty($details)): ?>
            <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-orange-500 text-2xl">list_alt</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Order Records</h2>
                    <span
                        class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full"><?php echo count($details); ?>
                        records</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-3">Order #</th>
                                <th class="pb-3 pr-3">Customer</th>
                                <th class="pb-3">RDC</th>
                                <th class="pb-3 pr-3">Date</th>
                                <th class="pb-3 pr-3">Amount</th>
                                <th class="pb-3 pr-3">Delivery Date</th>
                                <th class="pb-3 pr-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50">
                            <?php foreach ($details as $d): ?>
                                <tr class="hover:bg-white/30 transition">
                                    <td class="py-3 pr-3 font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($d['order_number']); ?>
                                    </td>
                                    <td class="py-3 pr-3 text-gray-600"><?php echo htmlspecialchars($d['rdc_name']); ?></td>
                                    <td class="py-3 pr-3 text-gray-600">
                                        <?php echo htmlspecialchars($d['driver_name'] ?? '-'); ?>
                                    </td>
                                    <td class="py-3 pr-3 text-gray-500 text-xs">
                                        <?php echo $d['scheduled_date'] ? date('M j, Y H:i', strtotime($d['scheduled_date'])) : '-'; ?>
                                    </td>
                                    <td class="py-3 pr-3 text-gray-500 text-xs">
                                        <?php echo $d['completed_date'] ? date('M j, Y H:i', strtotime($d['completed_date'])) : '-'; ?>
                                    </td>
                                    <td class="py-3 pr-3"><span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $statusBadge[$d['delivery_status']] ?? 'bg-gray-100 text-gray-600'; ?>"><?php echo $d['delivery_status']; ?></span>
                                    </td>
                                    <td class="py-3 text-gray-600">
                                        <?php echo $d['duration_hours'] !== null ? $d['duration_hours'] . 'h' : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Leaflet + OpenStreetMap for real map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
<style>
    .custom-marker {
        background: none !important;
        border: none !important;
    }

    .marker-shop-icon {
        font-family: 'Material Symbols Rounded';
        font-weight: 400;
        font-style: normal;
        font-size: 22px;
        line-height: 1;
        letter-spacing: normal;
    }

    #delivery-map .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    #delivery-map .leaflet-popup-content {
        margin: 12px 16px;
        min-width: 160px;
    }
</style>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    (function () {
        var mapData = <?php echo json_encode($mapRdcData); ?>;
        var rdcCoords = {
            'NORTH': [9.6615, 80.0255],
            'SOUTH': [6.0531, 80.2110],
            'EAST': [7.7131, 81.6924],
            'WEST': [6.9271, 79.8612],
            'CENTRAL': [7.2906, 80.6337]
        };
        var rdcAddresses = {
            'NORTH': 'Jaffna Industrial Zone',
            'SOUTH': 'Galle Trade Center',
            'EAST': 'Batticaloa Hub',
            'WEST': 'Colombo Warehouse Complex',
            'CENTRAL': 'Kandy Distribution Park'
        };
        var colors = { good: '#10b981', medium: '#f59e0b', bad: '#ef4444', empty: '#9ca3af' };
        // Simplified Sri Lanka regional polygons (lat, lng) - approximate provincial boundaries
        var regionPolygons = {
            'NORTH': [[9.75, 80.0], [9.5, 80.15], [9.0, 80.5], [8.7, 80.6], [8.9, 80.9], [9.3, 80.95], [9.6, 80.5], [9.75, 80.0]],
            'SOUTH': [[6.0, 80.2], [6.3, 80.4], [6.6, 80.3], [6.9, 80.85], [6.5, 81.0], [6.1, 80.6], [6.0, 80.2]],
            'EAST': [[8.9, 80.6], [8.5, 81.0], [7.8, 81.7], [7.2, 81.8], [6.8, 81.5], [7.0, 81.0], [7.5, 80.8], [8.2, 80.7], [8.9, 80.6]],
            'WEST': [[8.7, 79.8], [8.0, 79.9], [7.0, 79.95], [6.2, 80.1], [6.0, 80.2], [6.3, 80.4], [6.6, 80.3], [7.2, 80.0], [7.8, 79.9], [8.7, 79.8]],
            'CENTRAL': [[8.0, 80.5], [7.8, 80.7], [7.5, 80.8], [7.0, 81.0], [6.8, 81.5], [7.2, 80.9], [7.5, 80.5], [7.8, 80.2], [8.0, 80.5]]
        };
        var map = L.map('delivery-map').setView([7.8731, 80.7718], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        // Add colored region polygons first (below markers)
        ['NORTH', 'SOUTH', 'EAST', 'WEST', 'CENTRAL'].forEach(function (code) {
            var d = mapData[code] || null;
            var col = d ? colors[d.perf_class] : colors.empty;
            var fillOpacity = d ? 0.55 : 0.25;
            var poly = L.polygon(regionPolygons[code], {
                color: col,
                weight: 2,
                fillColor: col,
                fillOpacity: fillOpacity
            }).addTo(map);
            var name = d ? d.rdc_name : (code + ' RDC');
            var addr = rdcAddresses[code] || '';
            var popup = '<div style="font-weight:700;color:#1f2937;font-size:14px;">' + name + '</div>';
            if (addr) popup += '<div style="font-size:11px;color:#6b7280;margin-top:4px;">' + addr + '</div>';
            if (d) {
                var effColor = d.perf_class === 'good' ? '#059669' : (d.perf_class === 'medium' ? '#d97706' : '#dc2626');
                popup += '<div style="margin-top:8px;font-size:13px;"><span style="font-weight:700;color:' + effColor + ';">' + d.efficiency + '%</span> efficiency</div>';
                popup += '<div style="font-size:11px;margin-top:4px;"><span style="color:#10b981">On-time: ' + d.on_time + '</span> | <span style="color:#ef4444">Delayed: ' + d.delayed + '</span> | <span style="color:#f59e0b">Pending: ' + d.pending + '</span></div>';
            } else {
                popup += '<div style="margin-top:8px;font-size:11px;color:#6b7280;">No delivery data</div>';
            }
            poly.bindPopup(popup, { maxWidth: 260 });
            poly.on('mouseover', function () { this.setStyle({ fillOpacity: 0.75 }); });
            poly.on('mouseout', function () { this.setStyle({ fillOpacity: fillOpacity }); });
        });
        // Add markers on top - shop/store icons for RDC locations
        ['NORTH', 'SOUTH', 'EAST', 'WEST', 'CENTRAL'].forEach(function (code) {
            var d = mapData[code] || null;
            var c = rdcCoords[code];
            var col = d ? colors[d.perf_class] : colors.empty;
            var icon = L.divIcon({
                className: 'custom-marker',
                html: '<div style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:white;border:2px solid ' + col + ';box-shadow:0 2px 8px rgba(0,0,0,0.25);"><span class="marker-shop-icon" style="color:' + col + ';">storefront</span></div>',
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });
            var m = L.marker(c, { icon: icon }).addTo(map);
            var name = d ? d.rdc_name : (code + ' RDC');
            var addr = rdcAddresses[code] || '';
            var popup = '<div style="font-weight:700;color:#1f2937;font-size:14px;">' + name + '</div>';
            if (addr) popup += '<div style="font-size:11px;color:#6b7280;margin-top:4px;">' + addr + '</div>';
            if (d) {
                var effColor = d.perf_class === 'good' ? '#059669' : (d.perf_class === 'medium' ? '#d97706' : '#dc2626');
                popup += '<div style="margin-top:8px;font-size:13px;"><span style="font-weight:700;color:' + effColor + ';">' + d.efficiency + '%</span> efficiency</div>';
                popup += '<div style="font-size:11px;margin-top:4px;"><span style="color:#10b981">On-time: ' + d.on_time + '</span> | <span style="color:#ef4444">Delayed: ' + d.delayed + '</span> | <span style="color:#f59e0b">Pending: ' + d.pending + '</span></div>';
            } else {
                popup += '<div style="margin-top:8px;font-size:11px;color:#6b7280;">No delivery data</div>';
            }
            m.bindPopup(popup, { maxWidth: 260 });
        });
    })();
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesSeriesByRdc = {

        "Northern RDC": [
            { date: "2026-01-01", sales: 120000 },
            { date: "2026-01-02", sales: 132000 },
            { date: "2026-01-03", sales: 98000 },
            { date: "2026-01-04", sales: 150000 },
            { date: "2026-01-05", sales: 165000 },
            { date: "2026-01-06", sales: 142000 },
            { date: "2026-01-07", sales: 175000 },
            { date: "2026-01-08", sales: 188000 },
            { date: "2026-01-09", sales: 160000 },
            { date: "2026-01-10", sales: 172000 }
        ],

        "Southern RDC": [
            { date: "2026-01-01", sales: 90000 },
            { date: "2026-01-02", sales: 110000 },
            { date: "2026-01-03", sales: 125000 },
            { date: "2026-01-04", sales: 118000 },
            { date: "2026-01-05", sales: 140000 },
            { date: "2026-01-06", sales: 150000 },
            { date: "2026-01-07", sales: 158000 },
            { date: "2026-01-08", sales: 162000 },
            { date: "2026-01-09", sales: 148000 },
            { date: "2026-01-10", sales: 170000 }
        ],

        "Central RDC": [
            { date: "2026-01-01", sales: 75000 },
            { date: "2026-01-02", sales: 88000 },
            { date: "2026-01-03", sales: 92000 },
            { date: "2026-01-04", sales: 105000 },
            { date: "2026-01-05", sales: 115000 },
            { date: "2026-01-06", sales: 110000 },
            { date: "2026-01-07", sales: 125000 },
            { date: "2026-01-08", sales: 130000 },
            { date: "2026-01-09", sales: 122000 },
            { date: "2026-01-10", sales: 138000 }
        ],

        "Western RDC": [
            { date: "2026-01-01", sales: 60000 },
            { date: "2026-01-02", sales: 85000 },
            { date: "2026-01-03", sales: 78000 },
            { date: "2026-01-04", sales: 92000 },
            { date: "2026-01-05", sales: 101000 },
            { date: "2026-01-06", sales: 108000 },
            { date: "2026-01-07", sales: 112000 },
            { date: "2026-01-08", sales: 120000 },
            { date: "2026-01-09", sales: 118000 },
            { date: "2026-01-10", sales: 130000 }
        ],

        "Eastern RDC": [
            { date: "2026-01-01", sales: 55000 },
            { date: "2026-01-02", sales: 62000 },
            { date: "2026-01-03", sales: 70000 },
            { date: "2026-01-04", sales: 76000 },
            { date: "2026-01-05", sales: 83000 },
            { date: "2026-01-06", sales: 90000 },
            { date: "2026-01-07", sales: 95000 },
            { date: "2026-01-08", sales: 99000 },
            { date: "2026-01-09", sales: 102000 },
            { date: "2026-01-10", sales: 108000 }
        ]

    };

    const rdcColors = {
        "Northern RDC": "#ef4444",
        "Southern RDC": "#8b5cf6",
        "Central RDC": "#10b981",
        "Western RDC": "#f59e0b",
        "Eastern RDC": "#0ea5e9"
    };

    const labels = salesSeriesByRdc["Northern RDC"].map(p => p.date);

    const datasets = Object.entries(salesSeriesByRdc).map(([rdc, points]) => ({
        label: rdc,
        data: points.map(p => p.sales),
        borderColor: rdcColors[rdc],
        backgroundColor: rdcColors[rdc],
        tension: 0.45,
        borderWidth: 3,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointRadius: 4,
        pointBorderWidth: 2,
        fill: false
    }));

    const ctx = document.getElementById("rdcSalesWormChart");

    new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "top",
                    labels: {
                        usePointStyle: true,
                        pointStyle: "circle",
                        boxWidth: 10,
                        boxHeight: 10,
                        padding: 18,
                        font: {
                            size: 12,
                            weight: "600"
                        }
                    }
                }
            },
            scales: {

                x: {
                    title: {
                        display: true,
                        text: "Date",
                        color: "#334155",
                        font: {
                            weight: "bold",
                            size: 13
                        }
                    },
                    grid: {
                        color: "rgba(15,23,42,0.06)"
                    }
                },

                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Sales Amount (LKR)",
                        color: "#334155",
                        font: {
                            weight: "bold",
                            size: 13
                        }
                    },
                    ticks: {
                        stepSize: 20000,
                        callback: function (value) {
                            return "Rs. " + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: "rgba(15,23,42,0.06)"
                    }
                }

            }
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>