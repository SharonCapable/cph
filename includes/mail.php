<?php
/**
 * Email Helper Functions
 * Simple email sending with support for plain mail() or SMTP
 */

/**
 * Send an email
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML supported)
 * @param string $replyTo Optional reply-to address
 * @return bool Success status
 */
function sendEmail($to, $subject, $body, $replyTo = null) {
    try {
        // Use SMTP if configured, otherwise fall back to mail()
        if (SMTP_HOST && SMTP_USERNAME && SMTP_PASSWORD) {
            return sendEmailSMTP($to, $subject, $body, $replyTo);
        } else {
            return sendEmailSimple($to, $subject, $body, $replyTo);
        }
    } catch (Exception $e) {
        error_log('Email send failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send email using PHP's mail() function
 * Works on most shared hosting including Hostinger
 */
function sendEmailSimple($to, $subject, $body, $replyTo = null) {
    $headers = [];
    $headers[] = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'MIME-Version: 1.0';

    if ($replyTo) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Send email using SMTP (for production with proper email server)
 * Requires SMTP settings in .env
 */
function sendEmailSMTP($to, $subject, $body, $replyTo = null) {
    // This is a placeholder for SMTP implementation
    // For production, you would use PHPMailer or similar
    // For now, fall back to simple mail
    return sendEmailSimple($to, $subject, $body, $replyTo);
}

/**
 * Send property manager application received email
 * Sent immediately when someone applies
 */
function sendApplicationReceivedEmail($email, $firstName, $tempPassword = null) {
    $subject = 'Property Manager Application Received - ' . APP_NAME;

    $loginUrl = APP_URL . '/public/login.php';

    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2563eb 0%, #9333ea 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
            .credentials-box { background: white; border: 2px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 8px; }
            .button { display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #9333ea 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . APP_NAME . '</h1>
                <p style="font-size: 18px; margin: 10px 0 0 0;">Property Manager Application</p>
            </div>
            <div class="content">
                <h2>Hello ' . htmlspecialchars($firstName) . '!</h2>

                <p>Thank you for applying to become a property manager with ' . APP_NAME . '. We have received your application and will review it shortly.</p>';

    if ($tempPassword) {
        $body .= '
                <div class="credentials-box">
                    <h3 style="color: #10b981; margin-top: 0;">🔑 Your Login Credentials</h3>
                    <p><strong>Save these credentials - you\'ll need them to login once approved!</strong></p>
                    <table style="width: 100%; margin: 15px 0;">
                        <tr>
                            <td style="padding: 8px 0;"><strong>Email:</strong></td>
                            <td style="padding: 8px 0; font-family: monospace;">' . htmlspecialchars($email) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0;"><strong>Password:</strong></td>
                            <td style="padding: 8px 0; font-family: monospace; color: #10b981; font-weight: bold;">' . htmlspecialchars($tempPassword) . '</td>
                        </tr>
                    </table>
                </div>

                <div class="warning">
                    <strong>⚠️ Important:</strong> Save these credentials now! Once approved, you can login and change your password.
                </div>';
    }

    $body .= '
                <h3>What happens next?</h3>
                <ol style="padding-left: 20px;">
                    <li><strong>Review:</strong> Our team will review your application (usually within 24-48 hours)</li>
                    <li><strong>Approval:</strong> You\'ll receive an email once your application is approved</li>
                    <li><strong>Get Started:</strong> Login and start listing your properties!</li>
                </ol>

                <p>If you have any questions, feel free to reply to this email.</p>

                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($email, $subject, $body);
}

/**
 * Send property manager application approved email
 * Sent by super admin when approving an application
 */
function sendApplicationApprovedEmail($email, $firstName) {
    $subject = 'Application Approved! Start Listing Properties - ' . APP_NAME;

    $loginUrl = APP_URL . '/public/login.php';
    $dashboardUrl = APP_URL . '/admin/index.php';

    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
            .success-icon { font-size: 48px; margin: 20px 0; }
            .button { display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #9333ea 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .credentials-reminder { background: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="success-icon">✅</div>
                <h1>Congratulations ' . htmlspecialchars($firstName) . '!</h1>
                <p style="font-size: 18px; margin: 10px 0 0 0;">Your Application Has Been Approved</p>
            </div>
            <div class="content">
                <p>Great news! Your property manager application has been approved. You can now start listing and managing properties on ' . APP_NAME . '.</p>

                <div class="credentials-reminder">
                    <strong>📧 Login Details:</strong>
                    <p style="margin: 10px 0 0 0;">Use the email and password from your application confirmation email to login.</p>
                    <p style="margin: 5px 0 0 0;"><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                </div>

                <div style="text-align: center;">
                    <a href="' . $loginUrl . '" class="button">Login to Your Dashboard</a>
                </div>

                <h3>Getting Started:</h3>
                <ol style="padding-left: 20px;">
                    <li><strong>Login:</strong> Click the button above or visit <a href="' . $loginUrl . '">' . $loginUrl . '</a></li>
                    <li><strong>Add Properties:</strong> Navigate to Properties → Add New Property</li>
                    <li><strong>Upload Photos:</strong> Add high-quality images to attract guests</li>
                    <li><strong>Manage Bookings:</strong> View and respond to booking inquiries</li>
                </ol>

                <h3>Need Help?</h3>
                <p>If you forgot your password or have any questions, reply to this email and we\'ll assist you.</p>

                <p style="margin-top: 30px;">Welcome to the ' . APP_NAME . ' family!</p>

                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($email, $subject, $body);
}

/**
 * Send booking confirmation email
 * Sent when a booking is confirmed by property manager
 */
function sendBookingConfirmationEmail($userEmail, $userName, $propertyName, $checkIn, $checkOut, $totalPrice) {
    $subject = 'Booking Confirmed - ' . $propertyName;

    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2563eb 0%, #9333ea 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
            .booking-details { background: white; border: 2px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 8px; }
            .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Booking Confirmed! ✓</h1>
            </div>
            <div class="content">
                <h2>Hello ' . htmlspecialchars($userName) . '!</h2>

                <p>Great news! Your booking has been confirmed.</p>

                <div class="booking-details">
                    <h3 style="margin-top: 0; color: #10b981;">Booking Details</h3>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 8px 0;"><strong>Property:</strong></td>
                            <td style="padding: 8px 0;">' . htmlspecialchars($propertyName) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0;"><strong>Check-in:</strong></td>
                            <td style="padding: 8px 0;">' . date('F j, Y', strtotime($checkIn)) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0;"><strong>Check-out:</strong></td>
                            <td style="padding: 8px 0;">' . date('F j, Y', strtotime($checkOut)) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0;"><strong>Total:</strong></td>
                            <td style="padding: 8px 0; font-size: 18px; color: #2563eb;"><strong>$' . number_format($totalPrice, 2) . '</strong></td>
                        </tr>
                    </table>
                </div>

                <p>The property manager will contact you soon with additional details and check-in instructions.</p>

                <p>If you have any questions, feel free to reply to this email.</p>

                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($userEmail, $subject, $body);
}
