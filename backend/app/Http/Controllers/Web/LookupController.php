<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\LookupRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source' => 'required|string|in:' . implode(',', LookupRegistry::sourceKeys()),
            'filters' => 'nullable|array',
            'filters.*' => 'nullable|string|max:100',
        ]);

        $items = LookupRegistry::getItems(
            $validated['source'],
            $validated['filters'] ?? null
        );

        return response()->json(['data' => $items->values()]);
    }
}
