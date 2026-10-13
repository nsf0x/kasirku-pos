<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — KasirKu' : 'KasirKu' }}</title>

    {{-- Google Fonts: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-bg dark:bg-bg-dark text-content-primary dark:text-content-primary-dark antialiased">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">

        {{-- Logo --}}
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 mb-8">
            <div class="flex items-center justify-center w-10 h-10 rounded-input bg-primary text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
            </div>
            <span class="font-bold text-primary" style="font-size: 26px; line-height: 1;">KasirKu</span>
        </a>

        {{-- Content --}}
        <div class="w-full" style="max-width: 440px;">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        <p class="mt-8 text-content-secondary" style="font-size: 13px;">
            &copy; {{ date('Y') }} KasirKu — POS untuk UMKM Indonesia
        </p>

    </div>

    @livewireScripts
</body>
</html>
