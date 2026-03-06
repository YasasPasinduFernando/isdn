<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/SalesReport.php';

$action = $_GET['action'] ?? 'view';
$salesReport = new SalesReport($pdo);

switch ($action) {
    case 'chart':
        header('Content-Type: application/json');
        generateReportChart($salesReport);
        exit;

    default:
        require __DIR__ . '/../../views/reports/sales_report.php';
        exit;
}

/**
 * Return sales chart data as JSON
 */
function generateReportChart(SalesReport $salesReport): void
{
    try {
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date'   => $_GET['end_date'] ?? date('Y-m-d'),
        ];

        if (!empty($_GET['rdc_id'])) {
            $filters['rdc_id'] = (int) $_GET['rdc_id'];
        }

        $rows = $salesReport->getDailySalesByRdc($filters);

        $grouped = [];

        foreach ($rows as $row) {
            $rdcName = $row['rdc_name'];

            if (!isset($grouped[$rdcName])) {
                $grouped[$rdcName] = [];
            }

            $grouped[$rdcName][] = [
                'date'  => $row['sales_date'],
                'sales' => (float) $row['total_sales']
            ];
        }

        echo json_encode([
            'success' => true,
            'data'    => $grouped
        ]);
    } catch (Throwable $e) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}