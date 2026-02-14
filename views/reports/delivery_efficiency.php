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
    $rdcData    = $report->getRdcEfficiency($filters);
    $summary    = $report->getOverallSummary($filters);
    $details    = $report->getDeliveryDetails($filters, 50);
    $allRdcs    = $report->getAllRdcs();
} catch (Exception $e) {
    $error   = $e->getMessage();
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
            $row['rdc_name'], $row['rdc_code'], $row['total_deliveries'],
            $row['completed'], $row['on_time'], $row['delayed'], $row['pending'],
            number_format($eff, 1), $row['avg_delivery_hours'] ?? 0, $band,
        ]);
    }

    fputcsv($out, []);
    fputcsv($out, ['Delivery Records (Sample)']);
    fputcsv($out, ['Order #', 'RDC', 'Driver', 'Scheduled Date', 'Completed Date', 'Status', 'Duration (hrs)']);
    foreach ($details as $d) {
        $schedDate = !empty($d['scheduled_date']) ? date('Y-m-d', strtotime($d['scheduled_date'])) : '-';
        $compDate  = !empty($d['completed_date']) ? date('Y-m-d', strtotime($d['completed_date'])) : '-';
        fputcsv($out, [
            $d['order_number'], $d['rdc_name'], $d['driver_name'] ?? '-',
            $schedDate, $compDate,
            $d['delivery_status'], $d['duration_hours'] ?? '-',
        ]);
    }

    fclose($out);
    exit;
}

// ── Page output (include header only after exports are handled) ─
require_once __DIR__ . '/../../includes/header.php';

// ── Chart data preparation ───────────────────────────────────
$chartData = [
    'labels'     => array_column($rdcData, 'rdc_name'),
    'on_time'    => array_map('intval', array_column($rdcData, 'on_time')),
    'delayed'    => array_map('intval', array_column($rdcData, 'delayed')),
    'pending'    => array_map('intval', array_column($rdcData, 'pending')),
    'efficiency' => array_map('floatval', array_column($rdcData, 'efficiency_pct')),
    'avg_hours'  => array_map('floatval', array_column($rdcData, 'avg_delivery_hours')),
];

// Map data for Sri Lanka regional view: rdc_code => { efficiency, rdc_name, on_time, delayed, pending, perf_class }
$mapRdcData = [];
foreach ($rdcData as $r) {
    $eff = (float) ($r['efficiency_pct'] ?? 0);
    $perfClass = $eff >= 80 ? 'good' : ($eff >= 50 ? 'medium' : 'bad');
    $mapRdcData[$r['rdc_code'] ?? ''] = [
        'rdc_name'   => $r['rdc_name'] ?? '',
        'efficiency' => $eff,
        'on_time'    => (int) ($r['on_time'] ?? 0),
        'delayed'    => (int) ($r['delayed'] ?? 0),
        'pending'    => (int) ($r['pending'] ?? 0),
        'total'      => (int) ($r['total_deliveries'] ?? 0),
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
                <a href="<?php echo BASE_PATH; ?>/index.php?page=<?php echo $_SESSION['role'] === 'system_admin' ? 'system-admin-dashboard' : 'head-office-manager-dashboard'; ?>" class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition"><span class="material-symbols-rounded">arrow_back</span></a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Delivery Efficiency Report</h1>
                    <p class="text-sm text-gray-500">RDC-wise delivery performance analysis</p>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <?php
                    $exportCsvQs = http_build_query(array_merge($_GET, ['export' => 'csv']));
                    $exportPdfQs = http_build_query(array_merge($_GET, ['export' => 'pdf']));
                ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?<?php echo $exportPdfQs; ?>" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-red-500 to-rose-600 text-white font-bold text-sm shadow-lg shadow-red-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-lg">picture_as_pdf</span> Export PDF
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?<?php echo $exportCsvQs; ?>" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold text-sm shadow-lg shadow-green-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-lg">download</span> Export CSV
                </a>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="<?php echo BASE_PATH; ?>/index.php" class="glass-card rounded-2xl p-5 mb-8 flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="delivery-report">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>"
                       class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">To</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>"
                       class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">RDC</label>
                <select name="rdc_id" class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm min-w-[150px] focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    <option value="">All RDCs</option>
                    <?php foreach ($allRdcs as $r): ?>
                        <option value="<?php echo $r['rdc_id']; ?>" <?php echo (($filters['rdc_id'] ?? '') == $r['rdc_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($r['rdc_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm min-w-[130px] focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    <option value="">All</option>
                    <option value="completed" <?php echo (($filters['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending"   <?php echo (($filters['status'] ?? '') === 'pending')   ? 'selected' : ''; ?>>Pending</option>
                    <option value="on_time"   <?php echo (($filters['status'] ?? '') === 'on_time')   ? 'selected' : ''; ?>>On-time</option>
                    <option value="delayed"   <?php echo (($filters['status'] ?? '') === 'delayed')   ? 'selected' : ''; ?>>Delayed</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-bold text-sm shadow-lg hover:scale-[1.02] transition flex items-center gap-1.5">
                <span class="material-symbols-rounded text-base">filter_list</span> Apply
            </button>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=delivery-report" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-white/40 transition text-sm flex items-center gap-1">
                <span class="material-symbols-rounded text-base">refresh</span> Reset
            </a>
        </form>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="rounded-3xl p-5 text-white shadow-lg bg-gradient-to-br from-slate-900 to-slate-700">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-200">Total Deliveries</p>
                <h3 class="text-3xl font-bold mt-1 font-['Outfit']"><?php echo number_format($summary['total_deliveries']); ?></h3>
                <p class="text-[11px] font-semibold mt-2 flex items-center text-slate-200"><span class="material-symbols-rounded text-sm mr-1">local_shipping</span> Network volume</p>
            </div>
            <div class="rounded-3xl p-5 text-white shadow-lg bg-gradient-to-br from-emerald-600 to-emerald-500">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-100">On-time Deliveries</p>
                <h3 class="text-3xl font-bold mt-1 font-['Outfit']"><?php echo number_format($summary['on_time']); ?></h3>
                <p class="text-[11px] font-semibold mt-2 flex items-center text-emerald-100"><span class="material-symbols-rounded text-sm mr-1">check_circle</span> Within SLA window</p>
            </div>
            <div class="rounded-3xl p-5 text-white shadow-lg bg-gradient-to-br from-rose-600 to-red-500">
                <p class="text-[10px] font-bold uppercase tracking-wider text-red-100">Delayed Deliveries</p>
                <h3 class="text-3xl font-bold mt-1 font-['Outfit']"><?php echo number_format($summary['delayed']); ?></h3>
                <p class="text-[11px] font-semibold mt-2 flex items-center text-red-100"><span class="material-symbols-rounded text-sm mr-1">schedule</span> Missed target time</p>
            </div>
            <div class="rounded-3xl p-5 text-white shadow-lg bg-gradient-to-br from-amber-500 to-orange-400">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-100">Pending Deliveries</p>
                <h3 class="text-3xl font-bold mt-1 font-['Outfit']"><?php echo number_format($summary['pending']); ?></h3>
                <p class="text-[11px] font-semibold mt-2 flex items-center text-amber-100"><span class="material-symbols-rounded text-sm mr-1">hourglass_top</span> Awaiting completion</p>
            </div>
            <div class="rounded-3xl p-5 text-white shadow-lg bg-gradient-to-br from-sky-600 to-blue-500">
                <p class="text-[10px] font-bold uppercase tracking-wider text-sky-100">Overall Efficiency</p>
                <h3 class="text-3xl font-bold mt-1 font-['Outfit']"><?php echo number_format((float) ($summary['overall_efficiency'] ?? 0), 1); ?>%</h3>
                <p class="text-[11px] font-semibold mt-2 flex items-center text-sky-100"><span class="material-symbols-rounded text-sm mr-1">speed</span> On-time completion rate</p>
            </div>
            <div class="rounded-3xl p-5 text-white shadow-lg bg-gradient-to-br from-cyan-600 to-teal-500">
                <p class="text-[10px] font-bold uppercase tracking-wider text-cyan-100">Average Delivery Time</p>
                <h3 class="text-3xl font-bold mt-1 font-['Outfit']"><?php echo number_format((float) ($summary['avg_hours'] ?? 0), 1); ?>h</h3>
                <p class="text-[11px] font-semibold mt-2 flex items-center text-cyan-100"><span class="material-symbols-rounded text-sm mr-1">timer</span> From order to completion</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-10">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-rounded text-emerald-600">military_tech</span>
                    <p class="text-sm font-bold text-emerald-700 uppercase tracking-wide">Top Performer</p>
                </div>
                <?php if ($bestRdcUi): ?>
                    <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($bestRdcUi['rdc_name'] ?? '-'); ?> <span class="text-sm text-gray-500">(<?php echo htmlspecialchars($bestRdcUi['rdc_code'] ?? '-'); ?>)</span></p>
                    <p class="text-sm text-gray-600 mt-1">Efficiency: <span class="font-semibold text-emerald-700"><?php echo number_format((float) ($bestRdcUi['efficiency_pct'] ?? 0), 1); ?>%</span> | On-time: <span class="font-semibold"><?php echo number_format((int) ($bestRdcUi['on_time'] ?? 0)); ?></span> | Avg: <span class="font-semibold"><?php echo is_numeric($bestRdcUi['avg_delivery_hours'] ?? null) ? number_format((float) $bestRdcUi['avg_delivery_hours'], 1) . 'h' : '-'; ?></span></p>
                <?php else: ?>
                    <p class="text-sm text-gray-500">No RDC data available for current filters.</p>
                <?php endif; ?>
            </div>
            <div class="rounded-2xl border border-rose-200 bg-rose-50/80 p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-rounded text-rose-600">warning</span>
                    <p class="text-sm font-bold text-rose-700 uppercase tracking-wide">Needs Attention</p>
                </div>
                <?php if ($worstRdcUi): ?>
                    <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($worstRdcUi['rdc_name'] ?? '-'); ?> <span class="text-sm text-gray-500">(<?php echo htmlspecialchars($worstRdcUi['rdc_code'] ?? '-'); ?>)</span></p>
                    <p class="text-sm text-gray-600 mt-1">Efficiency: <span class="font-semibold text-rose-700"><?php echo number_format((float) ($worstRdcUi['efficiency_pct'] ?? 0), 1); ?>%</span> | Delayed: <span class="font-semibold"><?php echo number_format((int) ($worstRdcUi['delayed'] ?? 0)); ?></span> | Pending: <span class="font-semibold"><?php echo number_format((int) ($worstRdcUi['pending'] ?? 0)); ?></span></p>
                <?php else: ?>
                    <p class="text-sm text-gray-500">No RDC data available for current filters.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sri Lanka Map - Regional Delivery Efficiency -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-teal-500 text-2xl">map</span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Regional Delivery Efficiency</h2>
                        <p class="text-sm text-gray-500">Interactive map — RDC locations with performance markers</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-green-500 shadow-sm border-2 border-white"></span>
                        <span class="text-xs font-medium text-gray-600">Good (≥80%)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-amber-500 shadow-sm border-2 border-white"></span>
                        <span class="text-xs font-medium text-gray-600">Medium (50–79%)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-red-500 shadow-sm border-2 border-white"></span>
                        <span class="text-xs font-medium text-gray-600">Needs attention (&lt;50%)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-gray-300 shadow-sm border-2 border-white"></span>
                        <span class="text-xs font-medium text-gray-600">No data</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col lg:flex-row gap-8 items-center lg:items-start">
                <div class="flex-1 w-full lg:min-w-0">
                    <div class="bg-white rounded-2xl overflow-hidden shadow-inner border border-gray-100">
                        <div id="delivery-map" class="w-full h-[400px] rounded-2xl"></div>
                    </div>
                </div>
                <div class="w-full lg:w-80 space-y-3">
                    <?php
                    $allCodes = ['NORTH', 'SOUTH', 'EAST', 'WEST', 'CENTRAL'];
                    foreach ($allCodes as $code):
                        $d = $mapRdcData[$code] ?? null;
                        $label = $code === 'NORTH' ? 'Northern' : ($code === 'SOUTH' ? 'Southern' : ($code === 'EAST' ? 'Eastern' : ($code === 'WEST' ? 'Western' : 'Central')));
                        $label .= ' RDC';
                        $perfClass = $d['perf_class'] ?? 'empty';
                        $bgClass = $perfClass === 'good' ? 'bg-green-50 border-green-200' : ($perfClass === 'medium' ? 'bg-amber-50 border-amber-200' : ($perfClass === 'bad' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200'));
                        $effColor = $perfClass === 'good' ? 'text-green-600' : ($perfClass === 'medium' ? 'text-amber-600' : ($perfClass === 'bad' ? 'text-red-600' : 'text-gray-500'));
                    ?>
                    <div class="rounded-xl border p-4 <?php echo $bgClass; ?> transition hover:shadow-md" data-rdc="<?php echo $code; ?>">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800"><?php echo htmlspecialchars($d['rdc_name'] ?? $label); ?></span>
                            <span class="font-bold <?php echo $effColor; ?>"><?php echo $d ? $d['efficiency'] . '%' : '—'; ?></span>
                        </div>
                        <?php if ($d): ?>
                        <div class="flex gap-4 mt-2 text-xs text-gray-500">
                            <span class="text-green-600">On-time: <?php echo $d['on_time']; ?></span>
                            <span class="text-red-600">Delayed: <?php echo $d['delayed']; ?></span>
                            <span class="text-amber-600">Pending: <?php echo $d['pending']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
            <!-- Stacked Bar Chart: On-time vs Delayed per RDC -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-blue-500 text-2xl">bar_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">RDC Delivery Breakdown</h2>
                </div>
                <div class="h-72">
                    <?php if (empty($chartData['labels'])): ?>
                        <div class="flex flex-col items-center justify-center h-full"><p class="text-sm text-gray-400">No delivery data</p></div>
                    <?php else: ?>
                        <canvas id="rdcBreakdownChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Efficiency % + Avg Hours -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-teal-500 text-2xl">speed</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Efficiency &amp; Avg Duration</h2>
                </div>
                <div class="h-72">
                    <?php if (empty($chartData['labels'])): ?>
                        <div class="flex flex-col items-center justify-center h-full"><p class="text-sm text-gray-400">No data</p></div>
                    <?php else: ?>
                        <canvas id="efficiencyChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RDC Comparison Table -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-indigo-500 text-2xl">table_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">RDC Comparison</h2>
                </div>
            </div>
            <?php if (empty($rdcData)): ?>
                <div class="text-center py-12"><p class="text-sm text-gray-400">No delivery data available for the selected filters.</p></div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
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
                                        <div class="w-9 h-9 rounded-xl bg-indigo-100/50 text-indigo-600 flex items-center justify-center flex-shrink-0"><span class="material-symbols-rounded text-base">warehouse</span></div>
                                        <div>
                                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($r['rdc_name']); ?></p>
                                            <p class="text-[10px] text-gray-400"><?php echo htmlspecialchars($r['rdc_code']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 pr-4 text-center font-bold text-gray-800"><?php echo $r['total_deliveries']; ?></td>
                                <td class="py-4 pr-4 text-center font-semibold text-green-600"><?php echo $r['on_time']; ?></td>
                                <td class="py-4 pr-4 text-center font-semibold text-red-600"><?php echo $r['delayed']; ?></td>
                                <td class="py-4 pr-4 text-center text-yellow-600"><?php echo $r['pending']; ?></td>
                                <td class="py-4 pr-4 text-center font-bold <?php echo $effColor; ?>"><?php echo $eff; ?>%</td>
                                <td class="py-4 pr-4 text-center text-gray-600"><?php echo $r['avg_delivery_hours'] ?? '-'; ?>h</td>
                                <td class="py-4 min-w-[120px]">
                                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                                        <div class="bg-gradient-to-r <?php echo $barColor; ?> h-2.5 rounded-full transition-all duration-500" style="width: <?php echo min(100, $eff); ?>%"></div>
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
                <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Delivery Records</h2>
                <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full"><?php echo count($details); ?> records</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                            <th class="pb-3 pr-3">Order #</th>
                            <th class="pb-3 pr-3">RDC</th>
                            <th class="pb-3 pr-3">Driver</th>
                            <th class="pb-3 pr-3">Scheduled</th>
                            <th class="pb-3 pr-3">Completed</th>
                            <th class="pb-3 pr-3">Status</th>
                            <th class="pb-3">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/50">
                        <?php foreach ($details as $d): ?>
                        <tr class="hover:bg-white/30 transition">
                            <td class="py-3 pr-3 font-semibold text-gray-800"><?php echo htmlspecialchars($d['order_number']); ?></td>
                            <td class="py-3 pr-3 text-gray-600"><?php echo htmlspecialchars($d['rdc_name']); ?></td>
                            <td class="py-3 pr-3 text-gray-600"><?php echo htmlspecialchars($d['driver_name'] ?? '-'); ?></td>
                            <td class="py-3 pr-3 text-gray-500 text-xs"><?php echo $d['scheduled_date'] ? date('M j, Y H:i', strtotime($d['scheduled_date'])) : '-'; ?></td>
                            <td class="py-3 pr-3 text-gray-500 text-xs"><?php echo $d['completed_date'] ? date('M j, Y H:i', strtotime($d['completed_date'])) : '-'; ?></td>
                            <td class="py-3 pr-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $statusBadge[$d['delivery_status']] ?? 'bg-gray-100 text-gray-600'; ?>"><?php echo $d['delivery_status']; ?></span></td>
                            <td class="py-3 text-gray-600"><?php echo $d['duration_hours'] !== null ? $d['duration_hours'] . 'h' : '-'; ?></td>
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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
<style>
.custom-marker { background: none !important; border: none !important; }
.marker-shop-icon { font-family: 'Material Symbols Rounded'; font-weight: 400; font-style: normal; font-size: 22px; line-height: 1; letter-spacing: normal; }
#delivery-map .leaflet-popup-content-wrapper { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
#delivery-map .leaflet-popup-content { margin: 12px 16px; min-width: 160px; }
</style>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function() {
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
        'NORTH': [[9.75,80.0],[9.5,80.15],[9.0,80.5],[8.7,80.6],[8.9,80.9],[9.3,80.95],[9.6,80.5],[9.75,80.0]],
        'SOUTH': [[6.0,80.2],[6.3,80.4],[6.6,80.3],[6.9,80.85],[6.5,81.0],[6.1,80.6],[6.0,80.2]],
        'EAST': [[8.9,80.6],[8.5,81.0],[7.8,81.7],[7.2,81.8],[6.8,81.5],[7.0,81.0],[7.5,80.8],[8.2,80.7],[8.9,80.6]],
        'WEST': [[8.7,79.8],[8.0,79.9],[7.0,79.95],[6.2,80.1],[6.0,80.2],[6.3,80.4],[6.6,80.3],[7.2,80.0],[7.8,79.9],[8.7,79.8]],
        'CENTRAL': [[8.0,80.5],[7.8,80.7],[7.5,80.8],[7.0,81.0],[6.8,81.5],[7.2,80.9],[7.5,80.5],[7.8,80.2],[8.0,80.5]]
    };
    var map = L.map('delivery-map').setView([7.8731, 80.7718], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    // Add colored region polygons first (below markers)
    ['NORTH','SOUTH','EAST','WEST','CENTRAL'].forEach(function(code) {
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
        poly.on('mouseover', function() { this.setStyle({ fillOpacity: 0.75 }); });
        poly.on('mouseout', function() { this.setStyle({ fillOpacity: fillOpacity }); });
    });
    // Add markers on top - shop/store icons for RDC locations
    ['NORTH','SOUTH','EAST','WEST','CENTRAL'].forEach(function(code) {
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
(function() {
    Chart.defaults.font.family = "'Outfit', 'Segoe UI', system-ui, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#9ca3af';

    var data = <?php echo json_encode($chartData); ?>;
    var el;

    // Stacked bar: On-time vs Delayed vs Pending per RDC
    el = document.getElementById('rdcBreakdownChart');
    if (el && data.labels.length > 0) {
        new Chart(el, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'On-time',  data: data.on_time, backgroundColor: '#10b981', borderRadius: 4, barPercentage: 0.6 },
                    { label: 'Delayed',  data: data.delayed, backgroundColor: '#ef4444', borderRadius: 4, barPercentage: 0.6 },
                    { label: 'Pending',  data: data.pending, backgroundColor: '#f59e0b', borderRadius: 4, barPercentage: 0.6 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16 } } },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, title: { display: true, text: 'Deliveries', font: { size: 10 } } }
                }
            }
        });
    }

    // Combo chart: Efficiency % (bars) + Avg Hours (line)
    el = document.getElementById('efficiencyChart');
    if (el && data.labels.length > 0) {
        new Chart(el, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Efficiency %',
                        data: data.efficiency,
                        backgroundColor: data.efficiency.map(function(v) { return v >= 80 ? '#10b981' : (v >= 50 ? '#f59e0b' : '#ef4444'); }),
                        borderRadius: 6,
                        barPercentage: 0.5,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Avg Hours',
                        data: data.avg_hours,
                        type: 'line',
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139,92,246,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#8b5cf6',
                        borderWidth: 2.5,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16 } } },
                scales: {
                    y:  { beginAtZero: true, max: 100, position: 'left', grid: { color: 'rgba(0,0,0,0.04)' }, title: { display: true, text: 'Efficiency %', font: { size: 10 } } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Avg Hours', font: { size: 10 } } },
                    x:  { grid: { display: false } }
                }
            }
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

