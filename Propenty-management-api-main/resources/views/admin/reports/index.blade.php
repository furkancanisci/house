@extends('admin.layouts.app')

@section('title', 'Reports & Analytics')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Reports</li>
</ol>
@endsection

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_properties'] }}</h3>
                <p>Total Properties</p>
            </div>
            <div class="icon">
                <i class="fas fa-home"></i>
            </div>
            <a href="{{ route('admin.reports.properties') }}" class="small-box-footer">
                View Details <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['active_properties'] }}</h3>
                <p>Active Properties</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.reports.properties', ['status' => 'active']) }}" class="small-box-footer">
                View Details <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['total_users'] }}</h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('admin.reports.users') }}" class="small-box-footer">
                View Details <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['total_leads'] }}</h3>
                <p>Total Leads</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <a href="{{ route('admin.leads.index') }}" class="small-box-footer">
                View Details <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Monthly Properties Chart -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Property Listings (Last 12 Months)</h3>
            </div>
            <div class="card-body">
                <canvas id="monthlyPropertiesChart" 
                        style="height: 300px;"
                        data-labels="{{ json_encode($monthlyProperties->pluck('month')) }}"
                        data-values="{{ json_encode($monthlyProperties->pluck('count')) }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Leads by Status -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Leads by Status</h3>
            </div>
            <div class="card-body">
                <canvas id="leadsStatusChart" 
                        style="height: 300px;"
                        data-labels="{{ json_encode($leadsByStatus->pluck('status')) }}"
                        data-values="{{ json_encode($leadsByStatus->pluck('count')) }}"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Properties by Type -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Properties by Type</h3>
            </div>
            <div class="card-body">
                <canvas id="propertiesTypeChart" 
                        style="height: 250px;"
                        data-labels="{{ json_encode($propertiesByType->pluck('property_type')) }}"
                        data-values="{{ json_encode($propertiesByType->pluck('count')) }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Reports -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Reports</h3>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @can('view reports')
                    <a href="{{ route('admin.reports.properties') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-home"></i> Property Reports
                        <span class="float-right text-muted">Detailed property analysis</span>
                    </a>
                    <a href="{{ route('admin.reports.users') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-users"></i> User Reports
                        <span class="float-right text-muted">User activity and statistics</span>
                    </a>
                    <a href="{{ route('admin.reports.revenue') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-dollar-sign"></i> Revenue Reports
                        <span class="float-right text-muted">Financial performance</span>
                    </a>
                    @endcan
                    
                    @can('export reports')
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">Export Options</h6>
                    <a href="{{ route('admin.reports.export', 'properties') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-download"></i> Export Properties
                        <span class="float-right text-muted">CSV format</span>
                    </a>
                    <a href="{{ route('admin.reports.export', 'users') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-download"></i> Export Users
                        <span class="float-right text-muted">CSV format</span>
                    </a>
                    <a href="{{ route('admin.reports.export', 'leads') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-download"></i> Export Leads
                        <span class="float-right text-muted">CSV format</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // Monthly Properties Chart
    var monthlyPropertiesCanvas = document.getElementById('monthlyPropertiesChart');
    var monthlyPropertiesCtx = monthlyPropertiesCanvas.getContext('2d');
    var monthlyLabels = JSON.parse(monthlyPropertiesCanvas.getAttribute('data-labels'));
    var monthlyData = JSON.parse(monthlyPropertiesCanvas.getAttribute('data-values'));
    
    var monthlyPropertiesChart = new Chart(monthlyPropertiesCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Properties Listed',
                data: monthlyData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Leads Status Chart
    var leadsStatusCanvas = document.getElementById('leadsStatusChart');
    var leadsStatusCtx = leadsStatusCanvas.getContext('2d');
    var leadsLabels = JSON.parse(leadsStatusCanvas.getAttribute('data-labels'));
    var leadsData = JSON.parse(leadsStatusCanvas.getAttribute('data-values'));
    
    var leadsStatusChart = new Chart(leadsStatusCtx, {
        type: 'doughnut',
        data: {
            labels: leadsLabels,
            datasets: [{
                data: leadsData,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Properties Type Chart
    var propertiesTypeCanvas = document.getElementById('propertiesTypeChart');
    var propertiesTypeCtx = propertiesTypeCanvas.getContext('2d');
    var typeLabels = JSON.parse(propertiesTypeCanvas.getAttribute('data-labels'));
    var typeData = JSON.parse(propertiesTypeCanvas.getAttribute('data-values'));
    
    var propertiesTypeChart = new Chart(propertiesTypeCtx, {
        type: 'bar',
        data: {
            labels: typeLabels,
            datasets: [{
                label: 'Number of Properties',
                data: typeData,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush