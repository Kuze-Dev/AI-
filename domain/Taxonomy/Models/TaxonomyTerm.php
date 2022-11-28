<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxonomyTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxonomy_id',
        'name',
        'slug',
        'description',
        'order',
    ];

    public function taxonomies(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }
}
