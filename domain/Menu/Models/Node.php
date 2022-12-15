<?php

declare(strict_types=1);

namespace Domain\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Node extends Model implements Sortable
{
    use SortableTrait;

    protected $fillable = [
        'name',
        'menu_id',
        'parent_id',
        'url',
        'target',
        'sort',
    ];

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function childs()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
}
