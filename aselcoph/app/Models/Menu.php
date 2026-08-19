<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['key', 'name', 'description'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    // Convenience to eager-load full tree
    public function itemsWithChildren()
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->with(['childrenRecursive'])
            ->orderBy('order');
    }
}
