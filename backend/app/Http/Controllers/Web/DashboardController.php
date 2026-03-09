<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $token = session('api_token');
        $totalUsers = 0;
        $totalRoles = 0;
        $totalPermissions = 0;

        if ($token) {
            try {
                $usersRes = $this->apiRequest($request, '/users?per_page=100');
                if ($usersRes->getStatusCode() === 200) {
                    $usersData = json_decode($usersRes->getContent(), true);
                    $totalUsers = $usersData['data']['total'] ?? count($usersData['data']['data'] ?? []);
                }

                $rolesRes = $this->apiRequest($request, '/roles');
                if ($rolesRes->getStatusCode() === 200) {
                    $rolesData = json_decode($rolesRes->getContent(), true);
                    $totalRoles = count($rolesData['data'] ?? []);
                }

                $permsRes = $this->apiRequest($request, '/permissions');
                if ($permsRes->getStatusCode() === 200) {
                    $permsData = json_decode($permsRes->getContent(), true);
                    $totalPermissions = $permsData['total'] ?? count($permsData['data'] ?? []);
                }
            } catch (\Throwable) {
            }
        }

        return view('dashboard', compact('totalUsers', 'totalRoles', 'totalPermissions'));
    }

    private function apiRequest(Request $request, string $path): Response
    {
        $token = session('api_token');
        $apiReq = Request::create('/api/v1' . $path, 'GET');
        $apiReq->headers->set('Authorization', 'Bearer ' . $token);
        $apiReq->headers->set('Accept', 'application/json');
        $apiReq->cookies->replace($request->cookies->all());

        return app()->handle($apiReq);
    }
}
