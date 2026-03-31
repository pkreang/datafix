<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ThailandAddressSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThailandAddressSearchController extends Controller
{
    public function subdistricts(Request $request, ThailandAddressSearchService $service): JsonResponse
    {
        $q = (string) $request->query('q', '');

        return response()->json([
            'data' => $service->searchSubdistricts($q, 30),
        ]);
    }
}
