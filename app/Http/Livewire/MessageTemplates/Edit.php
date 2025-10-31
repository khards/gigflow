<?php

namespace App\Http\Livewire\MessageTemplates;

use App\Domains\Email\Models\MailTemplate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public MailTemplate $mailTemplate;

    public $subject;

    public $type = "";

    protected $rules = [
        'mailTemplate.subject' => 'required|string|max:512',
        'mailTemplate.html_template' => 'required|string|max:5120',
        'mailTemplate.text_template' => 'required|string|max:5120',
    ];

    protected $listeners = [
        'save' => 'save',
    ];

    public function mount($mailTemplate): void
    {
        $this->mailTemplate = $mailTemplate;
        $this->subject = $mailTemplate->name;
        $this->type = $this->getType();
    }

    public function updated()
    {
        $this->dispatchBrowserEvent('template-saved', ['html_template' => $this->mailTemplate->html_template]);

        $this->mailTemplate->save();
    }

    public function render()
    {
        $mailTemplate = $this->mailTemplate;

        return view('frontend.user.message-templates.edit-livewire', compact(['mailTemplate']));
    }

    private function getType()
    {
        $bits = explode('\\', $this->mailTemplate->mailable);
        return end($bits);
    }
}
