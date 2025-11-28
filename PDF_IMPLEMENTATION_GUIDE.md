# PDF Letter Generation Implementation Guide

## Overview
This guide explains how to implement PDF generation for booking request letters and visa invitation letters for the CirclePoint Homes booking system.

## Option 1: Using TCPDF (Recommended)

### Installation via Composer
```bash
composer require tecnickcom/tcpdf
```

### Or Manual Installation
1. Download TCPDF from: https://github.com/tecnickcom/TCPDF
2. Extract to `vendor/tcpdf/`
3. Include in your project

## Option 2: Using mPDF (Alternative)

### Installation via Composer
```bash
composer require mpdf/mpdf
```

## Implementation Steps

### Step 1: Create PDF Helper File

Create `includes/pdf.php`:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';  // If using Composer
// OR
require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';  // If manually installed

use TCPDF;

class PDFGenerator {

    public static function generateBookingLetter($booking, $property) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('CirclePoint Homes');
        $pdf->SetAuthor('CirclePoint Homes');
        $pdf->SetTitle('Booking Confirmation Letter');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Add a page
        $pdf->AddPage();

        // Get HTML content
        $html = self::getBookingLetterHTML($booking, $property);

        // Output HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Generate filename
        $filename = 'booking_letter_' . $booking['id'] . '.pdf';
        $filepath = ROOT_PATH . '/uploads/letters/' . $filename;

        // Create directory if it doesn't exist
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        // Save PDF
        $pdf->Output($filepath, 'F');

        return '/uploads/letters/' . $filename;
    }

    public static function generateVisaInvitationLetter($booking, $property) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('CirclePoint Homes');
        $pdf->SetAuthor('CirclePoint Homes');
        $pdf->SetTitle('Visa Invitation Letter');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Add a page
        $pdf->AddPage();

        // Get HTML content
        $html = self::getVisaInvitationLetterHTML($booking, $property);

        // Output HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Generate filename
        $filename = 'visa_invitation_' . $booking['id'] . '.pdf';
        $filepath = ROOT_PATH . '/uploads/letters/' . $filename;

        // Create directory if it doesn't exist
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        // Save PDF
        $pdf->Output($filepath, 'F');

        return '/uploads/letters/' . $filename;
    }

    private static function getBookingLetterHTML($booking, $property) {
        $html = '
        <style>
            body { font-family: helvetica; }
            h1 { color: #2563eb; font-size: 24px; }
            h2 { color: #1e40af; font-size: 18px; margin-top: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2563eb; padding-bottom: 20px; }
            .section { margin-bottom: 20px; }
            .label { font-weight: bold; color: #374151; }
            .value { color: #000000; }
            .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #6b7280; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 8px; }
            .signature { margin-top: 40px; }
        </style>

        <div class="header">
            <h1>CirclePoint Homes</h1>
            <p>Booking Confirmation Letter</p>
            <p style="font-size: 12px;">Date: ' . date('F d, Y') . '</p>
        </div>

        <div class="section">
            <h2>Guest Information</h2>
            <table>
                <tr>
                    <td class="label" width="40%">Full Name:</td>
                    <td class="value">' . htmlspecialchars($booking['guest_full_name']) . '</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td class="value">' . htmlspecialchars($booking['guest_email']) . '</td>
                </tr>
                <tr>
                    <td class="label">Phone:</td>
                    <td class="value">' . htmlspecialchars($booking['phone']) . '</td>
                </tr>
                <tr>
                    <td class="label">Nationality:</td>
                    <td class="value">' . htmlspecialchars($booking['guest_nationality']) . '</td>
                </tr>
                <tr>
                    <td class="label">Passport/ID Number:</td>
                    <td class="value">' . htmlspecialchars($booking['guest_passport_number']) . '</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Property Details</h2>
            <table>
                <tr>
                    <td class="label" width="40%">Property Name:</td>
                    <td class="value">' . htmlspecialchars($property['title']) . '</td>
                </tr>
                <tr>
                    <td class="label">Address:</td>
                    <td class="value">' . htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['country']) . '</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Booking Details</h2>
            <table>
                <tr>
                    <td class="label" width="40%">Booking ID:</td>
                    <td class="value">' . htmlspecialchars($booking['id']) . '</td>
                </tr>
                <tr>
                    <td class="label">Check-in Date:</td>
                    <td class="value">' . date('F d, Y', strtotime($booking['check_in'])) . '</td>
                </tr>
                <tr>
                    <td class="label">Check-out Date:</td>
                    <td class="value">' . date('F d, Y', strtotime($booking['check_out'])) . '</td>
                </tr>
                <tr>
                    <td class="label">Number of Guests:</td>
                    <td class="value">' . $booking['guests'] . '</td>
                </tr>
                <tr>
                    <td class="label">Total Price:</td>
                    <td class="value">$' . number_format($booking['total_price'], 2) . '</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Purpose of Visit</h2>
            <p>' . nl2br(htmlspecialchars($booking['purpose_of_visit'])) . '</p>
        </div>

        <div class="signature">
            <p><strong>Guest Signature:</strong></p>
            <p style="font-family: cursive; font-size: 18px;">' . htmlspecialchars($booking['signature_data']) . '</p>
            <p style="font-size: 12px; color: #6b7280;">Date: ' . date('F d, Y', strtotime($booking['signature_date'])) . '</p>
        </div>

        <div class="footer">
            <p style="text-align: center;">
                This is an official booking confirmation letter from CirclePoint Homes.<br>
                For inquiries, please contact us via WhatsApp: ' . WHATSAPP_NUMBER . '<br>
                Email: ' . MAIL_FROM_ADDRESS . '
            </p>
        </div>
        ';

        return $html;
    }

    private static function getVisaInvitationLetterHTML($booking, $property) {
        $html = '
        <style>
            body { font-family: helvetica; }
            h1 { color: #2563eb; font-size: 24px; }
            h2 { color: #1e40af; font-size: 18px; margin-top: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2563eb; padding-bottom: 20px; }
            .letter-body { line-height: 1.8; text-align: justify; }
            .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
            .signature-block { margin-top: 40px; }
        </style>

        <div class="header">
            <h1>CirclePoint Homes</h1>
            <p>' . htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['country']) . '</p>
            <p>Phone: ' . WHATSAPP_NUMBER . ' | Email: ' . MAIL_FROM_ADDRESS . '</p>
            <p style="margin-top: 20px;"><strong>Date: ' . date('F d, Y') . '</strong></p>
        </div>

        <div style="margin-bottom: 30px;">
            <p><strong>To Whom It May Concern</strong></p>
            <p>Visa Section<br>Embassy/Consulate</p>
        </div>

        <div style="margin-bottom: 20px;">
            <p><strong>Subject: Invitation Letter for Accommodation</strong></p>
        </div>

        <div class="letter-body">
            <p>Dear Sir/Madam,</p>

            <p>
                We are pleased to confirm that <strong>' . htmlspecialchars($booking['guest_full_name']) . '</strong>,
                holder of passport number <strong>' . htmlspecialchars($booking['guest_passport_number']) . '</strong>,
                nationality <strong>' . htmlspecialchars($booking['guest_nationality']) . '</strong>,
                born on <strong>' . date('F d, Y', strtotime($booking['guest_date_of_birth'])) . '</strong>,
                has made a confirmed reservation to stay at our property.
            </p>

            <p><strong>Accommodation Details:</strong></p>
            <p>
                Property Name: <strong>' . htmlspecialchars($property['title']) . '</strong><br>
                Property Address: <strong>' . htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['country']) . '</strong><br>
                Check-in Date: <strong>' . date('F d, Y', strtotime($booking['check_in'])) . '</strong><br>
                Check-out Date: <strong>' . date('F d, Y', strtotime($booking['check_out'])) . '</strong><br>
                Duration of Stay: <strong>' . ceil((strtotime($booking['check_out']) - strtotime($booking['check_in'])) / (60 * 60 * 24)) . ' days</strong>
            </p>

            <p><strong>Purpose of Visit:</strong></p>
            <p>' . nl2br(htmlspecialchars($booking['purpose_of_visit'])) . '</p>

            <p>
                The guest will be staying at our fully furnished property during their visit.
                All accommodation arrangements have been confirmed and paid for.
                We guarantee that adequate accommodation will be provided for the duration of their stay.
            </p>

            <p>
                Should you require any additional information or verification, please do not hesitate to contact us
                at the phone number or email address provided above.
            </p>

            <p>Thank you for your consideration.</p>
        </div>

        <div class="signature-block">
            <p><strong>Yours faithfully,</strong></p>
            <br><br>
            <p>___________________________</p>
            <p><strong>CirclePoint Homes Management</strong><br>
            Property Manager<br>
            Date: ' . date('F d, Y') . '</p>
        </div>

        <div class="footer" style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 40px; font-size: 10px; color: #6b7280; text-align: center;">
            <p>
                This is an official invitation letter from CirclePoint Homes.<br>
                Booking Reference: ' . htmlspecialchars($booking['id']) . '<br>
                For verification, please contact: ' . WHATSAPP_NUMBER . ' | ' . MAIL_FROM_ADDRESS . '
            </p>
        </div>
        ';

        return $html;
    }
}
```

### Step 2: Update booking.php to Generate PDFs

In `api/booking.php`, replace the TODO comment with:

```php
// Generate PDF letters
require_once '../includes/pdf.php';

// Always generate booking letter
$bookingLetterPath = PDFGenerator::generateBookingLetter([
    'id' => $bookingId,
    'guest_full_name' => $guestFullName,
    'guest_email' => $guestEmail,
    'phone' => $phone,
    'guest_nationality' => $guestNationality,
    'guest_passport_number' => $guestPassportNumber,
    'check_in' => $checkIn,
    'check_out' => $checkOut,
    'guests' => $guests,
    'total_price' => $totalPrice,
    'purpose_of_visit' => $purposeOfVisit,
    'signature_data' => $signatureData,
    'signature_date' => date('Y-m-d H:i:s')
], $property);

// Update booking with PDF path
db()->query(
    'UPDATE bookings SET booking_letter_path = ? WHERE id = ?',
    [$bookingLetterPath, $bookingId]
);

// Generate visa letter if required
if ($requiresVisaLetter) {
    $visaLetterPath = PDFGenerator::generateVisaInvitationLetter([
        'id' => $bookingId,
        'guest_full_name' => $guestFullName,
        'guest_nationality' => $guestNationality,
        'guest_passport_number' => $guestPassportNumber,
        'guest_date_of_birth' => $guestDateOfBirth,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'purpose_of_visit' => $purposeOfVisit
    ], $property);

    // Update booking with visa letter path
    db()->query(
        'UPDATE bookings SET visa_letter_path = ? WHERE id = ?',
        [$visaLetterPath, $bookingId]
    );
}
```

### Step 3: Create Download Links

In the admin booking detail page, add download links:

```php
<?php if ($booking['booking_letter_path']): ?>
    <a href="<?php echo $booking['booking_letter_path']; ?>"
       class="btn btn-primary" download>
        <i class="fas fa-download"></i> Download Booking Letter
    </a>
<?php endif; ?>

<?php if ($booking['visa_letter_path']): ?>
    <a href="<?php echo $booking['visa_letter_path']; ?>"
       class="btn btn-success" download>
        <i class="fas fa-download"></i> Download Visa Invitation Letter
    </a>
<?php endif; ?>
```

## Alternative: Simple HTML Print-to-PDF

If you can't install PDF libraries, create viewable/printable HTML pages:

1. Create `public/booking-letter.php?id=[booking_id]`
2. Create `public/visa-letter.php?id=[booking_id]`
3. Add "Print" button that opens print dialog
4. Users can save as PDF from browser print dialog

This is less elegant but works without dependencies.

## Testing

1. Make a test booking with "foreigner" and "visa letter" checkboxes checked
2. Check that PDFs are generated in `/uploads/letters/`
3. Download and verify PDF content
4. Test email delivery if implemented

## Troubleshooting

**Issue**: "Class TCPDF not found"
- **Solution**: Verify autoload.php path or TCPDF include path

**Issue**: Permission denied when saving PDF
- **Solution**: Ensure `/uploads/letters/` directory exists and is writable (chmod 755)

**Issue**: Blank PDF generated
- **Solution**: Check PHP error logs, verify HTML is valid

## Future Enhancements

1. Email PDFs automatically to guest2
2. Add company logo to letterhead
3. Digital signature verification
4. Multiple language support
5. Customizable letter templates via admin panel
