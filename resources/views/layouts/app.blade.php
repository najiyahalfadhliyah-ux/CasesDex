{{-- Layout halaman setelah login (Figma: CasesDex / Dashboard). Memakai <head> dari layouts.guest.
     Halaman mengisi @section('title'), @section('heading') (lokasi di bilah atas) dan @section('page'). --}}
@extends('layouts.guest')

@php
    $isAdmin = auth()->user()->role === 'admin';
    // [route, pola route aktif, ikon, label, khusus admin]
    $menu = [
        ['dashboard', ['dashboard'], 'grid-1x2', 'Dashboard', false],
        ['cases.index', ['cases.*', 'evidence.*', 'suspects.*', 'timeline.*'], 'journal-text', 'Daftar kasus', false],
        ['archives.index', ['archives.*'], 'archive', 'Arsip kasus', false],
        ['reports.index', ['reports.*'], 'bar-chart', 'Statistik & laporan', false],
        ['audit-logs.index', ['audit-logs.*'], 'clock-history', 'Riwayat aktivitas', true],
        ['users.index', ['users.*'], 'people', 'Manajemen pengguna', true],
    ];
@endphp

@section('content')
<a href="#konten" class="skip-link">Lewati ke konten</a>

<div class="d-flex min-vh-100">
    {{-- offcanvas-lg: menetap di layar besar, jadi menu geser di bawah 992px --}}
    <aside class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
        <div class="offcanvas-header">
            <a href="{{ route('dashboard') }}" class="brand" id="sidebarLabel">
                <x-logo type="simbol" height="44" />
                <span>
                    <span class="brand-name">CasesDex</span>
                    <span class="brand-sub">Arsip digital</span>
                </span>
            </a>
            <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
        </div>

        <div class="offcanvas-body">
            <nav class="nav flex-column" aria-label="Menu utama">
                <div class="nav-section">Ruang kerja</div>
                @foreach ($menu as [$route, $patterns, $icon, $label, $adminOnly])
                    @continue($adminOnly && ! $isAdmin)
                    @php($active = request()->routeIs(...$patterns))
                    <a @class(['nav-link', 'active' => $active]) href="{{ route($route) }}" @if ($active) aria-current="page" @endif>
                        <i class="bi bi-{{ $icon }}" aria-hidden="true"></i> {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="sidebar-note">
                <p class="sidebar-note-title">Akses terkendali</p>
                <p class="mb-0">Data fiktif untuk demonstrasi. Hanya pengguna berwenang.</p>
            </div>

            <nav class="nav flex-column sidebar-footer" aria-label="Akun">
                <a @class(['nav-link', 'active' => request()->routeIs('account.*')]) href="{{ route('account.edit') }}"
                   @if (request()->routeIs('account.*')) aria-current="page" @endif>
                    <i class="bi bi-gear" aria-hidden="true"></i> Pengaturan akun
                </a>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar
                    </button>
                </form>
            </nav>
        </div>
    </aside>

    <div class="app-main">
        <header class="app-navbar">
            <button class="btn btn-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>

            <div class="app-location">
                <p class="page-heading">@yield('heading')</p>
                <p class="app-date d-none d-sm-block">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            </div>

            <button type="button" class="btn btn-theme" data-theme-toggle aria-pressed="false" aria-label="Mode gelap">
                <i class="bi bi-moon-stars" aria-hidden="true"></i><span class="d-none d-md-inline">Mode gelap</span>
            </button>

            <span class="role-chip d-none d-sm-inline-flex">Peran: <strong class="text-capitalize">{{ auth()->user()->role }}</strong></span>

            <div class="app-user d-none d-md-block">
                <p class="app-user-name">{{ auth()->user()->name }}</p>
                <p class="app-user-sub">Sesi terautentikasi</p>
            </div>
        </header>

        <main id="konten" class="app-content" tabindex="-1">
            @yield('page')
        </main>

        <footer class="app-footer no-print">
            <span>CasesDex · Sistem arsip kasus & barang bukti</span>
            <span>Internal · Akses sesuai kewenangan</span>
        </footer>
    </div>
</div>

<x-confirm-modal />

<script>
    // Tombol Terang / Gelap: data-bs-theme sudah dipasang di <head> (layouts.guest); di sini hanya ganti dan simpan
    (function () {
        var root = document.documentElement;
        var toggle = document.querySelector('[data-theme-toggle]');
        function sync() { toggle.setAttribute('aria-pressed', root.getAttribute('data-bs-theme') === 'dark'); }
        sync();
        toggle.addEventListener('click', function () {
            var next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-bs-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
            sync();
        });
    })();
</script>
@endsection
