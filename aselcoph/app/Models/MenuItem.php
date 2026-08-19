<?php
// app/Models/MenuItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'icon',
        'link_type',
        'custom_url',
        'route_name',
        'route_params',
        'target',
        'order',
        'is_active',
    ];

    protected $casts = [
        'route_params' => 'array',
        'is_active'    => 'boolean',
        'order'        => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    // Eager-load the whole subtree
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    // Resolve the final URL you can directly use in Blade
    public function getHrefAttribute(): ?string
    {
        if ($this->link_type === 'route' && $this->route_name) {
            try {
                return route($this->route_name, $this->route_params ?? []);
            } catch (\Throwable $e) {
                return '#'; // Fallback if route missing / params bad
            }
        }
        return $this->custom_url ?: '#';
    }
}
