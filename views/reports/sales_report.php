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
require_once __DIR__ . '/../../models/SalesReport.php';

// ── Role-based access control ────────────────────────────────
$allowedRoles = [USER_ROLE_HEAD_OFFICE_MANAGER, USER_ROLE_SYSTEM_ADMIN];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
    flash_message('Access denied. This report requires Head Office Manager or System Admin privileges.', 'error');
    redirect('/index.php?page=login');
}

// ── Initialize model and ensure indexes ──────────────────────
$report = new DeliveryReport($pdo);
$salesReport = new SalesReport($pdo);
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
// ── Fetch data ───────────────────────────────────────────────
$error = null;
try {
    $allRdcs = $report->getAllRdcs();
    $totalSales = $salesReport->getTotalSales($filters);
    $totalPayments = $salesReport->getPaymentsReceived($filters);
    $statusSummary = $salesReport->getStatusSummary($filters);
    $topProduct = $salesReport->getTopProductAndAverageSale($filters);
    $topCustomer = $salesReport->getTopPurchasingCustomerByAmount($filters);
    $topSalesRep = $salesReport->getTopSalesRefByOrderAmount($filters);
    $rdcSalesSummary = $salesReport->getSalesSummaryReport($filters);
    $orderRecords = $salesReport->getOrderReport($filters);
    $topSellingItems = $salesReport->getTopSellingItems($filters);

    $salesSummary = [
        'total_sales' => $totalSales['total_sales'],
        'order_count' => $totalSales['order_count'],
        'payment_received' => $totalPayments['total_payment_received'],
        'pending_count' => 0,
        'pending_sum' => 0,
        'processing_count' => 0,
        'processing_sum' => 0,
        'in_transit_count' => 0,
        'in_transit_sum' => 0,
        'delivered_count' => 0,
        'delivered_sum' => 0,
        'cancelled_count' => 0,
        'cancelled_sum' => 0

    ];
    foreach ($statusSummary as $row) {

        $status = str_replace(' ', '_', $row['status']);

        $salesSummary[$status . '_count'] = (int) $row['order_count'];
        $salesSummary[$status . '_sum'] = (float) $row['total_amount_sum'];
    }
    //print_r($rdcSalesSummary);



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
    $salesSummary = [
        'total_sales' => 0,
        'order_count' => 0,
        'payment_received' => 0,
        'pending_count' => 0,
        'pending_sum' => 0,
        'processing_count' => 0,
        'processing_sum' => 0,
        'in_transit_count' => 0,
        'in_transit_sum' => 0,
        'delivered_count' => 0,
        'delivered_sum' => 0,
        'cancelled_count' => 0,
        'cancelled_sum' => 0

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

    $rankedRdcs = [];
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

    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'ISDN Sales Report', 0, 1, 'C');

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(0, 6, 'Generated: ' . date('Y-m-d H:i:s'), 0, 1);

    $pdf->Ln(5);

    // Sales Summary
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Sales Summary', 0, 1);

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(80, 7, 'Total Sales', 1);
    $pdf->Cell(40, 7, $salesSummary['total_sales'], 1, 1);

    $pdf->Cell(80, 7, 'Order Count', 1);
    $pdf->Cell(40, 7, $salesSummary['order_count'], 1, 1);

    $pdf->Cell(80, 7, 'Payments Received', 1);
    $pdf->Cell(40, 7, $salesSummary['payment_received'], 1, 1);

    $pdf->Ln(6);

    // RDC Sales Summary
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'RDC Sales Summary', 0, 1);

    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(90, 7, 'RDC', 1);
    $pdf->Cell(40, 7, 'Orders', 1);
    $pdf->Cell(50, 7, 'Sales Amount', 1);
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 10);

    foreach ($rdcSalesSummary as $r) {
        $pdf->Cell(90, 7, $r['rdc_name'], 1);
        $pdf->Cell(40, 7, $r['order_count'], 1);
        $pdf->Cell(50, 7, $r['total_sales'], 1);
        $pdf->Ln();
    }

    $pdf->Ln(6);

    // Order Records
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Order Records', 0, 1);

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(30, 7, 'Order No', 1);
    $pdf->Cell(50, 7, 'Customer', 1);
    $pdf->Cell(40, 7, 'Sales Rep', 1);
    $pdf->Cell(30, 7, 'Amount', 1);
    $pdf->Cell(30, 7, 'Status', 1);
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 9);

    foreach ($orderRecords as $o) {
        $pdf->Cell(30, 7, $o['order_no'], 1);
        $pdf->Cell(50, 7, $o['customer_name'], 1);
        $pdf->Cell(40, 7, $o['sales_rep'], 1);
        $pdf->Cell(30, 7, $o['total_amount'], 1);
        $pdf->Cell(30, 7, $o['status'], 1);
        $pdf->Ln();
    }

    $pdf->Output('D', 'sales_report_' . date('Y-m-d') . '.pdf');
    exit;
}

// ── CSV Export ───────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // Excel UTF-8 support

    // Report Header
    fputcsv($out, ['ISDN Sales Report']);
    fputcsv($out, ['Generated At', date('Y-m-d H:i:s')]);
    fputcsv($out, []);

    // Sales Summary
    fputcsv($out, ['Sales Summary']);
    fputcsv($out, ['Metric', 'Value']);

    fputcsv($out, ['Total Sales', $salesSummary['total_sales']]);
    fputcsv($out, ['Order Count', $salesSummary['order_count']]);
    fputcsv($out, ['Payments Received', $salesSummary['payment_received']]);

    fputcsv($out, []);

    // RDC Sales Summary
    fputcsv($out, ['RDC Sales Summary']);
    fputcsv($out, ['RDC', 'Orders', 'Sales Amount']);

    print_r($rdcSalesSummary);
    foreach ($rdcSalesSummary as $r) {
        fputcsv($out, [
            $r['rdc_name'],
            $r['total_sales'],
            $r['total_orders']
        ]);
    }

    fputcsv($out, []);

    // Order Records
    fputcsv($out, ['Order Records']);
    fputcsv($out, ['Order No', 'Customer', 'Sales Rep', 'Total Amount', 'Status', 'Date']);

    foreach ($orderRecords as $o) {
        fputcsv($out, [
            $o['order_no'],
            $o['customer_name'],
            $o['sales_rep'],
            $o['total_amount'],
            $o['status'],
            $o['created_at']
        ]);
    }

    fclose($out);
    exit;
}

// ── Page output (include header only after exports are handled) ─
require_once __DIR__ . '/../../includes/header.php';

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
                <a href=""
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

            <input type="hidden" name="page" value="sales-report">

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
                <div class="flex justify-between items-start gap-4">

                    <!-- Text Section -->
                    <div class="min-w-0 flex-1">

                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            TOTAL SALES
                        </p>

                        <h3 class="text-2xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            LKR <?php echo number_format((float) ($salesSummary['total_sales'] ?? 0), 2); ?>
                        </h3>

                        <p class="text-teal-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">payments</span>
                            All Order Sales
                        </p>

                    </div>

                    <!-- Icon Section -->
                    <div class="w-12 h-12 flex-shrink-0 rounded-2xl bg-teal-100/50
                    flex items-center justify-center text-teal-600
                    group-hover:bg-teal-500 group-hover:text-white
                    transition-colors duration-300 backdrop-blur-sm">
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
                            <?php echo (int) ($salesSummary['order_count'] ?? 0); ?>
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
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift
            border border-gray-200 border-l-4 border-l-yellow-400">

                <div class="flex justify-between items-start gap-4">

                    <!-- Text Section -->
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Payments Received
                        </p>

                        <h3 class="text-2xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            LKR <?php echo number_format((float) ($salesSummary['payment_received'] ?? 0), 2); ?>
                        </h3>

                        <p class="text-xs font-semibold mt-2 flex items-center text-yellow-600">
                            <span class="material-symbols-rounded text-sm mr-1">payment_arrow_down</span>
                            Cash + Card Payments
                        </p>
                    </div>

                    <!-- Icon Section -->
                    <div class="w-12 h-12 flex-shrink-0 rounded-2xl bg-yellow-100/50
                    flex items-center justify-center text-yellow-600
                    group-hover:bg-yellow-400 group-hover:text-white
                    transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">pending</span>
                    </div>

                </div>
            </div>
            <!-- Delivered -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift
            border border-gray-200 border-l-4 border-l-green-500">

                <div class="flex justify-between items-start gap-4">

                    <!-- Text Section -->
                    <div class="min-w-0 flex-1">

                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Total Delivered
                        </p>

                        <h3 class="text-2xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            LKR <?php echo number_format((float) ($salesSummary['delivered_sum'] ?? 0), 2); ?>
                        </h3>

                        <p class="text-gray-800 mt-2 font-['Outfit']">
                            Count: <?php echo (int) ($salesSummary['delivered_count'] ?? 0); ?>
                        </p>

                        <p class="text-green-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">check_circle</span>
                            Successfully Delivered
                        </p>

                    </div>

                    <!-- Icon Section -->
                    <div class="w-12 h-12 flex-shrink-0 rounded-2xl bg-green-100/50
                    flex items-center justify-center text-green-600
                    group-hover:bg-green-500 group-hover:text-white
                    transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">done_all</span>
                    </div>

                </div>
            </div>
            <!-- Monthly Growth -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift
            border border-gray-200 border-l-4 border-l-purple-500">

                <div class="flex justify-between items-start gap-4">

                    <!-- Text Section -->
                    <div class="min-w-0 flex-1">

                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Total Pending
                        </p>

                        <h3 class="text-2xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            LKR <?php echo number_format((float) ($salesSummary['pending_sum'] ?? 0), 2); ?>
                        </h3>

                        <p class="text-gray-800 mt-2 font-['Outfit']">
                            Count: <?php echo (int) ($salesSummary['pending_count'] ?? 0); ?>
                        </p>

                        <p class="text-purple-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">hourglass_top</span>
                            Awaiting processing
                        </p>

                    </div>

                    <!-- Icon Section -->
                    <div class="w-12 h-12 flex-shrink-0 rounded-2xl bg-purple-100/50
                    flex items-center justify-center text-purple-500
                    group-hover:bg-purple-500 group-hover:text-white
                    transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">clock_loader_60</span>
                    </div>

                </div>

            </div>
            <!-- Low Stock -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift
            border border-gray-200 border-l-4 border-l-red-500">

                <div class="flex justify-between items-start gap-4">

                    <!-- Text Section -->
                    <div class="min-w-0 flex-1">

                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Total Cancelled
                        </p>

                        <h3 class="text-2xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            LKR <?php echo number_format((float) ($salesSummary['cancelled_sum'] ?? 0), 2); ?>
                        </h3>

                        <p class="text-gray-800 mt-2 font-['Outfit']">
                            Count: <?php echo (int) ($salesSummary['cancelled_count'] ?? 0); ?>
                        </p>

                        <p class="text-red-500 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">warning</span>
                            Cancelled Orders
                        </p>

                    </div>

                    <!-- Icon Section -->
                    <div class="w-12 h-12 flex-shrink-0 rounded-2xl bg-red-100/50
                    flex items-center justify-center text-red-500
                    group-hover:bg-red-500 group-hover:text-white
                    transition-colors duration-300 backdrop-blur-sm">
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
                <p class="text-lg font-bold text-gray-800"><?= $topProduct['product_name'] ?? 'No Data' ?></span></p>
                <p class="text-sm text-gray-600 mt-1">Category: <span
                        class="font-semibold text-emerald-700"></span><span
                        class="font-semibold"><?= $topProduct['category_name'] ?? '-' ?></span> | Avg Sale: <span
                        class="font-semibold"><?= (int) ($topProduct['avg_qty_per_order'] ?? 0) ?></span></p>
            </div>
            <div class="rounded-2xl border border-yellow-200 bg-yellow-50/50 p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-rounded text-yellow-600">trending_up</span>
                    <span class="material-symbols-rounded text-yellow-600">person</span>
                    <p class="text-sm font-bold text-yellow-700 uppercase tracking-wide">
                        Top Purchasing Customer
                    </p>
                </div>

                <p class="text-lg font-bold text-gray-800"><?= $topCustomer['customer_name'] ?? 'No Data' ?></p>

                <p class="text-sm text-gray-600 mt-1">
                    RDC:
                    <span class="font-semibold text-yellow-700"><?= $topCustomer['rdc_name'] ?? '-' ?></span>
                    | Purchases:
                    <span
                        class="font-semibold"><?= number_format((float) ($topCustomer['total_purchased_amount'] ?? 0), 2) ?></span>
                </p>
            </div>
            <div class="rounded-2xl border border-purple-200 bg-purple-50/80 p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-rounded text-purple-600">rewarded_ads</span>
                    <p class="text-sm font-bold text-purple-700 uppercase tracking-wide">
                        Top Performing Sales Rep.
                    </p>
                </div>

                <p class="text-lg font-bold text-gray-800"><?= $topSalesRep['sales_ref_name'] ?? 'No Data' ?></p>

                <p class="text-sm text-gray-600 mt-1">
                    RDC:
                    <span class="font-semibold text-purple-700"><?= $topSalesRep['rdc_name'] ?? '-' ?></span>
                    | Orders:
                    <span class="font-semibold text-purple-700"><?= (float) ($topSalesRep['order_count'] ?? 0) ?></span>
                    | Sales Amount:
                    <span
                        class="font-semibold"><?= number_format((float) ($topSalesRep['total_order_amount'] ?? 0), 2) ?></span>
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
            <?php if (empty($rdcSalesSummary)): ?>
                <div class="text-center py-12">
                    <p class="text-sm text-gray-400">No sales data available for the selected filters.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-4">RDC</th>
                                <th class="pb-3 pr-4 text-right">Total Sales</th>
                                <th class="pb-3 pr-4 text-right">Total Orders</th>
                                <th class="pb-3 pr-4 text-right">Payments</th>
                                <th class="pb-3 pr-4 text-right">Total Delivered</th>
                                <th class="pb-3 pr-4 text-right">Delivered Count</th>
                                <th class="pb-3 pr-4 text-right">Total Pending</th>
                                <th class="pb-3 pr-4 text-right">Pendin Count</th>
                                <th class="pb-3 pr-4 text-right">Total Cancelled</th>
                                <th class="pb-3 pr-4 text-right">Cancelled Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50">
                            <?php foreach ($rdcSalesSummary as $rss): ?>
                                <tr class="hover:bg-white/30 transition">
                                    <td class="py-4 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-xl bg-indigo-100/50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-rounded text-base">warehouse</span>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800">
                                                    <?php echo htmlspecialchars($rss['rdc_name']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-4 text-right font-bold text-blue-600">
                                        <?php echo htmlspecialchars(number_format($rss['total_sales']), 0); ?>
                                    </td>
                                    <td class="py-4 pr-4 text-right font-semibold text-blue-600">
                                        <?php echo htmlspecialchars($rss['total_orders']); ?>

                                    </td>
                                    <td class="py-4 pr-4 text-right font-semibold text-yellow-600">
                                        <?php echo htmlspecialchars(number_format($rss['payment_received'], 2)); ?>

                                    </td>
                                    <td class="py-4 pr-4 text-right text-green-600">
                                        <?php echo htmlspecialchars(number_format($rss['total_delivered'], 2)); ?>
                                    </td>
                                    <td class="py-4 pr-4 text-right text-green-600">
                                        <?php echo htmlspecialchars($rss['delivered_count']); ?>
                                    </td>
                                    <td class="py-4 pr-4 text-right text-purple-600">
                                        <?php echo htmlspecialchars(number_format($rss['total_pending'], 2)); ?>
                                    </td>
                                    <td class="py-4 pr-4 text-right text-purple-600">
                                        <?php echo htmlspecialchars($rss['pending_count']); ?>
                                    </td>
                                    <td class="py-4 pr-4 text-right text-red-600">
                                        <?php echo htmlspecialchars(number_format($rss['total_cancelled'], 2)); ?>
                                    </td>
                                    <td class="py-4 pr-4 text-right text-red-600">
                                        <?php echo htmlspecialchars($rss['cancelled_count']); ?>
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
            <?php if (empty($topSellingItems)): ?>
                <div class="text-center py-12">
                    <p class="text-sm text-gray-400">No sales records available for the selected filters.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-4 text-left">Product Name</th>
                                <th class="pb-3 pr-4 text-right">Code</th>
                                <th class="pb-3 pr-4 text-right">Category</th>
                                <th class="pb-3 pr-4 text-right">Total Sales Count</th>
                                <th class="pb-3 pr-4 text-right">Total Sales Amount</th>
                                <th class="pb-3 pr-4 text-right">Avg. Sale</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50">
                            <?php foreach ($topSellingItems as $tsi): ?>
                                <tr class="hover:bg-white/30 transition">
                                    <td class="py-4 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-xl bg-indigo-100/50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-rounded text-base">shopping_bag</span>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800">
                                                    <?php echo htmlspecialchars($tsi['product_name']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-4 text-right font-bold text-blue-600">
                                        <?php echo htmlspecialchars($tsi['product_code'], 0); ?>
                                    </td>
                                    <td class="py-4 pr-4 text-right font-semibold text-blue-600">
                                        <?php echo htmlspecialchars($tsi['category']); ?>

                                    </td>
                                    <td class="py-4 pr-4 text-right font-semibold text-yello-600">
                                        <?php echo htmlspecialchars($tsi['total_sales_count']); ?>

                                    </td>
                                    <td class="py-4 pr-4 text-right text-green-600">
                                        <?php echo htmlspecialchars(number_format($tsi['total_sales_amount'], 2)); ?>
                                    </td>
                                    <td class="py-4 pr-4 text-right text-yellow-600">
                                        <?php echo htmlspecialchars($tsi['average_sale']); ?>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Detailed Delivery Records -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center space-x-3 mb-6">
                <span class="material-symbols-rounded text-orange-500 text-2xl">list_alt</span>
                <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Order Records</h2>
                <span
                    class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full"><?php echo count($orderRecords); ?>
                    records</span>
            </div>
            <?php if (empty($orderRecords)): ?>
                <div class="text-center py-12">
                    <p class="text-sm text-gray-400">No sales records available for the selected filters.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-3">Order #</th>
                                <th class="pb-3 pr-3">Customer</th>
                                <th class="pb-3 pr-3">Sales Rep.</th>
                                <th class="pb-3 pr-3">RDC</th>
                                <th class="pb-3 pr-3">Date</th>
                                <th class="pb-3 pr-3">Amount</th>
                                <th class="pb-3 pr-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50">
                            <?php foreach ($orderRecords as $or): ?>
                                <tr class="hover:bg-white/30 transition">
                                    <td class="py-3 pr-3 font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($or['order_number']); ?>
                                    </td>
                                    <td class="py-3 pr-3 text-gray-600"><?php echo htmlspecialchars($or['customer_name']); ?>
                                    </td>
                                    <td class="py-3 pr-3 text-gray-600">
                                        <?php echo htmlspecialchars($or['sales_ref_name'] ?? '-'); ?>
                                    </td>
                                    <td class="py-3 pr-3 text-gray-500 text-xs">
                                        <?php echo 'RDC' ?>
                                    </td>
                                    <td class="py-3 pr-3 text-gray-500 text-xs">
                                        <?php echo $or['order_date'] ?>
                                    </td>
                                    <td class="py-3 pr-3 text-gray-500 text-xs">
                                        <?php echo $or['amount'] ?>
                                    </td>
                                    <td class="py-3 pr-3 text-gray-800 text-xs">
                                        <?php echo ucwords($or['status']) ?>
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
    document.addEventListener("DOMContentLoaded", async function () {
        const chartCanvas = document.getElementById("rdcSalesWormChart");

        if (!chartCanvas) return;

        const rdcColors = {
            "Northern RDC": "#ef4444",
            "Southern RDC": "#8b5cf6",
            "Central RDC": "#10b981",
            "Western RDC": "#f59e0b",
            "Eastern RDC": "#0ea5e9"
        };

        let salesChart = null;

        async function loadSalesChart(startDate, endDate, rdcId = "") {
            try {
                const url = new URL("index.php", window.location.href);
                url.searchParams.set("page", "sales-report");
                url.searchParams.set("action", "chart");
                url.searchParams.set("start_date", startDate);
                url.searchParams.set("end_date", endDate);

                if (rdcId) {
                    url.searchParams.append("rdc_id", rdcId);
                }

                const response = await fetch(url.toString(), {
                    method: "GET",
                    headers: {
                        "Accept": "application/json"
                    }
                });

                if (!response.ok) {
                    throw new Error("Failed to fetch chart data.");
                }

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || "Unable to load chart data.");
                }

                const salesSeriesByRdc = result.data || {};

                const allDates = [...new Set(
                    Object.values(salesSeriesByRdc)
                        .flat()
                        .map(item => item.date)
                )].sort();

                const datasets = Object.entries(salesSeriesByRdc).map(([rdc, points]) => {
                    const salesMap = {};
                    points.forEach(p => {
                        salesMap[p.date] = Number(p.sales);
                    });

                    return {
                        label: rdc,
                        data: allDates.map(date => salesMap[date] ?? null),
                        borderColor: rdcColors[rdc] || "#64748b",
                        backgroundColor: rdcColors[rdc] || "#64748b",
                        tension: 0.45,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBorderWidth: 2,
                        fill: false,
                        spanGaps: true

                    };
                });

                if (salesChart) {
                    salesChart.destroy();
                }

                salesChart = new Chart(chartCanvas, {
                    type: "line",
                    data: {
                        labels: allDates,
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
                                        return "Rs. " + Number(value).toLocaleString();
                                    }
                                },
                                grid: {
                                    color: "rgba(15,23,42,0.06)"
                                }
                            }
                        }
                    }
                });

            } catch (error) {
                console.error("Chart load error:", error);
            }
        }

        const startDate = <?= json_encode($filters['start_date'] ?? '') ?>;
        const endDate = <?= json_encode($filters['end_date'] ?? '') ?>;
        const rdcId = <?= json_encode($filters['rdc_id'] ?? '') ?>;
        console.log(startDate);
        console.log(endDate);

        await loadSalesChart(startDate, endDate, rdcId);
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>