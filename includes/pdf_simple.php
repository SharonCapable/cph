<?php
/**
 * Simple PDF Generation (HTML Printable)
 * These generate HTML pages that can be printed to PDF by the browser
 * or served directly for viewing
 */

/**
 * Generate Booking Request Form HTML
 */
function generateBookingRequestHTML($booking, $property) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Booking Request Form - ' . htmlspecialchars($booking['guest_full_name']) . '</title>
        <style>
            @media print {
                body { margin: 0; padding: 20mm; }
                .no-print { display: none; }
            }
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 40px; }
            .header { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
            .header h1 { color: #2563eb; margin: 0 0 10px 0; }
            .section { margin-bottom: 30px; }
            .section-title { background: #2563eb; color: white; padding: 10px 15px; margin-bottom: 15px; font-weight: bold; }
            .field { margin-bottom: 15px; display: flex; }
            .field-label { font-weight: bold; min-width: 200px; }
            .field-value { flex: 1; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
            .declaration { background: #f3f4f6; padding: 20px; margin: 30px 0; border-left: 4px solid #2563eb; }
            .signature-section { margin-top: 40px; }
            .signature-line { border-bottom: 2px solid #000; width: 300px; margin-top: 50px; }
            .print-button { background: #2563eb; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin: 20px 0; }
            .print-button:hover { background: #1d4ed8; }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button onclick="window.print()" class="print-button">🖨️ Print / Save as PDF</button>
        </div>

        <div class="header">
            <h1>CirclePoint Homes</h1>
            <h2>Apartment Booking Request Form</h2>
            <p>(for Traveling Guest)</p>
            <p><strong>Booking ID:</strong> ' . htmlspecialchars($booking['id']) . '</p>
            <p><strong>Date:</strong> ' . date('F d, Y') . '</p>
        </div>

        <div class="section">
            <div class="section-title">Guest Information</div>
            <div class="field">
                <div class="field-label">Full Name:</div>
                <div class="field-value">' . htmlspecialchars($booking['guest_full_name']) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Date of Birth:</div>
                <div class="field-value">' . date('F d, Y', strtotime($booking['guest_date_of_birth'])) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Nationality:</div>
                <div class="field-value">' . htmlspecialchars($booking['guest_nationality']) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Passport Number:</div>
                <div class="field-value">' . htmlspecialchars($booking['guest_passport_number']) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Email Address:</div>
                <div class="field-value">' . htmlspecialchars($booking['guest_email']) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Phone Number:</div>
                <div class="field-value">' . htmlspecialchars($booking['phone']) . '</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Property Details</div>
            <div class="field">
                <div class="field-label">Property Name:</div>
                <div class="field-value">' . htmlspecialchars($property['title']) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Address:</div>
                <div class="field-value">' . htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['country']) . '</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Travel Details</div>
            <div class="field">
                <div class="field-label">Purpose of Visit:</div>
                <div class="field-value">' . htmlspecialchars($booking['purpose_of_visit']) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Check-in Date:</div>
                <div class="field-value">' . date('F d, Y', strtotime($booking['check_in'])) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Check-out Date:</div>
                <div class="field-value">' . date('F d, Y', strtotime($booking['check_out'])) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Arrival Date:</div>
                <div class="field-value">' . date('F d, Y', strtotime($booking['arrival_date'])) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Departure Date:</div>
                <div class="field-value">' . date('F d, Y', strtotime($booking['departure_date'])) . '</div>
            </div>' .
            ($booking['arrival_flight'] ? '<div class="field"><div class="field-label">Flight Number:</div><div class="field-value">' . htmlspecialchars($booking['arrival_flight']) . '</div></div>' : '') . '
        </div>

        <div class="section">
            <div class="section-title">Emergency Contact</div>
            <div class="field">
                <div class="field-label">Name:</div>
                <div class="field-value">' . htmlspecialchars($booking['emergency_contact_name']) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Phone Number:</div>
                <div class="field-value">' . htmlspecialchars($booking['emergency_contact_phone']) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Relationship to Guest:</div>
                <div class="field-value">' . htmlspecialchars($booking['emergency_contact_relationship']) . '</div>
            </div>
        </div>

        <div class="declaration">
            <h3>Declaration</h3>
            <p>I confirm that the information provided above is correct and I understand that this booking request is subject to confirmation by the apartment management.</p>
        </div>

        <div class="signature-section">
            <div class="field">
                <div class="field-label">Signature:</div>
                <div class="field-value" style="font-family: cursive; font-size: 20px;">' . htmlspecialchars($booking['signature_data']) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Date:</div>
                <div class="field-value">' . date('F d, Y', strtotime($booking['signature_date'])) . '</div>
            </div>
        </div>

        <div class="no-print" style="margin-top: 40px; text-align: center;">
            <button onclick="window.print()" class="print-button">🖨️ Print / Save as PDF</button>
            <p style="color: #666; font-size: 14px;">Use your browser\'s Print function and select "Save as PDF" as the destination</p>
        </div>
    </body>
    </html>';

    return $html;
}

/**
 * Generate Visa Invitation Letter HTML
 */
function generateVisaInvitationHTML($booking, $property) {
    $nights = ceil((strtotime($booking['check_out']) - strtotime($booking['check_in'])) / (60 * 60 * 24));

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Visa Invitation Letter - ' . htmlspecialchars($booking['guest_full_name']) . '</title>
        <style>
            @media print {
                body { margin: 0; padding: 20mm; }
                .no-print { display: none; }
            }
            body { font-family: "Times New Roman", serif; line-height: 1.8; color: #000; max-width: 800px; margin: 0 auto; padding: 40px; font-size: 14pt; }
            .letterhead { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
            .letterhead h1 { color: #2563eb; margin: 0 0 10px 0; font-size: 24pt; }
            .date { text-align: right; margin: 30px 0; font-weight: bold; }
            .recipient { margin: 30px 0; }
            .subject { margin: 30px 0; font-weight: bold; text-decoration: underline; }
            .body-text { text-align: justify; margin-bottom: 20px; }
            .signature { margin-top: 60px; }
            .signature-line { border-top: 2px solid #000; width: 300px; margin-top: 60px; padding-top: 10px; }
            .print-button { background: #2563eb; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin: 20px 0; }
            .print-button:hover { background: #1d4ed8; }
            table { width: 100%; margin: 20px 0; }
            td { padding: 5px 0; }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button onclick="window.print()" class="print-button">🖨️ Print / Save as PDF</button>
        </div>

        <div class="letterhead">
            <h1>CirclePoint Homes</h1>
            <p>' . htmlspecialchars($property['address']) . '<br>
            ' . htmlspecialchars($property['city'] . ', ' . $property['country']) . '<br>
            Email: ' . MAIL_FROM_ADDRESS . '<br>
            Phone: ' . WHATSAPP_NUMBER . '</p>
        </div>

        <div class="date">
            Date: ' . date('F d, Y') . '
        </div>

        <div class="recipient">
            <strong>To:</strong><br>
            The Consulate General of [Country]<br>
            [Embassy/Consulate Address]
        </div>

        <div class="subject">
            Subject: Invitation Letter for ' . htmlspecialchars($booking['guest_full_name']) . ' – Passport No. ' . htmlspecialchars($booking['guest_passport_number']) . '
        </div>

        <p class="body-text">Dear Sir/Madam,</p>

        <p class="body-text">
            I, <strong>Francis Curtis</strong>, on behalf of CirclePoint Homes, residing at <strong>' . htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['country']) . '</strong>, am writing to invite <strong>' . htmlspecialchars($booking['guest_full_name']) . '</strong>, holder of passport number <strong>' . htmlspecialchars($booking['guest_passport_number']) . '</strong>, to visit us in ' . htmlspecialchars($property['country']) . ' from <strong>' . date('F d, Y', strtotime($booking['check_in'])) . '</strong> to <strong>' . date('F d, Y', strtotime($booking['check_out'])) . '</strong>.
        </p>

        <p class="body-text">
            During this stay, the guest will be accommodated at <strong>' . htmlspecialchars($property['title']) . '</strong>, which has been booked for the duration of the visit. I will ensure that all accommodation arrangements are in place and that ' . htmlspecialchars($booking['guest_full_name']) . ' abides by the rules of the host country and returns home upon completion of the visit.
        </p>

        <p class="body-text">
            <strong>Accommodation Details:</strong>
        </p>
        <table>
            <tr>
                <td width="40%"><strong>Property Name:</strong></td>
                <td>' . htmlspecialchars($property['title']) . '</td>
            </tr>
            <tr>
                <td><strong>Property Address:</strong></td>
                <td>' . htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['country']) . '</td>
            </tr>
            <tr>
                <td><strong>Check-in Date:</strong></td>
                <td>' . date('F d, Y', strtotime($booking['check_in'])) . '</td>
            </tr>
            <tr>
                <td><strong>Check-out Date:</strong></td>
                <td>' . date('F d, Y', strtotime($booking['check_out'])) . '</td>
            </tr>
            <tr>
                <td><strong>Duration of Stay:</strong></td>
                <td>' . $nights . ' days</td>
            </tr>
        </table>

        <p class="body-text">
            The purpose of the visit is <strong>' . htmlspecialchars($booking['purpose_of_visit']) . '</strong>.
        </p>

        <p class="body-text">
            Should you require any further information or verification, please feel free to contact me by phone or email at the details provided above.
        </p>

        <p class="body-text">Yours faithfully,</p>

        <div class="signature">
            <div class="signature-line">
                <strong>Francis Curtis</strong><br>
                CirclePoint Homes Management<br>
                Property Manager<br>
                Date: ' . date('F d, Y') . '
            </div>
        </div>

        <div style="margin-top: 60px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 10pt; color: #666; text-align: center;">
            <p>This is an official invitation letter from CirclePoint Homes.<br>
            Booking Reference: ' . htmlspecialchars($booking['id']) . '<br>
            For verification, please contact: ' . WHATSAPP_NUMBER . ' | ' . MAIL_FROM_ADDRESS . '</p>
        </div>

        <div class="no-print" style="margin-top: 40px; text-align: center;">
            <button onclick="window.print()" class="print-button">🖨️ Print / Save as PDF</button>
            <p style="color: #666; font-size: 14px;">Use your browser\'s Print function and select "Save as PDF" as the destination</p>
        </div>
    </body>
    </html>';

    return $html;
}

/**
 * Save HTML to file for later viewing/download
 */
function saveHTMLLetter($html, $bookingId, $type) {
    $directory = ROOT_PATH . '/uploads/letters';

    // Create directory if it doesn't exist
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $filename = $type . '_' . $bookingId . '.html';
    $filepath = $directory . '/' . $filename;

    file_put_contents($filepath, $html);

    return '/uploads/letters/' . $filename;
}
