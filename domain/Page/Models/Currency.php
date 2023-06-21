<?php

namespace Domain\Page\Models;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{


    protected $fillable = [
        'code',
        'name',
        'enabled',
        'exchange_rate',
        'default',
        'created_at',
        'updated_at',
    ];


    protected static function boot()
    {
        parent::boot();

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
    }
}
