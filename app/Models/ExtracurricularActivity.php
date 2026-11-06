<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtracurricularActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'sort_order',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Published activities in their display order, as shown on the public site.
     *
     * @param  Builder<ExtracurricularActivity>  $query
     */
    public function scopeForPublicSite(Builder $query): void
    {
        $query->where('published', true)->orderBy('sort_order')->orderBy('name');
    }
}
