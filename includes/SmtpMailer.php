<?php
/**
 * Lightweight SMTP Mailer for sending emails via Gmail SMTP
 * No external libraries required - uses PHP sockets directly.
 */
class SmtpMailer
{
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;
    private string $lastError = '';

    public function __construct(string $host, int $port, string $username, string $password, string $fromEmail, string $fromName)
    {
        $this->host      = $host;
        $this->port      = $port;
        $this->username  = $username;
        $this->password  = $password;
        $this->fromEmail = $fromEmail;
        $this->fromName  = $fromName;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * Send an HTML email via SMTP
     */
    public function send(string $to, string $subject, string $htmlBody): bool
    {
        $this->lastError = '';

        // Connect to SMTP server with TLS
        $socket = @fsockopen('ssl://' . $this->host, $this->port, $errno, $errstr, 30);

        if (!$socket) {
            $this->lastError = "Connection failed: {$errstr} ({$errno})";
            return false;
        }

        // Set timeout
        stream_set_timeout($socket, 30);

        try {
            // Read server greeting
            $this->getResponse($socket);

            // EHLO
            $this->sendCommand($socket, 'EHLO ' . gethostname());

            // AUTH LOGIN
            $this->sendCommand($socket, 'AUTH LOGIN');

            // Username (base64 encoded)
            $this->sendCommand($socket, base64_encode($this->username));

            // Password (base64 encoded)
            $response = $this->sendCommand($socket, base64_encode($this->password));

            if (strpos($response, '235') === false) {
                $this->lastError = "Authentication failed: {$response}";
                fclose($socket);
                return false;
            }

            // MAIL FROM
            $this->sendCommand($socket, 'MAIL FROM:<' . $this->fromEmail . '>');

            // RCPT TO
            $this->sendCommand($socket, 'RCPT TO:<' . $to . '>');

            // DATA
            $this->sendCommand($socket, 'DATA');

            // Build email headers and body
            $message  = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $message .= "To: {$to}\r\n";
            $message .= "Subject: {$subject}\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "X-Mailer: ISDN-SMTP\r\n";
            $message .= "Date: " . date('r') . "\r\n";
            $message .= "Message-ID: <" . uniqid('isdn_') . "@" . gethostname() . ">\r\n";
            $message .= "\r\n";
            $message .= $htmlBody;
            $message .= "\r\n.\r\n";

            $response = $this->sendCommand($socket, $message, false);

            // QUIT
            $this->sendCommand($socket, 'QUIT');

            fclose($socket);

            // Check if message was accepted (250 OK)
            if (strpos($response, '250') !== false) {
                return true;
            }

            $this->lastError = "Message rejected: {$response}";
            return false;

        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            if (is_resource($socket)) {
                fclose($socket);
            }
            return false;
        }
    }

    /**
     * Send a command and get the response
     */
    private function sendCommand($socket, string $command, bool $addCrlf = true): string
    {
        $data = $addCrlf ? $command . "\r\n" : $command;
        fwrite($socket, $data);
        return $this->getResponse($socket);
    }

    /**
     * Read response from server
     */
    private function getResponse($socket): string
    {
        $response = '';
        $timeout  = time() + 10;

        while (time() < $timeout) {
            $line = fgets($socket, 512);
            if ($line === false) break;
            $response .= $line;

            // If the 4th character is a space, this is the last line
            if (isset($line[3]) && $line[3] === ' ') break;
        }

        return trim($response);
    }
}
