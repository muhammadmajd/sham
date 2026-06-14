<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/icon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .admin-layout-shell {
                display: grid;
                grid-template-columns: 260px minmax(0, 1fr);
                gap: 0;
                min-height: calc(100vh - 4rem);
            }

            .admin-layout-main {
                min-width: 0;
            }

            .admin-layout-main .max-w-7xl {
                max-width: none;
            }

            .admin-side-menu {
                position: sticky;
                top: 0;
                min-height: calc(100vh - 4rem);
                padding: 18px 14px;
                border-right: 1px solid #e5e7eb;
                background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
                box-sizing: border-box;
            }

            .admin-side-brand {
                display: flex;
                align-items: center;
                min-height: 58px;
                padding: 12px 12px;
                margin-bottom: 12px;
                border-radius: 12px;
                background: #111827;
                color: #fff;
            }

            .admin-side-brand span {
                display: block;
                color: #cbd5e1;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
            }

            .admin-side-brand strong {
                display: block;
                margin-top: 2px;
                font-size: 18px;
                line-height: 1.2;
            }

            .admin-side-nav {
                display: grid;
                gap: 7px;
            }

            .admin-side-link {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                min-height: 44px;
                padding: 9px 10px;
                border-radius: 10px;
                color: #334155;
                text-decoration: none;
                transition: background .15s ease, color .15s ease, transform .15s ease, box-shadow .15s ease;
            }

            .admin-side-link:hover {
                background: #eef2f7;
                color: #0f172a;
                transform: translateX(2px);
            }

            .admin-side-link.is-active {
                background: color-mix(in srgb, var(--item-color) 13%, #fff);
                color: #0f172a;
                box-shadow: inset 3px 0 0 var(--item-color);
            }

            .admin-side-link-main {
                display: inline-flex;
                align-items: center;
                gap: 9px;
                min-width: 0;
                font-size: 14px;
                font-weight: 800;
            }

            .admin-side-dot {
                width: 9px;
                height: 9px;
                border-radius: 999px;
                background: var(--item-color);
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--item-color) 16%, transparent);
            }

            .admin-side-count {
                min-width: 34px;
                padding: 3px 8px;
                border-radius: 999px;
                background: #fff;
                color: #475569;
                border: 1px solid #e2e8f0;
                font-size: 12px;
                font-weight: 800;
                text-align: center;
            }

            @media (max-width: 900px) {
                .admin-layout-shell {
                    grid-template-columns: 1fr;
                }

                .admin-side-menu {
                    position: static;
                    min-height: 0;
                    border-right: 0;
                    border-bottom: 1px solid #e5e7eb;
                }

                .admin-side-nav {
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        @php
            $showAdminSideMenu = Auth::check()
                && Auth::user()->is_admin
                && (request()->routeIs('dashboard') || request()->routeIs('admin.*'));
        @endphp

        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @if ($showAdminSideMenu)
                <div class="admin-layout-shell">
                    @include('layouts.admin-side-menu')

                    <div class="admin-layout-main">
                        @isset($header)
                            <header class="bg-white shadow">
                                <div class="max-w-none py-6 px-4 sm:px-6 lg:px-8">
                                    {{ $header }}
                                </div>
                            </header>
                        @endisset

                        <main>
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            @else
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main>
                    {{ $slot }}
                </main>
            @endif
        </div>
    </body>
</html>
