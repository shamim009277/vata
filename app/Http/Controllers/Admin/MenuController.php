<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check permission manually or rely on route middleware
        // if (!Auth::user()->hasPermissionTo('menus.index')) {
        //     abort(403, 'Unauthorized action.');
        // }

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

        // Create the menu
        $menu = Menu::create($request->all());

        // Create permissions if permission_name is provided
        if ($request->filled('permission_name')) {
            $permissionNames = explode(',', $request->permission_name);
            
            foreach ($permissionNames as $permissionName) {
                $permissionName = trim($permissionName);
                if (!empty($permissionName)) {
                    // Check if permission already exists for any menu
                    $existingPermission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();
                    
                    if ($existingPermission) {
                        // If permission exists but not linked to this menu, update it
                        if ($existingPermission->menu_id !== $menu->id) {
                            $existingPermission->update(['menu_id' => $menu->id]);
                        }
                    } else {
                        // Create new permission
                        \Spatie\Permission\Models\Permission::create([
                            'name' => $permissionName,
                            'menu_id' => $menu->id,
                        ]);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Menu created successfully with permissions.');
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

        // Update permissions if permission_name is provided
        if ($request->filled('permission_name')) {
            // Delete existing permissions for this menu
            \Spatie\Permission\Models\Permission::where('menu_id', $menu->id)->delete();
            
            $permissionNames = explode(',', $request->permission_name);
            
            foreach ($permissionNames as $permissionName) {
                $permissionName = trim($permissionName);
                if (!empty($permissionName)) {
                    // Check if permission already exists for any menu
                    $existingPermission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();
                    
                    if ($existingPermission) {
                        // If permission exists but not linked to this menu, update it
                        if ($existingPermission->menu_id !== $menu->id) {
                            $existingPermission->update(['menu_id' => $menu->id]);
                        }
                    } else {
                        // Create new permission
                        \Spatie\Permission\Models\Permission::create([
                            'name' => $permissionName,
                            'menu_id' => $menu->id,
                        ]);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Menu updated successfully with permissions.');
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
