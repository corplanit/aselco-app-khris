<?php
// app/Http/Controllers/MenuItemController.php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuItemController extends Controller
{
    // app/Http/Controllers/MenuItemController.php

public function saveTree(Request $request)
{
    $data = $request->validate([
        'menu_id' => ['required','exists:menus,id'],
        'items'   => ['required','array'],
        'items.*.id' => ['required','exists:menu_items,id'],
        'items.*.parent_id' => ['nullable','integer'],
        'items.*.order'     => ['required','integer'],
    ]);

    // Persist parent + order in bulk, ensuring items belong to the menu
    foreach ($data['items'] as $row) {
        $item = MenuItem::find($row['id']);
        if (!$item || (int)$item->menu_id !== (int)$data['menu_id']) {
            return response()->json(['message' => 'Invalid item/menu mapping.'], 422);
        }
        // parent cross-check: parent must be same menu (or null)
        if (!empty($row['parent_id'])) {
            $parent = MenuItem::find($row['parent_id']);
            if (!$parent || (int)$parent->menu_id !== (int)$data['menu_id']) {
                return response()->json(['message' => 'Parent belongs to different menu.'], 422);
            }
        }
        $item->update([
            'parent_id' => $row['parent_id'] ?: null,
            'order'     => (int)$row['order'],
        ]);
    }

    return response()->json(['status' => 'ok']);
}


    public function store(Request $request)
    {
        $data = $request->validate([
            'menu_id'    => ['required', 'exists:menus,id'],
            'parent_id'  => ['nullable', 'exists:menu_items,id'],
            'label'      => ['required', 'string', 'max:150'],
            'icon'       => ['nullable', 'string', 'max:150'],
            'link_type'  => ['required', Rule::in(['url','route'])],
            'custom_url' => ['nullable', 'url'],
            'route_name' => ['nullable', 'string', 'max:190'],
            'route_params' => ['nullable', 'array'],
            'target'     => ['nullable', Rule::in(['_self','_blank'])],
            'order'      => ['nullable', 'integer'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        // Enforce link-type-specific requirements
        if ($data['link_type'] === 'url') {
            $request->validate(['custom_url' => ['required','url']]);
            $data['route_name'] = null;
            $data['route_params'] = null;
        } else {
            $request->validate(['route_name' => ['required','string']]);
            $data['custom_url'] = null;
        }

        // Optional: prevent cross-menu parenting
        if (!empty($data['parent_id'])) {
            $parent = MenuItem::findOrFail($data['parent_id']);
            if ((int)$parent->menu_id !== (int)$data['menu_id']) {
                return response()->json(['message' => 'Parent belongs to different menu.'], 422);
            }
        }

        $item = MenuItem::create($data);
        return response()->json($item->fresh(), 201);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $data = $request->validate([
            'parent_id'  => ['nullable', 'exists:menu_items,id'],
            'label'      => ['sometimes', 'string', 'max:150'],
            'icon'       => ['nullable', 'string', 'max:150'],
            'link_type'  => ['sometimes', Rule::in(['url','route'])],
            'custom_url' => ['nullable', 'url'],
            'route_name' => ['nullable', 'string', 'max:190'],
            'route_params' => ['nullable', 'array'],
            'target'     => ['nullable', Rule::in(['_self','_blank'])],
            'order'      => ['nullable', 'integer'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('link_type', $data)) {
            if ($data['link_type'] === 'url') {
                $request->validate(['custom_url' => ['required','url']]);
                $data['route_name'] = null;
                $data['route_params'] = null;
            } else {
                $request->validate(['route_name' => ['required','string']]);
                $data['custom_url'] = null;
            }
        }

        if (array_key_exists('parent_id', $data) && !empty($data['parent_id'])) {
            $parent = MenuItem::findOrFail($data['parent_id']);
            if ((int)$parent->menu_id !== (int)$menuItem->menu_id) {
                return response()->json(['message' => 'Parent belongs to different menu.'], 422);
            }
        }

        $menuItem->update($data);
        return $menuItem->fresh();
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();
        return response()->noContent();
    }

    /**
     * Bulk reorder items under a parent (or root) for a given menu.
     * payload:
     * {
     *   "menu_id": 1,
     *   "parent_id": null,
     *   "orders": [{"id": 10, "order": 0}, {"id": 11, "order": 1}]
     * }
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'menu_id'   => ['required', 'exists:menus,id'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'orders'    => ['required', 'array'],
            'orders.*.id'    => ['required', 'exists:menu_items,id'],
            'orders.*.order' => ['required', 'integer'],
        ]);

        // Optional: safety check parent menu consistency
        if ($data['parent_id']) {
            $parent = MenuItem::findOrFail($data['parent_id']);
            if ((int)$parent->menu_id !== (int)$data['menu_id']) {
                return response()->json(['message' => 'Parent belongs to different menu.'], 422);
            }
        }

        foreach ($data['orders'] as $row) {
            MenuItem::where('id', $row['id'])
                ->where('menu_id', $data['menu_id'])
                ->update(['order' => $row['order']]);
        }

        return response()->json(['status' => 'ok']);
    }
}
