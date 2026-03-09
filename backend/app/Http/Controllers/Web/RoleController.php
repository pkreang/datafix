<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $response = $this->apiRequest($request, 'GET', '/roles');
        $data = $this->apiJson($response);
        $roles = $data['data'] ?? [];

        return view('roles.index', compact('roles'));
    }

    public function show(Request $request, int $id): View
    {
        $response = $this->apiRequest($request, 'GET', '/roles/' . $id);
        $data = $this->apiJson($response);
        $role = $data['data'] ?? [];

        return view('roles.show', compact('role'));
    }

    public function create(Request $request): View
    {
        $permRes = $this->apiRequest($request, 'GET', '/permissions');
        $permData = $this->apiJson($permRes);
        $grouped = $permData['grouped'] ?? [];

        return view('roles.create', compact('grouped'));
    }

    public function store(Request $request)
    {
        $response = $this->apiRequest($request, 'POST', '/roles', [], [
            'name'        => $request->name,
            'permissions' => $request->permissions ?? [],
        ]);

        $data = $this->apiJson($response);

        if ($response->getStatusCode() >= 400) {
            return back()->withInput()->withErrors(['name' => $data['message'] ?? 'Error creating role.']);
        }

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Request $request, int $id): View
    {
        $roleRes = $this->apiRequest($request, 'GET', '/roles/' . $id);
        $roleData = $this->apiJson($roleRes);
        $role = $roleData['data'] ?? [];

        $permRes = $this->apiRequest($request, 'GET', '/permissions');
        $permData = $this->apiJson($permRes);
        $grouped = $permData['grouped'] ?? [];

        return view('roles.edit', compact('role', 'grouped'));
    }

    public function update(Request $request, int $id)
    {
        $response = $this->apiRequest($request, 'PUT', '/roles/' . $id, [], [
            'name'        => $request->name,
            'permissions' => $request->permissions ?? [],
        ]);

        $data = $this->apiJson($response);

        if ($response->getStatusCode() >= 400) {
            return back()->withInput()->withErrors(['name' => $data['message'] ?? 'Error updating role.']);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->apiRequest($request, 'DELETE', '/roles/' . $id);

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    protected function apiRequest(Request $request, string $method, string $path, array $params = [], array $body = []): SymfonyResponse
    {
        $token = session('api_token');
        $query = $params ? '?' . http_build_query($params) : '';
        $apiReq = Request::create('/api/v1' . $path . $query, $method, $body);
        $apiReq->headers->set('Authorization', 'Bearer ' . $token);
        $apiReq->headers->set('Accept', 'application/json');
        $apiReq->cookies->replace($request->cookies->all());

        return app()->handle($apiReq);
    }

    protected function apiJson(SymfonyResponse $response): array
    {
        return json_decode($response->getContent(), true) ?? [];
    }
}
