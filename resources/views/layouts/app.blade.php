<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ sidebarOpen: false }"
      x-cloak
      class="{{ Cookie::get('darkMode') === '1' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — KasirKu' : 'KasirKu' }}</title>

    {{-- Google Fonts: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-bg dark:bg-bg-dark text-content-primary dark:text-content-primary-dark antialiased"
      x-data
      x-init="
        // Restore dark mode from cookie on hydration
        if (document.cookie.includes('darkMode=1')) {
            document.documentElement.classList.add('dark');
        }
      ">

    {{-- ================================================================
         TOAST NOTIFICATION SYSTEM (Alpine store 'toast')
         Usage from Livewire: $dispatch('toast', ['type'=>'success','message'=>'...'])
         ================================================================ --}}
    <div
        x-data
        x-on:toast.window="$store.toast.add($event.detail)"
        class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"
        style="max-width: 360px;"
        aria-live="polite"
        aria-atomic="false">
        <template x-for="toast in $store.toast.items" :key="toast.id">
            <div
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                class="pointer-events-auto flex items-start gap-3 rounded-input px-4 py-3 shadow-panel border w-full"
                :class="{
                    'bg-success-50 border-success-500/30 text-success-500': toast.type === 'success',
                    'bg-danger-50 border-danger-500/30 text-danger-500':   toast.type === 'error',
                    'bg-warning-50 border-warning-500/30 text-warning-500': toast.type === 'warning',
                    'bg-primary-50 border-primary-500/30 text-primary-600': toast.type === 'info',
                }">
                {{-- Icon --}}
                <span class="mt-0.5 shrink-0">
                    {{-- success --}}
                    <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{-- error --}}
                    <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    {{-- warning --}}
                    <svg x-show="toast.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    {{-- info --}}
                    <svg x-show="toast.type === 'info'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                    </svg>
                </span>

                <span class="flex-1 text-sm font-medium" x-text="toast.message"></span>

                <button
                    @click="$store.toast.remove(toast.id)"
                    class="shrink-0 opacity-60 hover:opacity-100 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    {{-- ================================================================
         MOBILE SIDEBAR OVERLAY
         ================================================================ --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-20 bg-black/40 lg:hidden"
        x-cloak>
    </div>

    {{-- ================================================================
         SIDEBAR
         ================================================================ --}}
    <aside
        id="sidebar"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-30 flex flex-col bg-surface dark:bg-surface-dark border-r border-border dark:border-border-dark transition-transform duration-200 ease-in-out lg:translate-x-0"
        style="width: 240px;">

        {{-- Logo --}}
        <div class="flex items-center gap-2.5 px-5 py-5 border-b border-border dark:border-border-dark shrink-0">
            {{-- POS icon --}}
            <div class="flex items-center justify-center w-9 h-9 rounded-input bg-primary text-white shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
            </div>
            <span class="font-bold text-primary" style="font-size: 22px; line-height: 1;">KasirKu</span>
        </div>

        {{-- Nav items --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

            @if(auth()->user()->isOwner())
            {{-- Beranda (owner only) --}}
            <a href="{{ route('dashboard') }}"
               wire:navigate
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                <span>Beranda</span>
            </a>
            @endif

            {{-- Kasir (semua) --}}
            <a href="{{ route('cashier.index') }}"
               wire:navigate
               class="nav-item {{ request()->routeIs('cashier.index') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
                <span>Kasir</span>
            </a>

            {{-- Riwayat (semua) --}}
            <a href="{{ route('sales.index') }}"
               wire:navigate
               class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                </svg>
                <span>Riwayat</span>
            </a>

            @if(auth()->user()->isOwner())
            {{-- Produk (owner only) --}}
            <a href="{{ route('products.index') }}"
               wire:navigate
               class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                </svg>
                <span>Produk</span>
            </a>

            {{-- Kategori (owner only) --}}
            <a href="{{ route('categories.index') }}"
               wire:navigate
               class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>
                </svg>
                <span>Kategori</span>
            </a>
            @endif

            {{-- Stok (semua) --}}
            <a href="{{ route('stock.index') }}"
               wire:navigate
               class="nav-item {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                </svg>
                <span>Stok</span>
            </a>

            @if(auth()->user()->isOwner())
            {{-- Laporan (owner only) --}}
            <a href="{{ route('reports.index') }}"
               wire:navigate
               class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                <span>Laporan</span>
            </a>

            {{-- Pengguna (owner only) --}}
            <a href="{{ route('users.index') }}"
               wire:navigate
               class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
                <span>Pengguna</span>
            </a>
            @endif

        </nav>

        {{-- Sidebar footer: dark mode + user info --}}
        <div class="shrink-0 border-t border-border dark:border-border-dark px-3 py-3 space-y-1">

            {{-- Dark mode toggle --}}
            <button
                @click="
                    const isDark = document.documentElement.classList.toggle('dark');
                    document.cookie = 'darkMode=' + (isDark ? '1' : '0') + ';path=/;max-age=31536000';
                "
                class="nav-item w-full text-left">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                </svg>
                <span class="dark:hidden">Mode gelap</span>
                <span class="hidden dark:inline">Mode terang</span>
            </button>

            {{-- User info --}}
            <div class="flex items-center gap-3 px-3 py-2">
                {{-- Avatar: colored circle with first letter --}}
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white font-semibold shrink-0"
                     style="font-size: 13px;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-content-primary dark:text-content-primary-dark truncate" style="font-size: 13px;">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-content-secondary dark:text-content-secondary-dark truncate capitalize" style="font-size: 13px;">
                        {{ auth()->user()->role === 'owner' ? 'Pemilik' : 'Kasir' }}
                    </p>
                </div>
                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-content-secondary hover:text-danger transition-colors"
                            title="Keluar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                    </button>
                </form>
            </div>

        </div>
    </aside>

    {{-- ================================================================
         MAIN CONTENT WRAPPER
         ================================================================ --}}
    <div class="lg:pl-[240px] flex flex-col min-h-screen">

        {{-- Top bar (mobile only) --}}
        <header class="lg:hidden sticky top-0 z-10 flex items-center gap-3 bg-surface dark:bg-surface-dark border-b border-border dark:border-border-dark px-4 py-3">
            <button
                @click="sidebarOpen = true"
                class="p-1.5 rounded-input text-content-secondary hover:text-content-primary hover:bg-border/40 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <span class="font-bold text-primary" style="font-size: 17px;">KasirKu</span>
            <div class="flex-1"></div>
            {{-- Optional header slot on mobile --}}
            @if(isset($header))
            <div class="flex items-center gap-2">{{ $header }}</div>
            @endif
        </header>

        {{-- Desktop page header --}}
        @if(isset($header))
        <div class="hidden lg:block px-6 pt-6 pb-0">
            {{ $header }}
        </div>
        @endif

        {{-- Main --}}
        <main class="flex-1 px-4 py-4 lg:px-6 lg:py-6 pb-24 lg:pb-6">
            {{ $slot }}
        </main>
    </div>

    {{-- ================================================================
         BOTTOM NAVIGATION (mobile only)
         ================================================================ --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-20 bg-surface dark:bg-surface-dark border-t border-border dark:border-border-dark flex items-stretch">

        @if(auth()->user()->isOwner())
        {{-- Beranda --}}
        <a href="{{ route('dashboard') }}"
           wire:navigate
           class="flex-1 flex flex-col items-center justify-center gap-1 py-2 min-h-touch
                  {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-content-secondary' }}
                  hover:text-primary transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
            </svg>
            <span style="font-size: 11px; font-weight: 500;">Beranda</span>
        </a>
        @endif

        {{-- Kasir --}}
        <a href="{{ route('cashier.index') }}"
           wire:navigate
           class="flex-1 flex flex-col items-center justify-center gap-1 py-2 min-h-touch
                  {{ request()->routeIs('cashier.index') ? 'text-primary' : 'text-content-secondary' }}
                  hover:text-primary transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
            </svg>
            <span style="font-size: 11px; font-weight: 500;">Kasir</span>
        </a>

        {{-- Riwayat --}}
        <a href="{{ route('sales.index') }}"
           wire:navigate
           class="flex-1 flex flex-col items-center justify-center gap-1 py-2 min-h-touch
                  {{ request()->routeIs('sales.*') ? 'text-primary' : 'text-content-secondary' }}
                  hover:text-primary transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
            <span style="font-size: 11px; font-weight: 500;">Riwayat</span>
        </a>

        {{-- Stok --}}
        <a href="{{ route('stock.index') }}"
           wire:navigate
           class="flex-1 flex flex-col items-center justify-center gap-1 py-2 min-h-touch
                  {{ request()->routeIs('stock.*') ? 'text-primary' : 'text-content-secondary' }}
                  hover:text-primary transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
            <span style="font-size: 11px; font-weight: 500;">Stok</span>
        </a>

    </nav>

    @livewireScripts

    <script>
        // ================================================================
        // Alpine.js Toast Store
        // ================================================================
        document.addEventListener('alpine:init', () => {
            Alpine.store('toast', {
                items: [],
                _counter: 0,

                add({ type = 'info', message = '', duration = 4000 }) {
                    const id = ++this._counter;
                    this.items.push({ id, type, message, visible: true });

                    if (duration > 0) {
                        setTimeout(() => this.remove(id), duration);
                    }
                },

                remove(id) {
                    const item = this.items.find(t => t.id === id);
                    if (item) {
                        item.visible = false;
                        setTimeout(() => {
                            this.items = this.items.filter(t => t.id !== id);
                        }, 300);
                    }
                },
            });
        });
    </script>

</body>
</html>
