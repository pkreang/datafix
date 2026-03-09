<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $token = session('api_token');
        $apiReq = Request::create('/api/v1/permissions', 'GET');
        $apiReq->headers->set('Authorization', 'Bearer ' . $token);
        $apiReq->headers->set('Accept', 'application/json');
        $apiReq->cookies->replace($request->cookies->all());

        $response = app()->handle($apiReq);
        $data = json_decode($response->getContent(), true);
        $grouped = $data['grouped'] ?? [];
        $total = $data['total'] ?? 0;

        return view('permissions.index', compact('grouped', 'total'));
    }
}
