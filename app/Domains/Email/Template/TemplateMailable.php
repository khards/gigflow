<?php

namespace App\Domains\Email\Template;
use Spatie\MailTemplates\TemplateMailable as SpatieTemplate;

/**
 * This is NOT a model, It is just a renderer for models.
 */
abstract class TemplateMailable extends SpatieTemplate
{
    /** Define the owner of this template */
    abstract public function owner();

    /**
     * Get a HTML version of the rendered email
     *
     * @return \Illuminate\Support\HtmlString|mixed|string
     * @throws \ReflectionException
     */
    public function getRendered() {
        return $this->buildView()['html'];
    }

    /**
     * Get the emails main html template.
     *
     * @return string
     */
    public function getHtmlLayout(): string
    {

        $data = [
            'subject' => $this->subject,
            'business' => $this->business,
            'unsubscribe_url' => 'htps://elitebookingsystem.com/unsubscribe/'
        ];
        return view('mail.main-layout', $data)->render();
    }
}
