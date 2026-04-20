<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserPinnedMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPinnedMenuController extends Controller
{
    /**
     * Toggle pin/unpin for the signed-in user. Returns the new state so the UI
     * can update star icons without a full page reload.
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_key' => ['required', 'string', 'max:100'],
        ]);

        $userId = (int) (session('user.id') ?? 0);
        if (! $userId) {
            abort(401);
        }

        $pinned = UserPinnedMenu::toggle($userId, $validated['menu_key']);

        return response()->json([
            'pinned' => $pinned,
            'menu_key' => $validated['menu_key'],
        ]);
    }
}
