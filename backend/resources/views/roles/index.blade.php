@extends('layouts.app')

@section('title', __('common.roles'))

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.all_roles') }}</h2>
        <a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('common.add_role') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-800/80">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.role') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.permissions') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.users') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.created_at') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($roles as $role)
                    <tr class="hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-6 py-3 whitespace-nowrap">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $role['name'] ?? '' }}</p>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $role['permissions_count'] ?? 0 }}</td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $role['users_count'] ?? 0 }}</td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ isset($role['created_at']) ? \Carbon\Carbon::parse($role['created_at'])->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-right">
                            <div x-data="{ open: false }" class="relative inline-block">
                                <button @click="open = !open" type="button"
                                        class="p-1.5 rounded-lg text-gray-400
                                               hover:text-gray-600 dark:hover:text-gray-300
                                               hover:bg-gray-100 dark:hover:bg-gray-700
                                               transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="5" r="1.5"/>
                                        <circle cx="12" cy="12" r="1.5"/>
                                        <circle cx="12" cy="19" r="1.5"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.outside="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     x-cloak
                                     class="absolute right-0 top-8 w-36 z-30
                                            bg-white dark:bg-gray-800
                                            border border-gray-200 dark:border-gray-700
                                            rounded-xl shadow-lg py-1">
                                    <a href="{{ route('roles.edit', $role['id']) }}"
                                       class="flex items-center gap-2 px-3 py-2 text-sm
                                              text-gray-700 dark:text-gray-300
                                              hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ __('common.edit') }}
                                    </a>
                                    <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                    <button @click="open = false;
                                                    $dispatch('open-delete-modal', {
                                                        id: {{ $role['id'] }},
                                                        name: {{ json_encode($role['display_name'] ?? $role['name'] ?? '') }}
                                                    })"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left
                                                   text-red-600 dark:text-red-400
                                                   hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        {{ __('common.delete') }}
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('common.no_roles_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Delete Confirm Modal --}}
    <div x-data="{ show: false, id: null, name: '' }"
         @open-delete-modal.window="show = true; id = $event.detail.id; name = $event.detail.name"
         x-show="show" x-cloak x-transition
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div @click.outside="show = false"
             class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-80 shadow-xl text-center">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                {{ __('common.confirm_delete') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ __('common.delete') }} <strong x-text="name"></strong>?
                <br><span class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.cannot_undo') }}</span>
            </p>
            <div class="flex gap-2 mt-4">
                <button @click="show = false"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-lg
                               border border-gray-300 dark:border-gray-600
                               text-gray-700 dark:text-gray-300
                               bg-white dark:bg-gray-800
                               hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    {{ __('common.cancel') }}
                </button>
                <form x-bind:action="`{{ url('roles') }}/${id}`" method="POST" class="flex-1" x-show="id">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium rounded-lg
                                   bg-red-600 hover:bg-red-700 text-white transition-colors">
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
