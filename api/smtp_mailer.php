<?php
// Lightweight zero-dependency SMTP Client with SSL/TLS support
// Built specifically for cPanel Exim authenticated SMTP sending.

class SimpleSMTP {
    private $host;
    private $port;
    private $username;
    private $password;
    private $timeout;
    private $socket;
    private $debug = [];

    public function __construct($host, $port, $username, $password, $timeout = 15) {
        $this->host = $host;
        $this->port = (int)$port;
        $this->username = $username;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    public function getLogs() {
        return $this->debug;
    }

    private function log($msg) {
        $this->debug[] = $msg;
    }

    private function readResponse() {
        $response = '';
        while ($str = fgets($this->socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) === ' ') {
                break;
            }
        }
        $this->log("SERVER: " . trim($response));
        return $response;
    }

    private function sendCommand($cmd, $expectedCode = null) {
        $this->log("CLIENT: " . (strpos($cmd, 'AUTH') !== false || strlen($cmd) > 100 ? substr($cmd, 0, 15) . '...' : trim($cmd)));
        fwrite($this->socket, $cmd . "\r\n");
        $res = $this->readResponse();
        if ($expectedCode !== null) {
            $code = substr($res, 0, 3);
            if ($code !== (string)$expectedCode) {
                throw new \Exception("SMTP command failed. Expected {$expectedCode}, got: " . trim($res));
            }
        }
        return $res;
    }

    public function send($to, $subject, $htmlBody, $fromName = "Paws & Hooves Mobile Vet", $replyTo = null) {
        // Connect via SSL (ssl://mail.pawhooves.co.ke:465) or standard
        $remote = ($this->port == 465 ? "ssl://" : "") . $this->host;
        
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $this->socket = @stream_socket_client(
            $remote . ':' . $this->port,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$this->socket) {
            // Fallback: try localhost on port 25 or 587 if external fails locally
            $this->log("Failed connecting to {$remote}:{$this->port} ({$errno} - {$errstr}), trying localhost:25");
            $this->socket = @stream_socket_client('tcp://127.0.0.1:25', $errno, $errstr, 5);
            if (!$this->socket) {
                throw new \Exception("Could not connect to SMTP server {$this->host}:{$this->port} ({$errno}: {$errstr})");
            }
        }

        stream_set_timeout($this->socket, $this->timeout);
        $this->readResponse();

        // EHLO
        $this->sendCommand("EHLO " . ($this->host ?: "localhost"), 250);

        // Authenticate
        $this->sendCommand("AUTH LOGIN", 334);
        $this->sendCommand(base64_encode($this->username), 334);
        $this->sendCommand(base64_encode($this->password), 235);

        // Sender & Recipient
        $this->sendCommand("MAIL FROM: <{$this->username}>", 250);
        $this->sendCommand("RCPT TO: <{$to}>", 250);

        // DATA
        $this->sendCommand("DATA", 354);

        $boundary = "==Multipart_Boundary_x" . md5(microtime()) . "x";

        $headers = [];
        $headers[] = "Date: " . date('r');
        $headers[] = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$this->username}>";
        $headers[] = "To: <{$to}>";
        if ($replyTo) {
            $headers[] = "Reply-To: <{$replyTo}>";
        }
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "Message-ID: <" . time() . "." . uniqid() . "@" . $this->host . ">";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
        $headers[] = "X-Mailer: PawsAndHooves-SMTP/1.0";

        // Plain text version
        $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody));
        $plainText = preg_replace("/\n\s+\n/", "\n\n", $plainText);

        $bodyData = implode("\r\n", $headers) . "\r\n\r\n";
        $bodyData .= "--{$boundary}\r\n";
        $bodyData .= "Content-Type: text/plain; charset=utf-8\r\n";
        $bodyData .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $bodyData .= chunk_split(base64_encode($plainText)) . "\r\n";
        $bodyData .= "--{$boundary}\r\n";
        $bodyData .= "Content-Type: text/html; charset=utf-8\r\n";
        $bodyData .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $bodyData .= chunk_split(base64_encode($htmlBody)) . "\r\n";
        $bodyData .= "--{$boundary}--\r\n";
        $bodyData .= ".";

        $this->sendCommand($bodyData, 250);
        $this->sendCommand("QUIT", 221);
        fclose($this->socket);

        return true;
    }
}

/**
 * Universal Mailer helper that tries authenticated SMTP first,
 * and falls back gracefully to standard PHP mail() with -f envelope.
 */
function sendPawsEmail($to, $subject, $htmlBody, $fromName = "Paws & Hooves Mobile Vet", $replyTo = null) {
    $smtpHost = 'mail.pawhooves.co.ke';
    $smtpPort = 465;
    $smtpUser = 'dr.vet@pawhooves.co.ke';
    $smtpPass = 'Dr.Vet 0759396531'; // Email password

    $smtp = new SimpleSMTP($smtpHost, $smtpPort, $smtpUser, $smtpPass);
    try {
        $result = $smtp->send($to, $subject, $htmlBody, $fromName, $replyTo);
        if ($result) {
            return ['success' => true, 'driver' => 'smtp_ssl_465'];
        }
    } catch (\Exception $e) {
        $smtpError = $e->getMessage();
        // Fallback to PHP mail() with envelope sender
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $fromName . ' <' . $smtpUser . '>',
            'Reply-To: ' . ($replyTo ?: $smtpUser),
            'Return-Path: ' . $smtpUser,
            'X-Mailer: PHP/' . phpversion()
        ];
        $mailSent = @mail($to, $subject, $htmlBody, implode("\r\n", $headers), "-f" . $smtpUser);
        return [
            'success' => $mailSent,
            'driver' => $mailSent ? 'php_mail_fallback' : 'failed',
            'smtp_error' => $smtpError,
            'smtp_logs' => $smtp->getLogs()
        ];
    }
    return ['success' => false];
}
