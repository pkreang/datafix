@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">All Roles</h2>
        <a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Role
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($roles as $role)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-sm font-medium text-gray-900">{{ $role['name'] ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $role['permissions_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $role['users_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ isset($role['created_at']) ? \Carbon\Carbon::parse($role['created_at'])->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                            <a href="{{ route('roles.show', $role['id']) }}" class="text-blue-600 hover:text-blue-800">View</a>
                            <a href="{{ route('roles.edit', $role['id']) }}" class="text-yellow-600 hover:text-yellow-800">Edit</a>
                            <form method="POST" action="{{ route('roles.destroy', $role['id']) }}" class="inline" onsubmit="return confirm('Delete this role?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">No roles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
