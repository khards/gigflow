<?php
namespace App\Domains\Email\Models;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MailTemplates\Interfaces\MailTemplateInterface;
use Spatie\MailTemplates\Models\MailTemplate as SpatieMailTemplate;

/**
 * @property MorphTo $owner
 */
class MailTemplate extends SpatieMailTemplate implements MailTemplateInterface
{
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Select owner specific template. The owner is extracted from the mailable..
     *
     * @param Builder $query
     * @param Mailable $mailable
     * @return Builder
     */
    public function scopeForMailable(Builder $query, Mailable $mailable): Builder
    {
        return $query
            ->where('mailable', get_class($mailable))
            ->where('owner_type', $mailable->owner()->getMorphClass())
            ->where('owner_id', $mailable->owner()->id);
    }

//    public function getHtmlLayout(): string
//    {
//        return $this->owner->mail_layout;
//    }
}
