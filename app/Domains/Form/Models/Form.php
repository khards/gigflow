<?php

namespace App\Domains\Form\Models;

use Database\Factories\FormFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Form extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'owner_type',
        'owner_id',
        'data',
        'action',
        'settings',
        'required',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'action' => 'collection',
        'settings' => 'collection',
    ];

    public function getDataAttribute()
    {
        if (is_array($this->attributes['data'])) {
            return $this->attributes['data'];
        }

        if (is_string($this->attributes['data'])) {
            return json_decode($this->attributes['data'], false);
        }
    }

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [

    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory()//Factory
    {
        return FormFactory::new();
    }

    /**
     * The morphto owner model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include required forms.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }

    /**
     * Scope a query to only include shared forms.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShared($query, bool $yn = true)
    {
        return $query->where('shared', $yn);
    }

    /**
     * @return Collection
     */
    public function getFormData()
    {
        return collect(json_decode($this->data));
    }

    /**
     * Get only questions with fixed resonses. Handy for the filter setup screen.
     *
     * @return Collection
     */
    public function getQuestionsWithFixedResponses()
    {
        $questionTypes = ['select', 'radio-group', 'autocomplete'];

        return $this->getFormData()->whereIn('type', $questionTypes);
    }

    /**
     * Get response values for a given question name.
     */
    public function getResponseValuesForQuestion($questionName)
    {
        $filtered = $this->getFormData()->whereIn('name', $questionName)->first();

        if ($filtered === null) {
            return collect([]);
        }

        if (! property_exists($filtered, 'values')) {
            return collect([]);
        }

        return collect($filtered->values);
    }
}
