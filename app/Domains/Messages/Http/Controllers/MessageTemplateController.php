<?php

namespace App\Domains\Messages\Http\Controllers;

use App\Booking\Business;
use App\Domains\Email\Models\MailTemplate;
use App\Domains\Order\Order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class MessageTemplateController extends Controller
{
    /**
     * Get all mailables and render a table.
     *
     * @param $businessId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Business $business)
    {
        return view('frontend.user.message-templates.index', compact(['business']));
    }

    /**
     * Return the edit a mailable screen.
     *
     * @param MailTemplate $mailTemplate
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($mailTemplate)
    {
        $mailTemplate = MailTemplate::findOrFail($mailTemplate);

        return view('frontend.user.message-templates.edit', compact(['mailTemplate']));
    }

    /**
     * Render the edit a mailable screen.
     *
     * @param MailTemplate $mailTemplate
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function preview(int $mailTemplateId)
    {
        $template = MailTemplate::findOrFail($mailTemplateId);

         try {
                $order = auth()->user()?->businesses()->first()?->orders()->first();

                if(!$order) {
                    $order = Order::first(); // Hopefully my test booking :-)
                }
                $params = ['order' => $order, 'user' => $order->user];

                $templateRenderer = new $template->mailable($params);
                echo htmlspecialchars_decode($templateRenderer->getRendered());
        } catch (\Exception $e) {
            echo "<h1>Unable to render template</h1>";
            echo "The following problem was encountered: ". $e->getMessage();
            Log::debug("The following problem was encountered when previewing an email: ". $e->getMessage(), [$order]);
        }
    }
}
