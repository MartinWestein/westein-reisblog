<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', __('Beheer')) — {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

        @vite(['resources/scss/admin.scss', 'resources/js/admin.js'])
        @stack('head')
    </head>
    <body>
        <div
            class="admin-shell"
            x-data="{
                collapsed: localStorage.getItem('admin-sidebar') === 'collapsed',
                mobileOpen: false,
                toggleCollapse() {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('admin-sidebar', this.collapsed ? 'collapsed' : 'expanded');
                }
            }"
            :class="{ 'is-collapsed': collapsed }">
            @include('admin._partials.sidebar')
        <div class="admin-main">
            <header class="admin-topbar">
                <button type="button" class="admin-mobile-toggle" @click="mobileOpen = true" aria-label="{{ __('Menu openen') }}">
                    <i class="bi bi-list"></i>
                </button>

                <nav class="admin-breadcrumbs" aria-label="{{ __('Kruimelpad') }}">
                    @hasSection('breadcrumbs')
                        @yield('breadcrumbs')
                    @else
                        <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
                    @endif
                </nav>

                <div class="admin-usermenu" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" class="admin-usermenu__trigger" @click="open = !open">
                        <span class="admin-usermenu__avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="admin-usermenu__name d-none d-md-inline">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down" style="font-size: 0.75rem; color: var(--admin-text-muted);"></i>
                    </button>

                    <div class="admin-usermenu__dropdown" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                        <div class="admin-usermenu__header">
                            <div class="admin-usermenu__header-name">{{ auth()->user()->name }}</div>
                            <div class="admin-usermenu__header-roles">
                                @foreach (auth()->user()->getRoleNames() as $role)
                                    <span class="admin-role-badge">{{ $role }}</span>
                                @endforeach
                            </div>
                        </div>

                        <a href="{{ route('profile.two-factor') }}" class="admin-usermenu__item">
                            <i class="bi bi-shield-lock"></i> {{ __('Tweestapsverificatie') }}
                        </a>
                        <a href="{{ url('/') }}" class="admin-usermenu__item" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> {{ __('Bekijk site') }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="admin-usermenu__item">
                                <i class="bi bi-box-arrow-right"></i> {{ __('Uitloggen') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="admin-content">
                @include('admin._partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
    </body>
</html>
