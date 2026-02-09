<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        $menus = [];
        
        if ($user) {
            $allMenus = Menu::whereNull('parent_id')->with(['children' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            }])->where('is_active', true)->orderBy('order')->get();

            $menus = $allMenus->filter(function ($menu) use ($user) {
                return $this->canViewMenu($user, $menu);
            })->values();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
                'roles' => $user ? $user->getRoleNames() : [],
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'menus' => $menus,
        ];
    }

    private function canViewMenu($user, $menu)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Check specific permission if set
        if ($menu->permission_name) {
            if (!$user->can($menu->permission_name)) {
                return false;
            }
        }

        // If it has children, filter them and only show parent if at least one child is visible
        if ($menu->children->isNotEmpty()) {
            $filteredChildren = $menu->children->filter(function ($child) use ($user) {
                return $this->canViewMenu($user, $child);
            })->values();

            $menu->setRelation('children', $filteredChildren);

            return $filteredChildren->isNotEmpty();
        }

        // If it's a parent menu (has no route/url) but has no children visible (filtered out above),
        // it should be hidden.
        // But wait, if it was a parent, it fell into the previous block.
        // If it reaches here, it either has no children originally, OR it's a leaf.
        
        // If it's a leaf (no children) and has no permission set, default to visible.
        return true;
    }
}
