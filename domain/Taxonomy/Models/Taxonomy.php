<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taxonomy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function taxonomyTerms(): HasMany
    {
        return $this->hasMany(TaxonomyTerm::class);
    }
}
