<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::with('children')->whereNull('parent_id')->orderBy('order')->get();
        $allMenus = Menu::orderBy('title')->get(); // For parent selection dropdown
        
        return Inertia::render('Admin/Menu/Index', [
            'menus' => $menus,
            'allMenus' => $allMenus
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'integer',
            'permission_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Menu::create($request->all());

        return redirect()->back()->with('success', 'Menu created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'integer',
            'permission_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $menu->update($request->all());

        return redirect()->back()->with('success', 'Menu updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->back()->with('success', 'Menu deleted successfully.');
    }
}
