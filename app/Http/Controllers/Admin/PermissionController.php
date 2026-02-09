<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Menu;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check permission manually or rely on route middleware
        // if (!Auth::user()->hasPermissionTo('permissions.index')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $permissions = Permission::with('menu')->get();
        // Only get menus that have a route or url (likely leaf nodes/actual pages)
        $menus = Menu::whereNotNull('route')->orWhereNotNull('url')->get();
        
        return Inertia::render('Admin/Permission/Index', [
            'permissions' => $permissions,
            'menus' => $menus
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'menu_id' => 'nullable|exists:menus,id',
        ]);

        // Check if name contains comma, split it
        $names = explode(',', $request->name);

        foreach ($names as $name) {
            $name = trim($name);
            if (!empty($name)) {
                // Check if permission already exists to avoid duplication error
                if (!Permission::where('name', $name)->exists()) {
                    Permission::create([
                        'name' => $name,
                        'menu_id' => $request->menu_id
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Permissions created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
            'menu_id' => 'nullable|exists:menus,id',
        ]);

        $permission->update([
            'name' => $request->name,
            'menu_id' => $request->menu_id
        ]);

        return redirect()->back()->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->back()->with('success', 'Permission deleted successfully.');
    }
}
