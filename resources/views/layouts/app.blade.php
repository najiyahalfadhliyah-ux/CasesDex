{{-- Layout halaman setelah login. Memakai <head> dari layouts.guest. --}}
@extends('layouts.guest')

@section('content')
<a href="#konten" class="skip-link">Lewati ke konten</a>

<div class="d-flex min-vh-100">
    {{-- offcanvas-lg: menetap di layar besar, bisa dibuka-tutup di layar kecil --}}
    <aside class="offcanvas-lg offcanvas-start sidebar d-flex flex-column" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
        <div class="offcanvas-header px-4 py-3">
            <a href="{{ route('dashboard') }}" class="brand fs-5" id="sidebarLabel">
                <i class="bi bi-archive-fill" aria-hidden="true"></i> Arsip Kasus
            </a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
        </div>

        <div class="offcanvas-body p-0 d-flex flex-column flex-grow-1">
            <nav class="nav flex-column w-100 flex-grow-1" aria-label="Menu utama">
                <div class="nav-section">Utama</div>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"
                   @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                    <i class="bi bi-grid-1x2" aria-hidden="true"></i> Dashboard
                </a>
                {{-- Link # diganti saat route-nya dibuat (tahap 3, 4, 7) --}}
                <a class="nav-link" href="#"><i class="bi bi-folder2-open" aria-hidden="true"></i> Kasus</a>

                @if (auth()->user()->role === 'admin')
                    <div class="nav-section">Administrasi</div>
                    <a class="nav-link" href="#"><i class="bi bi-journal-text" aria-hidden="true"></i> Riwayat Aktivitas</a>
                    <a class="nav-link" href="#"><i class="bi bi-people" aria-hidden="true"></i> Manajemen Pengguna</a>
                @endif
            </nav>

            <div class="user-box">
                <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                <small class="text-capitalize">{{ auth()->user()->role }}</small>
            </div>
        </div>
    </aside>

    <div class="flex-grow-1 d-flex flex-column" style="min-width:0">
        <header class="navbar app-navbar px-3 px-lg-4">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                <p class="page-heading">@yield('heading')</p>
            </div>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                <span class="badge bg-brand text-capitalize">{{ auth()->user()->role }}</span>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm" aria-label="Logout">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i><span class="d-none d-sm-inline">Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <main id="konten" class="p-3 p-lg-4" tabindex="-1">
            @yield('page')
        </main>
    </div>
</div>
@endsection
