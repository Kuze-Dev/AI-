<?php

namespace Domain\Taxonomy\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

use Domain\TaxonomyTerm\Models\TaxonomyTerm;

class Taxonomy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function taxonomy_terms(): HasMany 
    {
        return $this->hasMany(TaxonomyTerm::class);
    }
    

}
