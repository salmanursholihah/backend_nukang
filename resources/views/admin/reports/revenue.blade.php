@extends('layouts.app')

@section('title', 'Laporan Pendapatan')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Laporan Pendapatan</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                    <div class="breadcrumb-item active">Revenue</div>
                </div>
            </div>

            <div class="section-body">

                {{-- Filter Tahun --}}
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.revenue') }}">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label>Tahun</label>
                                    <select name="year" class="form-control">
                                        @for ($y = now()->year; $y >= now()->year - 4; $y--)
                                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                                {{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Tampilkan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Total Tahun Ini --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Total Pendapatan {{ $year }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                <div class="text-muted">Total Revenue {{ $year }}</div>
                                <div class="h2 font-weight-bold text-success mt-1">
                                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <canvas id="revenueChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Per Bulan --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Detail Per Bulan</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th class="text-right">Pendapatan (Platform Fee)</th>
                                        <th>Grafik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $bulan = [
                                            '',
                                            'Januari',
                                            'Februari',
                                            'Maret',
                                            'April',
                                            'Mei',
                                            'Juni',
                                            'Juli',
                                            'Agustus',
                                            'September',
                                            'Oktober',
                                            'November',
                                            'Desember',
                                        ];
                                        $maxRevenue = max(array_values($monthlyRevenue) ?: [1]);
                                    @endphp
                                    @for ($m = 1; $m <= 12; $m++)
                                        @php $rev = $monthlyRevenue[$m] ?? 0; @endphp
                                        <tr>
                                            <td>{{ $bulan[$m] }} {{ $year }}</td>
                                            <td
                                                class="text-right font-weight-bold {{ $rev > 0 ? 'text-success' : 'text-muted' }}">
                                                Rp {{ number_format($rev, 0, ',', '.') }}
                                            </td>
                                            <td style="width:40%">
                                                <div class="progress" style="height:20px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $maxRevenue > 0 ? ($rev / $maxRevenue) * 100 : 0 }}%">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-right text-success">Rp
                                            {{ number_format($totalRevenue, 0, ',', '.') }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        const monthlyData = @json($monthlyRevenue);
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const values = [];
        for (let m = 1; m <= 12; m++) {
            values.push(monthlyData[m] || 0);
        }

        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Platform Fee (Rp)',
                    data: values,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val => 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                        }
                    }
                }
            }
        });
    </script>
@endpush
