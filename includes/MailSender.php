<?php

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//required files
require __DIR__ . '/../libs/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/../libs/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/../libs/vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '/../includes/InvoiceGenerator.php';


//Create an instance; passing `true` enables exceptions
class Mailsender
{
  public static function sendMailAndGenerateInvoice($invoiceData)
  {
    $deliveryNotes = $invoiceData['delivery_notes'];
    $order_info = $invoiceData['order_info'];
    $order_items = $invoiceData['order_items'];
    $order_totals = $invoiceData['order_totals'];
    $sender_email = $invoiceData['sender_email'] ?? '';


    $mail = new PHPMailer(true);

    //Server settings
    $mail->isSMTP();                              //Send using SMTP
    $mail->Host = 'smtp.gmail.com';       //Set the SMTP server to send through
    $mail->SMTPAuth = true;             //Enable SMTP authentication
    $mail->Username = 'cl.shdinesh@gmail.com';   //SMTP write your email
    $mail->Password = 'ihlm dkag gisk ywkj';      //SMTP password
    $mail->SMTPSecure = 'ssl';            //Enable implicit SSL encryption
    $mail->Port = 465;

    //Recipients
    $mail->setFrom('sales@isdn.lk', 'ISDN - IslandLink');
    $recipient = trim($sender_email ?? '');
    //Validate and Add a recipient email  
    if (!empty($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
      $mail->addAddress($recipient);
    } else {
      $mail->addAddress('cl.shdinesh@gmail.com');
    }
    $mail->addReplyTo('info@isdn.lk', 'ISDN - IslandLink'); // reply to sender email

    //Content
    $mail->isHTML(true);               //Set email format to HTML
    $mail->Subject = "Your ISDN Order " . $order_info['order_number'] . " is confirmed!";
    $invoicePath = InvoiceGenerator::generate($invoiceData);

    $mail->addAttachment(
      $invoicePath,
      "ISDN-Invoice-{$order_info['order_number']}.pdf"
    );
    $logoPath = __DIR__ . '/../assets/images/icons/icon-192.png';
    $logoCid = 'isdn_logo_cid'; // any unique id

    if (!file_exists($logoPath)) {
      throw new Exception("Logo file not found: {$logoPath}");
    }

    // Add embedded image
    $mail->addEmbeddedImage($logoPath, $logoCid, 'icon-192.png');

    $items_content = '';
    foreach ($order_items as $item) {

      $items_content .= '
      <tr style="border-bottom:1px solid #e5e7eb;">
        <td>' . htmlspecialchars($item['product_code']) . '</td>
        <td>' . htmlspecialchars($item['product_name']) . '</td>
        <td>' . htmlspecialchars($item['product_category']) . '</td>
        <td>Rs. ' . number_format($item['unit_price'], 2) . '</td>
        <td align="center">' . (int) $item['quantity'] . '</td>
        <td align="right">Rs. ' . number_format($item['discount_amount'], 2) . '</td>
        <td align="right">Rs. ' . number_format($item['discounted_line_amount'], 2) . '</td>
      </tr>';
    }

    $orderDate = '';
    $estimatedDate = '';

    if (!empty($order_info['order_date'])) {
      $orderDate = (new DateTime($order_info['order_date']))->format('d M, Y');
    }
    if (!empty($order_info['estimated_date'])) {
      $estimatedDate = (new DateTime($order_info['estimated_date']))->format('d M, Y');
    }

    $emailContent = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Order Confirmation</title>
</head>
<body style="margin:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;">
    <!-- Wrapper -->

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb;padding:24px;font-family:Arial,Helvetica,sans-serif;">
  <tr>
    <td align="center">

  <!-- Container -->
  <table width="680" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.08);">

    <!-- Header -->
    <tr>
      <td style="padding:24px 28px;border-bottom:1px solid #e5e7eb;">
        <table width="100%">
          <tr>
            <td style="vertical-align:middle;">
               <img src="cid:' . $logoCid . '"  alt="ISDN" height="42" style="display:block;">
            </td>
            <td align="right" style="font-size:13px;color:#6b7280;">
              <strong>ISDN</strong><br>
              IslandLink Sales Distribution Network
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Intro -->
    <tr>
      <td style="padding:28px;">
        <h2 style="margin:0 0 8px;font-size:22px;color:#111827;">
          Thank you for your order! 🎉
        </h2>
        <p style="margin:0;color:#4b5563;font-size:14px;line-height:1.6;">
          Your order has been successfully placed and is now being processed. Below is a summary of your purchase.
        </p>
      </td>
    </tr>

    <!-- Order Info -->
    <tr>
      <td style="padding:0 28px 20px;">
        <table width="100%" cellpadding="8" cellspacing="0" style="background:#f9fafb;border-radius:10px;font-size:13px;">
          <tr>
            <td><strong>Order Number:</strong></td><td>#' . $order_info['order_number'] . '</td>
            <td><strong>Order Date:</strong></td><td>' . htmlspecialchars($orderDate) . '</td>
          </tr>
          <tr>
            <td><strong>Customer:</strong></td><td>' . $order_info['customer'] . '</td>
            <td><strong>Sales Ref:</strong></td><td>N/A</td>
          </tr>
          <tr>
            <td><strong>Payment Method:</strong></td><td>Card Payment</td>
            <td><strong>Order Status:</strong></td><td>Pending</td>
          </tr>
          <tr>
            <td><strong>Estimated Delivery:</strong></td><td>' . htmlspecialchars($estimatedDate) . '</td>
            <td><strong>Total Amount:</strong></td><td><strong>' . number_format($order_info['total_amount'], 2) . '</td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Items Table -->
    <tr>
      <td style="padding:0 28px 20px;">
        <h3 style="margin:0 0 10px;font-size:16px;color:#111827;">Order Products</h3>
        <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;font-size:13px;">
          <tr style="background:#f3f4f6;color:#374151;">
            <th align="left">Code</th>
            <th align="left">Product</th>
            <th align="left">Category</th>
            <th align="left">Price</th>
            <th align="center">Qty</th>
            <th align="right">Discount</th>
            <th align="right">Line Total</th>
          </tr>
' . $items_content . '
        </table>
      </td>
    </tr>

    <!-- Price Breakdown -->
    <tr>
      <td style="padding:0 32px 20px;">
        <table width="100%" cellpadding="5" cellspacing="0" style="font-size:13px;">
          <tr>
            <td align="right">Subtotal:</td><td align="right">' . number_format($order_totals['subtotal'], 2) . '</td>
          </tr>
          <tr>
            <td align="right">Discount:</td><td align="right">- ' . number_format($order_totals['discount_total'], 2) . '</td>
          </tr>
          <tr>
            <td align="right">VAT (15%):</td><td align="right">' . number_format($order_totals['tax_amount'], 2) . '</td>
          </tr>
          <tr>
            <td align="right">Delivery Fee:</td><td align="right">' . number_format($order_totals['delivery_fee'], 2) . '</td>
          </tr>
          <tr>
            <td align="right"><strong>Grand Total:</strong></td>
            <td align="right"><strong>' . number_format($order_totals['grand_total'], 2) . '</strong></td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Delivery Info -->
    <tr>
      <td style="padding:0 28px 24px;font-size:13px;color:#374151;">
        <strong>Delivery Address:</strong><br>
        ' . $order_info['address'] . '<br>
        <strong>Delivery Notes:</strong><br>
        ' . $deliveryNotes . '
      </td>
    </tr>

    <!-- CTA Buttons -->
    <tr>
      <td align="center" style="padding:10px 28px 30px;">
        <a href="' . APP_URL . '/index.php?page=tracking&order_id=' . $order_info['order_number'] . '" style="background:#0ea5a4;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-size:14px;font-weight:bold;margin-right:10px;">
          Track Order
        </a>
        <a href="mailto:info@isdn.lk" style="background:#e5e7eb;color:#111827;text-decoration:none;padding:12px 22px;border-radius:8px;font-size:14px;font-weight:bold;">
          Contact Support
        </a>
      </td>
    </tr>

    <!-- Footer -->
    <tr>
      <td style="background:#f9fafb;padding:20px 28px;font-size:12px;color:#6b7280;">
        <p style="margin:0 0 8px;">
          📧 info@isdn.lk | ☎ +94 11 234 5678
        </p>
        <p style="margin:0 0 8px;">
          RDC Regions:
          Colombo • Jaffna • Galle • Batticaloa • Kandy
        </p>
        <p style="margin:0;">
          Thank you for shopping with us.<br>
          © ISDN – IslandLink Sales Distribution Network
        </p>
      </td>
    </tr>

  </table>

</td>
  </tr>
</table>

</body>
</html>';
    $mail->Body = $emailContent;

    //$mail->Body = $_POST["message"]; //email message

    // Success sent message alert
    $mail->send();

    return $invoicePath;
  }

}

?>