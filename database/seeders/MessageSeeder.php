<?php

namespace Database\Seeders;

use App\Booking\Business;
use App\Domains\Email\Mailables\Booking\BookingConfirmation;
use App\Domains\Email\Mailables\Booking\PdfBookingConfirmation;
use App\Domains\Email\Mailables\Payments\PaymentReceivedReceipt;
use App\Domains\Email\Mailables\Quote\Quote;
use Illuminate\Database\Seeder;
use Spatie\MailTemplates\Models\MailTemplate;

class MessageSeeder //extends Seeder
{
    public function __construct(private Business $business) {

    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$this->business = Business::make();

        // Seed emails.
        $this->emailPaymentReceivedReceipt();
        $this->emailBookingConfirmation();
        $this->pdfBookingConfirmation();
        $this->quote();
    }

    private function emailPaymentReceivedReceipt(): void
    {
        if (MailTemplate::where('mailable', PaymentReceivedReceipt::class)->exists()) {
            echo "Didn't seed PaymentReceivedReceipt, as it exists.\n";
            return;
        }

        MailTemplate::create([
            'owner_type' => $this->business->getMorphClass(),
            'owner_id' => $this->business->id,
            'mailable' => PaymentReceivedReceipt::class,
            'subject' => 'Thank you for your payment, {{ name }}',
            'html_template' => file_get_contents(__DIR__ . '/PaymentReceipt.html'),
            'text_template' => '<p>Hello, {{ name }}.</p><p>Thanks for your payment of {{ amount }}<br/>Best regards, DJ Keith Hards.</p>'
        ]);
    }

    private function emailBookingConfirmation(): void
    {
        if (MailTemplate::where('mailable', BookingConfirmation::class)->exists()) {
            echo "Didn't seed BookingConfirmation, as it exists.\n";
            return;
        }

        MailTemplate::create([
            'owner_type' => $this->business->getMorphClass(),
            'owner_id' => $this->business->id,
            'mailable' => BookingConfirmation::class,
            'subject' => 'Booking Confirmation',
            'html_template' => file_get_contents(__DIR__ . '/BookingConfirmation.html'),
            'text_template' => 'Hello, {{ name }}.'
        ]);
    }

    private function pdfBookingConfirmation(): void
    {
        if (MailTemplate::where('mailable', PdfBookingConfirmation::class)->exists()) {
            echo "Didn't seed PdfBookingConfirmation, as it exists.\n";
            return;
        }

        MailTemplate::create([
            'owner_type' => $this->business->getMorphClass(),
            'owner_id' => $this->business->id,
            'mailable' => PdfBookingConfirmation::class,
            'subject' => 'PDF Booking Confirmation',
            'html_template' => file_get_contents(__DIR__ . '/BookingConfirmationPdf.html'),
            'text_template' => 'N/A'
        ]);
    }


    private function quote(): void
    {
        if (MailTemplate::where('mailable', Quote::class)->exists()) {
            echo "Didn't seed Quote, as it exists.\n";
            return;
        }

        MailTemplate::create([
            'owner_type' => $this->business->getMorphClass(),
            'owner_id' => $this->business->id,
            'mailable' => Quote::class,
            'subject' => 'Quotation',
            'html_template' => file_get_contents(__DIR__ . '/Quote.html'),
            'text_template' => 'N/A'
        ]);
    }

}
