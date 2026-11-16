{{-- Kerangka halaman error 404/419/500 (gaya sama dengan 403). Pesan sengaja tetap, bukan dari exception,
     supaya nama model, query, atau path tidak bocor. Tanpa query database agar aman dipakai saat error 500. --}}
@extends('layouts.guest')

@section('content')
<main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card card-narrow p-4 p-sm-5 text-center w-100">
        <i class="bi @yield('icon') fs-1 mb-2" style="color:var(--accent)" aria-hidden="true"></i>
        <p class="error-code mb-2">@yield('code')</p>
        <h1 class="h4 fw-bold mb-2">@yield('heading')</h1>
        <p class="text-muted mb-4">@yield('message')</p>
        @hasSection('action')
            @yield('action')
        @else
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke beranda
            </a>
        @endif
    </div>
</main>
@endsection
