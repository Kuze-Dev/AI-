<?php

namespace Domain\Currency\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Currency extends Model
{


    protected $fillable = [
        'code',
        'name',
        'enabled',
        'exchange_rate',
        'default',
    ];


    protected static function boot()
    {
        parent::boot();

        Relation::morphMap([
            'currency' => 'Domain\Currency\Models\Currency',
        ]);

        self::creating(function ($currency) {
            // Disable all other currencies when creating a new enabled currency
            
            if ($currency->enabled) {
                self::where('enabled', true)->update(['enabled' => false]);
            }
            if ($currency->default) {
                self::where('default', true)->update(['default' => false]);
            }
        });

        self::updating(function ($currency) {
            // Disable all other currencies when updating to an enabled currency
            if ($currency->enabled) {
                self::where('id', '!=', $currency->id)->update(['enabled' => false]);
            }
            if ($currency->default) {
                self::where('id', '!=', $currency->id)->update(['default' => false]);
            }
        });
        
        self::saved(function () {
            if (!self::where('enabled', true)->exists()) {
                self::where('default', true)->update(['enabled' => true]);
            }
        });
    }
}
