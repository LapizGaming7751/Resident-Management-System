<?php
// Email configuration and functions for sending emails via SMTP
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send QR code invite email to resident
 * @param string $email Recipient email
 * @param string $invite_code Invite code
 * @param string $user_type Type of user ('resident' or 'security')
 * @param string $room_code Room code (optional, for residents)
 * @param string $expires_at Expiry time
 * @return bool True if email sent successfully
 */
function sendInviteEmail($email, $invite_code, $user_type, $room_code = null, $expires_at = null) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = ucfirst($user_type) . ' Registration Invite - Check-In System';
        
        // Build email body
        if ($user_type === 'resident') {
            $registration_url = APP_URL . '/register.php';
            $mail->Body = "
                <h2>Welcome to the Check-In System</h2>
                <p>You have been invited to register as a resident in the Check-In System.</p>
                <p><strong>Your Invite Code:</strong> <code style='background: #f0f0f0; padding: 5px 10px; border-radius: 3px; font-family: monospace;'>$invite_code</code></p>
                <p><strong>Room Code:</strong> $room_code</p>
                <p>Please use this invite code to register: <a href='$registration_url'>Click here to register</a></p>
                <p>This invite code will expire at: <strong>$expires_at</strong></p>
                <p>If you did not request this invitation, please disregard this email.</p>
            ";
        } else {
            $registration_url = APP_URL . '/register.php';
            $mail->Body = "
                <h2>Welcome to the Check-In System</h2>
                <p>You have been invited to register as a security personnel in the Check-In System.</p>
                <p><strong>Your Invite Code:</strong> <code style='background: #f0f0f0; padding: 5px 10px; border-radius: 3px; font-family: monospace;'>$invite_code</code></p>
                <p>Please use this invite code to register: <a href='$registration_url'>Click here to register</a></p>
                <p>This invite code will expire at: <strong>$expires_at</strong></p>
                <p>If you did not request this invitation, please disregard this email.</p>
            ";
        }
        
        $mail->AltBody = "Your invite code is: $invite_code";
        
        $mail->send();
        error_log("Invite email sent successfully to: $email");
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send invite email to $email: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send password reset email
 * @param string $email Recipient email
 * @param string $token Reset token
 * @param string $user_type Type of user ('resident', 'security', or 'admin')
 * @return bool True if email sent successfully
 */
function sendPasswordResetEmail($email, $token, $user_type = 'resident') {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - Check-In System';
        
        $reset_url = APP_URL . '/reset_password.php?token=' . $token;
        
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>You have requested to reset your password for the Check-In System.</p>
            <p>Click the link below to reset your password:</p>
            <p><a href='$reset_url' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
            <p>Or copy and paste this link in your browser:</p>
            <p>$reset_url</p>
            <p><strong>This link will expire in 1 hour.</strong></p>
            <p>If you did not request a password reset, please disregard this email.</p>
        ";
        
        $mail->AltBody = "Click this link to reset your password: $reset_url";
        
        $mail->send();
        error_log("Password reset email sent successfully to: $email");
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send password reset email to $email: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send QR code email to resident with attachment
 * @param string $email Recipient email
 * @param string $token QR token
 * @param string $visitor_name Visitor name
 * @param string $plate_id Visitor car plate
 * @param string $expiry Expiry time
 * @param string $resident_name Name of resident who created the QR
 * @param string $room_code Room code
 * @return bool True if email sent successfully
 */
function sendQREmail($email, $token, $visitor_name, $plate_id, $expiry, $resident_name, $room_code) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Visitor Check-In QR Code - ' . $visitor_name;
        
        $qr_path = __DIR__ . '/qr/' . $token . '.png';
        
        // Build email body
        $mail->Body = "
            <h2>Visitor Check-In QR Code</h2>
            <p>Hello,</p>
            <p>Your visitor check-in QR code is attached below.</p>
            <h3>Visit Details:</h3>
            <ul>
                <li><strong>Visitor Name:</strong> $visitor_name</li>
                <li><strong>Car Plate:</strong> $plate_id</li>
                <li><strong>Resident:</strong> $resident_name (Room: $room_code)</li>
                <li><strong>QR Code Valid Until:</strong> $expiry</li>
            </ul>
            <h3>QR Code:</h3>
            <p><img src='cid:qrcode' alt='QR Code' style='max-width: 300px; border: 1px solid #ccc; padding: 10px;'></p>
            <p>Please present this QR code when entering the building.</p>
            <p>If you have any questions, please contact the resident: $resident_name</p>
        ";
        
        $mail->AltBody = "Visitor Check-In QR Code for $visitor_name. Valid until: $expiry";
        
        // Attach QR code image if it exists
        if (file_exists($qr_path)) {
            $mail->addEmbeddedImage($qr_path, 'qrcode', 'qrcode.png');
        }
        
        $mail->send();
        error_log("QR code email sent successfully to: $email for visitor: $visitor_name");
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send QR code email to $email: " . $mail->ErrorInfo);
        return false;
    }
}
?>
