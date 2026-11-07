{{-- Layout halaman setelah login. Memakai <head> dari layouts.guest. --}}
@extends('layouts.guest')

@section('content')
<div class="d-flex min-vh-100">
    {{-- offcanvas-lg: menetap di layar besar, bisa dibuka-tutup di layar kecil --}}
    <aside class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
        <div class="offcanvas-header d-lg-none">
            <span class="fw-bold" id="sidebarLabel">Arsip Kasus</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
        </div>
        <div class="d-none d-lg-block px-4 py-3 fw-bold fs-5">Arsip Kasus</div>

        <div class="offcanvas-body p-0">
            <nav class="nav flex-column w-100">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                {{-- Link # diganti saat route-nya dibuat (tahap 3, 4, 7) --}}
                <a class="nav-link" href="#">Kasus</a>
                @if (auth()->user()->role === 'admin')
                    <a class="nav-link" href="#">Riwayat Aktivitas</a>
                    <a class="nav-link" href="#">Manajemen Pengguna</a>
                @endif
            </nav>
        </div>
    </aside>

    <div class="flex-grow-1 d-flex flex-column">
        <nav class="navbar bg-white border-bottom px-3">
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <span>{{ auth()->user()->name }}</span>
                <span class="badge bg-brand text-capitalize">{{ auth()->user()->role }}</span>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
                </form>
            </div>
        </nav>

        <main class="p-4">
            @yield('page')
        </main>
    </div>
</div>
@endsection
