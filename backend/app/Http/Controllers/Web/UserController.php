<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $response = $this->apiRequest($request, 'GET', '/users', ['per_page' => 100]);
        $data = json_decode($response->getContent(), true);

        if ($response->getStatusCode() === 403) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view users.');
        }

        $users = $data['data']['data'] ?? $data['data'] ?? [];

        return view('users.index', compact('users'));
    }

    public function create(Request $request): View
    {
        $rolesRes = $this->apiRequest($request, 'GET', '/roles');
        $rolesData = json_decode($rolesRes->getContent(), true);
        $roles = $rolesData['data'] ?? [];

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        return redirect()->route('users.index');
    }

    public function edit(Request $request, int $id): View
    {
        $userRes = $this->apiRequest($request, 'GET', '/users/' . $id);
        $userData = json_decode($userRes->getContent(), true);
        $user = $userData['data'] ?? [];

        $rolesRes = $this->apiRequest($request, 'GET', '/roles');
        $rolesData = json_decode($rolesRes->getContent(), true);
        $roles = $rolesData['data'] ?? [];

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, int $id)
    {
        return redirect()->route('users.index');
    }

    public function destroy(int $id)
    {
        return redirect()->route('users.index');
    }

    protected function apiRequest(Request $request, string $method, string $path, array $params = []): SymfonyResponse
    {
        $token = session('api_token');
        $query = $params ? '?' . http_build_query($params) : '';
        $apiReq = Request::create('/api/v1' . $path . $query, $method);
        $apiReq->headers->set('Authorization', 'Bearer ' . $token);
        $apiReq->headers->set('Accept', 'application/json');
        $apiReq->cookies->replace($request->cookies->all());

        return app()->handle($apiReq);
    }
}
