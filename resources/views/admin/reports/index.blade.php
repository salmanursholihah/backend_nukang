{{-- ============================================================ --}}
{{-- resources/views/admin/reports/index.blade.php              --}}
{{-- ============================================================ --}}
@extends('layouts.app')

@section('title', 'Reports')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Reports</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Reports</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">

                    <div class="col-md-4">
                        <div class="card card-hero">
                            <div class="card-header">
                                <div class="card-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h4>Laporan Order</h4>
                                <div class="card-description">
                                    Rekap semua order berdasarkan periode tanggal
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="hero-option">
                                    <div class="hero-option-icon bg-primary">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="hero-option-content">
                                        Filter by tanggal, status, ringkasan pendapatan
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('admin.reports.orders') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-arrow-right"></i> Buka Laporan
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-hero">
                            <div class="card-header">
                                <div class="card-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <h4>Laporan Pendapatan</h4>
                                <div class="card-description">
                                    Grafik pendapatan platform per bulan dalam setahun
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="hero-option">
                                    <div class="hero-option-icon bg-success">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="hero-option-content">
                                        Grafik bar & total revenue per bulan
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('admin.reports.revenue') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-arrow-right"></i> Buka Laporan
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-hero">
                            <div class="card-header">
                                <div class="card-icon">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <h4>Performa Tukang</h4>
                                <div class="card-description">
                                    Ranking tukang berdasarkan jumlah order selesai
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="hero-option">
                                    <div class="hero-option-icon bg-warning">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div class="hero-option-content">
                                        Rating, total job, total penghasilan tukang
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('admin.reports.tukangs') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-arrow-right"></i> Buka Laporan
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </section>
    </div>
@endsection
