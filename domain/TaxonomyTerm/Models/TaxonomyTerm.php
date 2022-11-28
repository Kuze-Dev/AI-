<?php

namespace Domain\TaxonomyTerm\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Domain\Taxonomy\Models\Taxonomy;

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
