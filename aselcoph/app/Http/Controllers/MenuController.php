<?php
// app/Http/Controllers/MenuController.php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::orderBy('id')->get();
        return view('pages.admin.menu.index', compact('menus'));
    }

    public function create(Menu $menu)
    {
        $menu->load('itemsWithChildren');
        return view('pages.admin.menu.create', compact('menu'));
    }

    public function builder(Menu $menu)
    {
        $menu->load('itemsWithChildren');
        return view('pages.admin.menu.builder', compact('menu'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key'         => ['required', 'string', 'max:100', 'unique:menus,key'],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        $menu = Menu::create($data);
        return response()->json($menu, 201);
    }

    public function show(Menu $menu)
    {
        $menu->load('itemsWithChildren');
        return $menu;
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'key'         => ['sometimes', 'string', 'max:100', 'unique:menus,key,' . $menu->id],
            'name'        => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        $menu->update($data);
        return $menu;
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return response()->noContent();
    }


}
