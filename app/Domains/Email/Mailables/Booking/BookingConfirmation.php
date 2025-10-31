<?php

namespace App\Domains\Email\Mailables\Booking;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Email\Models\MailTemplate;
use App\Domains\Email\Template\TemplateMailable;
use App\Domains\Order\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingConfirmation extends TemplateMailable
{
    public Order $order;
    public User $user;
    public $business; // Business
    public $business_address;
    public $billpayer;
    public string $datetime;
    public string $payment_link;

    protected string $pdfMailableName = PdfBookingConfirmation::class;

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $user = $params['user'];
        $order = $params['order'];

        $this->user = $user;
        $this->order = $order;
        $this->billpayer = $order->getBillpayer()->first();
        $this->business = $order->business()->first();
        $this->business_address = $this->business->address()->first();
        $this->datetime = (new Carbon())->timezone($this->business->timezone)->format('l jS \\of F Y H:i');

        $this->payment_link = config('app.url').route('frontend.order.payment', $order->id, false);
    }

    public function owner() {
        return $this->order->business;
    }

    public function build()
    {
        $pdf = $this->getPdf($this->order);

        $this->attachData($pdf->stream(), 'booking_confirmation.pdf', [
            'mime' => 'application/pdf',
        ]);
        return parent::build();
    }

    protected function getPdf($order)
    {
        $template = MailTemplate::where('mailable', $this->pdfMailableName)
            ->where('owner_type', 'business')
            ->where('owner_id', 1)
            ->first();

        try {
            $params = ['order' => $order, 'user' => $order->user];
            $templateRenderer = new $template->mailable($params);
            $html = htmlspecialchars_decode($templateRenderer->getRendered());
        } catch (\Exception $e) {
            $html =  "<h1>Unable to render template</h1>";
            $html .= "The following problem was encountered: ". $e->getMessage();
            Log::debug("The following problem was encountered when sending a PDF with email: ". $e->getMessage(), [$order]);
        }

        try {
            $pdf = Pdf::loadHTML($html);
            //$pdf->setPaper('a4', 'portrait');// landscape
            return $pdf;

            // PDF::loadHTML($html)->setPaper('a4', 'landscape')->setWarnings(false)->save('myfile.pdf')
        } catch (\Exception $e) {
            Log::debug( __METHOD__ . "Error generating PDF : ". $e->getMessage(), [$order, $html]);
        }
    }
}
