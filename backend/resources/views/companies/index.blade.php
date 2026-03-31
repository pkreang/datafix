@extends('layouts.app')

@section('title', __('company.companies'))

@section('content')
<div>
    @if ($canCreateMore)
    <div class="flex items-center justify-between mb-2">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('company.all_companies') }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ trans_choice('company.companies_total', $companies->total(), ['count' => $companies->total()]) }}
            </p>
        </div>
        @can('manage companies')
            <div class="flex items-center gap-2">
                <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('company.add_company') }}
                </a>
            </div>
        @endcan
    </div>
    @endif

    @if ($canCreateMore)
    {{-- Search (multi mode only) --}}
    <form method="GET" action="{{ route('companies.index') }}" class="mb-5">
        <div class="relative max-w-sm">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="{{ __('company.search_placeholder') }}"
                   class="w-full pl-10 pr-4 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-gray-900 dark:text-gray-100">
        </div>
    </form>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    {{-- overflow-visible: dropdown แก้ไข/ลบเป็น absolute — overflow-hidden จะตัดเมนูทำให้กดไม่ได้ --}}
    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 overflow-visible pb-2">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-800/80">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('company.company') }}</th>
                    <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('company.email') }}</th>
                    <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('company.phone') }}</th>
                    <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('company.status') }}</th>
                    <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($companies as $company)
                    <tr class="hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-4 py-2 align-top whitespace-nowrap">
                            <div class="flex items-start gap-2.5">
                                @if ($company->logo)
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="" class="w-8 h-8 rounded-full object-cover shrink-0 ring-2 ring-gray-200 dark:ring-gray-600">
                                @else
                                    @php
                                        $logoColors = [
                                            'bg-blue-500', 'bg-emerald-500', 'bg-violet-500', 'bg-amber-500',
                                            'bg-rose-500', 'bg-cyan-500', 'bg-indigo-500', 'bg-pink-500',
                                        ];
                                        $ci = abs(crc32($company->name)) % count($logoColors);
                                        $logoBg = $logoColors[$ci];
                                    @endphp
                                    <div class="w-8 h-8 rounded-full {{ $logoBg }} flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0 pt-0.5 leading-tight">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate leading-snug">{{ $company->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5 leading-snug">{{ $company->code }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2 align-top whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $company->email ?? '—' }}
                        </td>
                        <td class="px-4 py-2 align-top whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $company->phone ?? '—' }}
                        </td>
                        <td class="px-4 py-2 align-top whitespace-nowrap">
                            @if ($company->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">{{ __('common.active') }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">{{ __('common.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 align-top whitespace-nowrap text-right">
                            @can('manage companies')
                            <div class="relative z-10 inline-block text-left pt-0.5" x-data="{ open: false }">
                                <button @click="open = !open" type="button"
                                        class="p-1 rounded-lg text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-cloak
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-[200]">
                                    <a href="{{ route('companies.edit', $company) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        {{ __('common.edit') }}
                                    </a>
                                    @if ($canCreateMore)
                                    <form method="POST" action="{{ route('companies.destroy', $company) }}" class="block"
                                          onsubmit="return confirm('{{ __('common.are_you_sure') }} {{ __('company.delete_company') }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 text-left">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('company.no_companies_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($companies->hasPages())
        <div class="mt-4">
            {{ $companies->links() }}
        </div>
    @endif
</div>
@endsection
