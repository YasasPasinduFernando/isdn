<?php

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//required files
require __DIR__ .'/../libs/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ .'/../libs/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ .'/../libs/vendor/phpmailer/phpmailer/src/SMTP.php';

//Create an instance; passing `true` enables exceptions
class Mailsender
{
  public static function sendMail()
  {
    $orderData = [
      'order_no' => 'ORD-RDCS-260213-1025',
      'order_date' => '13 Feb 2026',
      'customer' => 'Vijaya Stores - Galle',
      'payment_method' => 'Card Payment',
      'status' => 'Pending',
      'grand_total' => 'Rs. 18,751.46',
      'subtotal' => 'Rs. 15,044.75',
      'discount' => 'Rs. 310.25',
      'vat' => '15%',
      'delivery_fee' => 'Rs. 1,450.00',
      'address' => 'No. 62, Matara Road, Galle, Sri Lanka'
    ];

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

    $mail->addAddress('shdinesh.99@gmail.com');     //Add a recipient email  
    $mail->addReplyTo('info@isdn.lk', 'ISDN - IslandLink'); // reply to sender email

    //Content
    $mail->isHTML(true);               //Set email format to HTML
    $mail->Subject = "Your ISDN Order ORD-RDCS-260213-1025 is confirmed!";
    /*$invoicePath = InvoiceGenerator::generate($orderData);

    $mail->addAttachment(
      $invoicePath,
      "ISDN-Invoice-{$orderData['order_no']}.pdf"
    );*/
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

```
  <!-- Container -->
  <table width="680" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.08);">

    <!-- Header -->
    <tr>
      <td style="padding:24px 28px;border-bottom:1px solid #e5e7eb;">
        <table width="100%">
          <tr>
            <td style="vertical-align:middle;">
              <img src="http://localhost:82/phpmailer/dompdf/dompdf/src/Image/icon-192.svg" alt="ISDN" height="42" style="display:block;">
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
            <td><strong>Order Number:</strong></td><td>#ORD-RDCS-260213-1025</td>
            <td><strong>Order Date:</strong></td><td>13 Feb 2026</td>
          </tr>
          <tr>
            <td><strong>Customer:</strong></td><td>Vijaya Stores - Galle</td>
            <td><strong>Sales Ref:</strong></td><td>N/A</td>
          </tr>
          <tr>
            <td><strong>Payment Method:</strong></td><td>Card Payment</td>
            <td><strong>Order Status:</strong></td><td>Pending</td>
          </tr>
          <tr>
            <td><strong>Estimated Delivery:</strong></td><td>15 Feb 2026</td>
            <td><strong>Total Amount:</strong></td><td><strong>Rs. 18,751.46</strong></td>
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
            <th align="right">Price</th>
            <th align="center">Qty</th>
            <th align="right">Discount</th>
            <th align="right">Line Total</th>
          </tr>
          <tr style="border-bottom:1px solid #e5e7eb;">
            <td>PRD-BEV-000001</td>
            <td>Coca-Cola 1L</td>
            <td>Beverages</td>
            <td align="center">Rs. 300.00</td>
            <td align="right">10</td>
            <td align="right">Rs. 150.00</td>
            <td align="right">Rs. 2,850.00</td>
          </tr>
          <tr style="border-bottom:1px solid #e5e7eb;">
            <td>PRD-GNF-000001</td>
            <td>Nestomalt 400G</td>
            <td>Grocery & Food Items</td>
            <td align="center">Rs. 750.00</td>
            <td align="right">7</td>
            <td align="right">Rs. 0.00</td>
            <td align="right">Rs. 5,250.00</td>
          </tr>
          <tr style="border-bottom:1px solid #e5e7eb;">
            <td>PRD-HCP-000001</td>
            <td>Harpic Fresh 500ml</td>
            <td>Home Cleaning Products</td>
            <td align="center">Rs. 380.50</td>
            <td align="right">5</td>
            <td align="right">Rs. 0.00</td>
            <td align="right">Rs. 1,902.50</td>
          </tr>
          <tr style="border-bottom:1px solid #e5e7eb;">
            <td>PRD-HHE-000001</td>
            <td>Sunlight Detergent Powder - 1kg</td>
            <td>Household Essentials</td>
            <td align="center">Rs. 320.50</td>
            <td align="right">5</td>
            <td align="right">Rs. 160.25</td>
            <td align="right">Rs. 1,442.25</td>
          </tr>
          <tr style="border-bottom:1px solid #e5e7eb;">
            <td>PRD-PSC-000001</td>
            <td>Clogard Toothpaste 200g</td>
            <td>Household Essentials</td>
            <td align="center">Rs. 360.00</td>
            <td align="right">10</td>
            <td align="right">Rs. 0.00</td>
            <td align="right">Rs. 3,600.00</td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Price Breakdown -->
    <tr>
      <td style="padding:0 28px 20px;">
        <table width="100%" cellpadding="6" cellspacing="0" style="font-size:13px;">
          <tr>
            <td align="right">Subtotal:</td><td align="right">Rs. 15,044.75</td>
          </tr>
          <tr>
            <td align="right">Discount:</td><td align="right">- Rs. 310.25</td>
          </tr>
          <tr>
            <td align="right">VAT (15%):</td><td align="right">15%</td>
          </tr>
          <tr>
            <td align="right">Delivery Fee:</td><td align="right">Rs. 1,450.00</td>
          </tr>
          <tr>
            <td align="right"><strong>Grand Total:</strong></td>
            <td align="right"><strong>Rs. 18,751.46</strong></td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Delivery Info -->
    <tr>
      <td style="padding:0 28px 24px;font-size:13px;color:#374151;">
        <strong>Delivery Address:</strong><br>
        No. 62, Matara Road, Galle<br><br>
        <strong>Delivery Notes:</strong><br>
        Please deliver between 9 AM – 5 PM.
      </td>
    </tr>

    <!-- CTA Buttons -->
    <tr>
      <td align="center" style="padding:10px 28px 30px;">
        <a href="#" style="background:#0ea5a4;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-size:14px;font-weight:bold;margin-right:10px;">
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
```
  </tr>
</table>

</body>
</html>';
    $mail->Body = $emailContent;

    //$mail->Body = $_POST["message"]; //email message

    // Success sent message alert
    $mail->send();


  }

}

?>