<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BelongsToBusinessOrUserScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $business = auth()->user()?->businesses()->first();
        if ($business) {
            $builder->where(function ($builder) use ($business) {
                $builder->where('model_type', "business")
                    ->where('model_id', $business->id);
            })->orWhere(function ($builder) use ($business) {
                $builder->where('model_type', "user")
                    ->where('model_id', auth()->user()->id);
            });
        }
    }
}
