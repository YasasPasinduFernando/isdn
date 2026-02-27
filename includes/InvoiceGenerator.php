<?php

require_once __DIR__ . '/../libs/vendor/autoload.php';


use Dompdf\Dompdf;
use Dompdf\Options;


class InvoiceGenerator
{
    public static function generate(array $order): string
    {
        $dompdf = new Dompdf([
            'defaultFont' => 'DejaVu Sans'
        ]);

        $html = self::invoiceHtml($order);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filePath = __DIR__ . "/../invoices/ISDN-Invoice-{$order['order_no']}.pdf";

        file_put_contents($filePath, $dompdf->output());

        return $filePath;
    }

    private static function invoiceHtml(array $o): string
    {
        return "
        <html>
        <body style='font-family:Arial;font-size:12px;'>

        <h2>TAX INVOICE</h2>

        <p><strong>Invoice No:</strong> INV-{$o['order_no']}</p>
        <p><strong>Order No:</strong> {$o['order_no']}</p>

        <hr>

        <p><strong>ISDN – IslandLink Sales Distribution Network</strong><br>
        info@isdn.lk | +94 11 234 5678</p>

        <p><strong>Bill To:</strong><br>{$o['customer']}<br>{$o['address']}</p>

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
          <tr>
            <td>PRD-BEV-000001</td>
            <td>Coca-Cola 1L</td>
            <td>Beverages</td>
            <td>Rs. 300.00</td>
            <td>10</td>
            <td>Rs. 150.00</td>
            <td>Rs. 2,850.00</td>
          </tr>
          <tr>
            <td>PRD-GNF-000001</td>
            <td>Nestomalt 400G</td>
            <td>Grocery & Food Items</td>
            <td>Rs. 750.00</td>
            <td>7</td>
            <td>Rs. 0.00</td>
            <td>Rs. 5,250.00</td>
          </tr>
          <tr>
            <td>PRD-HCP-000001</td>
            <td>Harpic Fresh 500ml</td>
            <td>Home Cleaning Products</td>
            <td>Rs. 380.50</td>
            <td>5</td>
            <td>Rs. 0.00</td>
            <td>Rs. 1,902.50</td>
          </tr>
          <tr>
            <td>PRD-HHE-000001</td>
            <td>Sunlight Detergent Powder - 1kg</td>
            <td>Household Essentials</td>
            <td>Rs. 320.50</td>
            <td>5</td>
            <td>Rs. 160.25</td>
            <td>Rs. 1,442.25</td>
          </tr>
          <tr>
            <td>PRD-PSC-000001</td>
            <td>Clogard Toothpaste 200g</td>
            <td>Personal Care</td>
            <td>Rs. 360.00</td>
            <td>10</td>
            <td>Rs. 0.00</td>
            <td>Rs. 3,600.00</td>
          </tr>
           
        </table>

        <br>

        <table width='75%' align='right'>
            <tr><td align='right'>Subtotal:</td><td align='right'>{$o['subtotal']}</td></tr>
            <tr><td align='right'>Discount:</td><td align='right'>{$o['discount']}</td></tr>
            <tr><td align='right'>VAT (15%):</td><td align='right'>{$o['vat']}</td></tr>
            <tr><td align='right'>Delivery Fee:</td><td align='right'>{$o['delivery_fee']}</td></tr>
            <tr><td align='right'><strong>Grand Total:</strong></td>
                <td align='right'><strong>Rs. {$o['grand_total']}</strong></td></tr>
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