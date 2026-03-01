<?php

require_once __DIR__ . '/../libs/vendor/autoload.php';


use Dompdf\Dompdf;
use Dompdf\Options;


class InvoiceGenerator
{
  public static function generate(array $invoiceData)
  {
    $dompdf = new Dompdf([
      'defaultFont' => 'DejaVu Sans'
    ]);

    $html = self::invoiceHtml($invoiceData);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $order_info = $invoiceData['order_info'];

    $filePath = __DIR__ . "/../invoices/ISDN-Invoice-{$order_info['order_number']}.pdf";

    file_put_contents($filePath, $dompdf->output());

    return $filePath;
  }

  private static function invoiceHtml(array $invoiceData): string
  {
    $order_info = $invoiceData['order_info'];
    $order_items = $invoiceData['order_items'];
    $order_totals = $invoiceData['order_totals'];

    $items_content = '';
    $subtotal = number_format($order_totals['subtotal'], 2);
    $discount_total = number_format($order_totals['discount_total'], 2);
    $tax_amount = number_format($order_totals['tax_amount'], 2);
    $delivery_fee = number_format($order_totals['delivery_fee'], 2);
    $grand_total = number_format($order_totals['grand_total'], 2);

    foreach ($order_items as $item) {
      $items_content .= '
      <tr>
        <td>' . htmlspecialchars($item['product_code']) . '</td>
        <td>' . htmlspecialchars($item['product_name']) . '</td>
        <td>' . htmlspecialchars($item['product_category']) . '</td>
        <td>Rs. ' . number_format($item['unit_price'], 2) . '</td>
        <td>' . (int) $item['quantity'] . '</td>
        <td>Rs. ' . number_format($item['discount_amount'], 2) . '</td>
        <td>Rs. ' . number_format($item['discounted_line_amount'], 2) . '</td>
      </tr>';
    }
    return "
        <html>
        <body style='font-family:Arial;font-size:12px;'>

        <h2>TAX INVOICE</h2>

        <p><strong>Invoice No:</strong> INV-{$order_info['order_number']}</p>
        <p><strong>Order No:</strong> {$order_info['order_number']}</p>

        <hr>

        <p><strong>ISDN – IslandLink Sales Distribution Network</strong><br>
        info@isdn.lk | +94 11 234 5678</p>

        <table width='100%' cellpadding='5' cellspacing='0'>
            <tr>
                <!-- Bill To (Left Side) -->
                <td width='50%' valign='top'>
                    <strong>Bill To:</strong><br>
                    {$order_info['name']}<br>
                    {$order_info['address']}
                </td>

                <!-- Invoice Date (Right Side) -->
                <td width='50%' align='right' valign='bottom'>
                    <strong>Invoice Date:</strong><br>
                    " . (new DateTime($order_info['order_date']))->format('Y/m/d') . "
                </td>
            </tr>
        </table>
        <table width='100%' border='1' cellspacing='0' cellpadding='6'>
            <tr>
                <th>Code</th>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Discount</th>
                <th>Line Total</th>
            </tr>
          
           {$items_content}
        </table>

        <br>

        <table width='75%' align='right'>
            <tr><td align='right'>Subtotal:</td><td align='right'>{$subtotal}</td></tr>
            <tr><td align='right'>Discount:</td><td align='right'>- {$discount_total}</td></tr>
            <tr><td align='right'>VAT (15%):</td><td align='right'>{$tax_amount}</td></tr>
            <tr><td align='right'>Delivery Fee:</td><td align='right'>{$delivery_fee}</td></tr>
            <tr><td align='right'><strong>Grand Total:</strong></td>
                <td align='right'><strong>Rs. {$grand_total}</strong></td></tr>
        </table>

        <div style='
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 11px;'>

            <hr style='margin-bottom:5px;'>
            Page 1

        </div>

        </body>
        </html>";
  }
}
?>