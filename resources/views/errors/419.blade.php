@extends('errors.layout')

@section('title', 'Sesi Berakhir - CasesDex')
@section('icon', 'bi-clock-history')
@section('code', '419')
@section('heading', 'Sesi Anda telah berakhir')
@section('message', 'Halaman terlalu lama dibiarkan sehingga formulir tidak bisa dikirim. Masuk kembali, lalu ulangi isian Anda.')
@section('action')
    <a href="{{ route('login') }}" class="btn btn-primary">
        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Masuk kembali
    </a>
@endsection
