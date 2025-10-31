<?php
namespace Tests;

use App\Booking\Business;
use App\Domains\Email\Mailables\Booking\BookingConfirmation;
use App\Domains\Email\Mailables\Booking\PdfBookingConfirmation;
use App\Domains\Email\Mailables\Payments\PaymentReceivedReceipt;
use Spatie\MailTemplates\Models\MailTemplate;

trait EmailHelperTrait
{

    protected function setupTestEmails(Business $business) {

        MailTemplate::create([
            'owner_type' => $business->getMorphClass(),
            'owner_id' => $business->id,
            'mailable' => PaymentReceivedReceipt::class,
            'subject' => 'Thank you for your payment, {{ name }}',
            'html_template' => file_get_contents(__DIR__ . '/../database/seeders/PaymentReceipt.html'),
            'text_template' => '<p>Hello, {{ name }}.</p><p>Thanks for your payment of {{ amount }}</p>',
        ]);

        MailTemplate::create([
            'owner_type' => $business->getMorphClass(),
            'owner_id' => $business->id,
            'mailable' => BookingConfirmation::class,
            'subject' => 'Booking Confirmation',
            'html_template' => file_get_contents(__DIR__ . '/../database/seeders/BookingConfirmation.html'),
            'text_template' => 'Booking Confirmation, see HTML view for more details'
        ]);

        MailTemplate::create([
            'owner_type' => $business->getMorphClass(),
            'owner_id' => $business->id,
            'mailable' => PdfBookingConfirmation::class,
            'subject' => 'Booking Confirmation',
            'html_template' => file_get_contents(__DIR__ . '/../database/seeders/BookingConfirmationPdf.html'),
            'text_template' => ''
        ]);
    }
}
