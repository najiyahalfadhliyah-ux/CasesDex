@extends('layouts.guest')

@section('title', 'Login - Arsip Kasus')

@section('content')
<main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card card-narrow p-4 w-100">
        <h1 class="h4 fw-bold text-center mb-4">Masuk</h1>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="login" class="form-label">Email atau Username</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}"
                       class="form-control @error('login') is-invalid @enderror" required autofocus>
                @error('login')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Kata Sandi</label>
                <input id="password" name="password" type="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-accent w-100">Masuk</button>
        </form>
    </div>
</main>
@endsection
