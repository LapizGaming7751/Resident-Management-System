<?php
// Email configuration for password reset functionality using PHPMailer

// Include secure configuration
require_once 'config.php';

// Include PHPMailer via Composer autoloader
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendPasswordResetEmail($email, $token, $user_type = 'resident') {
    $reset_link = APP_URL . "/reset_password_confirm.php?token=" . urlencode($token);
    $user_type_display = ucfirst($user_type);
    
    $subject = "Password Reset Request - Check-In System";
    $message = "
    <html>
    <head>
        <title>Password Reset Request</title>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Password Reset Request</h1>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>We received a request to reset the password for your $user_type_display account in the Check-In System.</p>
                <p>To reset your password, please click the button below:</p>
                <p style='text-align: center;'>
                    <a href='$reset_link' class='button'>Reset My Password</a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background-color: #e9ecef; padding: 10px; border-radius: 3px;'>$reset_link</p>
                
                <div class='warning'>
                    <strong>Important:</strong>
                    <ul>
                        <li>This link will expire in 1 hour</li>
                        <li>This link can only be used once</li>
                        <li>If you didn't request this password reset, please ignore this email</li>
                    </ul>
                </div>
                
                <p>If you have any questions or need assistance, please contact the system administrator.</p>
            </div>
            <div class='footer'>
                <p>This is an automated message from the Check-In System. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Enable verbose debug output (remove in production)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        // Send the email
        $mail->send();
        
        // Log successful email
        error_log("Password reset email sent successfully to: $email");
        return true;
        
    } catch (Exception $e) {
        // Log the error
        error_log("Password reset email failed to send to $email. Error: {$mail->ErrorInfo}");
        
        // For development: also log to a file
        $log_entry = "[" . date('Y-m-d H:i:s') . "] Email failed to $email: {$mail->ErrorInfo}\n";
        file_put_contents('email_errors.log', $log_entry, FILE_APPEND | LOCK_EX);
        
        return false;
    }
}

function sendInviteEmail($email, $invite_code, $user_type = 'resident', $room_code = null, $expires_at = null) {
    $register_link = APP_URL . "/register.php?invite_code=" . urlencode($invite_code) . "&email=" . urlencode($email);
    $user_type_display = ucfirst($user_type);
    $expires_display = $expires_at ? date('F j, Y \a\t g:i A', strtotime($expires_at)) : '24 hours';
    
    $subject = "Invitation to Join Check-In System - $user_type_display Account";
    $message = "
    <html>
    <head>
        <title>Account Invitation</title>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            .info-box { background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .code-box { background-color: #e9ecef; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 16px; font-weight: bold; text-align: center; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Account Invitation</h1>
                <p>You've been invited to join the Check-In System</p>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>You have been invited to create a <strong>$user_type_display</strong> account in the Check-In System.</p>
                
                <div class='info-box'>
                    <strong>Your Invitation Details:</strong>
                    <ul>
                        <li><strong>Account Type:</strong> $user_type_display</li>";
    
    if ($room_code) {
        $message .= "<li><strong>Room Code:</strong> $room_code</li>";
    }
    
    $message .= "
                        <li><strong>Expires:</strong> $expires_display</li>
                    </ul>
                </div>
                
                <p>To create your account, please click the button below:</p>
                <p style='text-align: center;'>
                    <a href='$register_link' class='button'>Create My Account</a>
                </p>
                
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background-color: #e9ecef; padding: 10px; border-radius: 3px;'>$register_link</p>
                
                <div class='code-box'>
                    Your Invite Code: $invite_code
                </div>
                
                <p><strong>Instructions:</strong></p>
                <ol>
                    <li>Click the \"Create My Account\" button above</li>
                    <li>Fill in your desired username and password</li>
                    <li>Your invite code and email will be pre-filled</li>
                    <li>Complete the registration process</li>
                </ol>
                
                <p>If you have any questions or need assistance, please contact the system administrator.</p>
            </div>
            <div class='footer'>
                <p>This is an automated message from the Check-In System. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Enable verbose debug output (remove in production)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        // Send the email
        $mail->send();
        
        // Log successful email
        error_log("Invite email sent successfully to: $email for $user_type account");
        return true;
        
    } catch (Exception $e) {
        // Log the error
        error_log("Invite email failed to send to $email. Error: {$mail->ErrorInfo}");
        
        // For development: also log to a file
        $log_entry = "[" . date('Y-m-d H:i:s') . "] Invite email failed to $email: {$mail->ErrorInfo}\n";
        file_put_contents('email_errors.log', $log_entry, FILE_APPEND | LOCK_EX);
        
        return false;
    }
}

function sendQREmail($email, $token, $visitor_name, $plate_id, $expiry_date, $resident_name, $room_code) {
    $qr_image_url = APP_URL . "/qr/" . $token . ".png";
    $expires_display = date('F j, Y \a\t g:i A', strtotime($expiry_date));
    
    $subject = "Your QR Code for Visiting - Check-In System";
    $message = "
    <html>
    <head>
        <title>QR Code for Visit</title>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .qr-section { text-align: center; margin: 20px 0; padding: 20px; background: white; border-radius: 8px; border: 2px solid #e9ecef; }
            .qr-code { max-width: 200px; height: auto; border: 1px solid #ddd; border-radius: 8px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            .info-box { background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .warning-box { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .details-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            .details-table td { padding: 8px; border-bottom: 1px solid #e9ecef; }
            .details-table td:first-child { font-weight: bold; width: 30%; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Your QR Code is Ready!</h1>
                <p>Visitor Pass for Check-In System</p>
            </div>
            <div class='content'>
                <p>Hello <strong>$visitor_name</strong>,</p>
                <p>Your QR code for visiting has been created and is ready to use. Please find the details below:</p>
                
                <div class='qr-section'>
                    <h3>Your QR Code</h3>
                    <img src='$qr_image_url' alt='QR Code' class='qr-code'>
                    <p><strong>Token:</strong> $token</p>
                </div>
                
                <table class='details-table'>
                    <tr>
                        <td>Visitor Name:</td>
                        <td>$visitor_name</td>
                    </tr>
                    <tr>
                        <td>Car Plate:</td>
                        <td>" . ($plate_id ?: 'Not provided') . "</td>
                    </tr>
                    <tr>
                        <td>Valid Until:</td>
                        <td>$expires_display</td>
                    </tr>
                    <tr>
                        <td>Hosted By:</td>
                        <td>$resident_name (Room: $room_code)</td>
                    </tr>
                </table>
                
                <div class='info-box'>
                    <strong>How to Use Your QR Code:</strong>
                    <ol>
                        <li>Save this QR code image to your phone or print it</li>
                        <li>Present the QR code to security when arriving</li>
                        <li>Security will scan the code to log your entry</li>
                        <li>Present the same code when leaving to log your exit</li>
                    </ol>
                </div>
                
                <div class='warning-box'>
                    <strong>Important Notes:</strong>
                    <ul>
                        <li>This QR code is valid only until <strong>$expires_display</strong></li>
                        <li>Each QR code can only be used once for entry and once for exit</li>
                        <li>Keep this QR code secure and do not share it with others</li>
                        <li>If you lose this QR code, contact your host for a new one</li>
                    </ul>
                </div>
                
                <p>If you have any questions or need assistance, please contact your host or the building security.</p>
            </div>
            <div class='footer'>
                <p>This is an automated message from the Check-In System. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Enable verbose debug output (remove in production)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        // Send the email
        $mail->send();
        
        // Log successful email
        error_log("QR code email sent successfully to: $email for visitor: $visitor_name");
        return true;
        
    } catch (Exception $e) {
        // Log the error
        error_log("QR code email failed to send to $email. Error: {$mail->ErrorInfo}");
        
        // For development: also log to a file
        $log_entry = "[" . date('Y-m-d H:i:s') . "] QR email failed to $email: {$mail->ErrorInfo}\n";
        file_put_contents('email_errors.log', $log_entry, FILE_APPEND | LOCK_EX);
        
        return false;
    }
}

?>