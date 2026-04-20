<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class NavigationMenuController extends Controller
{
    public function index(): View
    {
        $rootMenus = NavigationMenu::whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('sort_order')
            ->get();

        return view('settings.navigation.index', compact('rootMenus'));
    }

    public function create(): View
    {
        $menu = new NavigationMenu;
        $parentMenus = NavigationMenu::whereNull('parent_id')->orderBy('sort_order')->get();
        $permissions = Permission::orderBy('name')->pluck('name');

        return view('settings.navigation.form', compact('menu', 'parentMenus', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:255',
            'label_en' => 'nullable|string|max:255',
            'label_th' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:navigation_menus,id',
            'permission' => 'nullable|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['label'] = $data['label'] ?? $data['label_en'] ?? $data['label_th'] ?? '';

        NavigationMenu::create($data);

        return redirect()->route('settings.navigation.index')
            ->with('success', __('common.navigation_menu_item_created'));
    }

    public function edit(NavigationMenu $navigation): View
    {
        $menu = $navigation;
        $parentMenus = NavigationMenu::whereNull('parent_id')
            ->where('id', '!=', $menu->id)
            ->orderBy('sort_order')
            ->get();
        $permissions = Permission::orderBy('name')->pluck('name');

        return view('settings.navigation.form', compact('menu', 'parentMenus', 'permissions'));
    }

    public function update(Request $request, NavigationMenu $navigation)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:255',
            'label_en' => 'nullable|string|max:255',
            'label_th' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:navigation_menus,id',
            'permission' => 'nullable|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['label'] = $data['label'] ?? $data['label_en'] ?? $data['label_th'] ?? $navigation->label;

        if ($data['parent_id'] == $navigation->id) {
            $data['parent_id'] = null;
        }

        $navigation->update($data);

        return redirect()->route('settings.navigation.index')
            ->with('success', __('common.navigation_menu_item_updated'));
    }

    public function destroy(NavigationMenu $navigation)
    {
        if ($navigation->allChildren()->exists()) {
            return redirect()->route('settings.navigation.index')
                ->with('error', __('common.navigation_menu_delete_has_children'));
        }

        $navigation->delete();

        return redirect()->route('settings.navigation.index')
            ->with('success', __('common.navigation_menu_item_deleted'));
    }

    public function reorder(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $ids = $request->ids;

        $groups = [];
        foreach ($ids as $id) {
            $menu = NavigationMenu::find($id);
            if ($menu) {
                $groups[$menu->parent_id ?? 'root'][] = $menu;
            }
        }

        foreach ($groups as $items) {
            foreach ($items as $order => $menu) {
                $menu->update(['sort_order' => $order + 1]);
            }
        }

        Cache::forget('navigation_menus_tree');

        return response()->json(['success' => true]);
    }

    public function toggle(NavigationMenu $navigation)
    {
        $navigation->update(['is_active' => ! $navigation->is_active]);

        Cache::forget('navigation_menus_tree');

        return response()->json(['is_active' => $navigation->is_active]);
    }
}
