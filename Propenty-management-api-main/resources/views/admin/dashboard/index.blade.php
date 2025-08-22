@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content-header', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <!-- Properties Stats -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['properties']['total'] }}</h3>
                    <p>Total Properties</p>
                </div>
                <div class="icon">
                    <i class="fas fa-home"></i>
                </div>
                <a href="{{ route('admin.properties.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->

        <!-- Active Properties -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['properties']['active'] }}</h3>
                    <p>Active Properties</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('admin.properties.index', ['filter[status]' => 'active']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->

        <!-- Pending Approvals -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['properties']['pending'] }}</h3>
                    <p>Pending Approvals</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('admin.moderation.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->

        <!-- New Leads -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['leads']['new'] }}</h3>
                    <p>New Leads</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <a href="{{ route('admin.leads.index', ['filter[status]' => 'new']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->

    <div class="row">
        <!-- Properties Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Properties Added (Last 30 Days)</h3>
                </div>
                <div class="card-body">
                    <canvas id="propertiesChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Properties by City -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Properties by City</h3>
                </div>
                <div class="card-body">
                    <canvas id="cityChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lead Conversion Funnel -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lead Status Distribution</h3>
                </div>
                <div class="card-body">
                    <canvas id="leadsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Properties by Type -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Properties by Type</h3>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Properties -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Properties</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Owner</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProperties as $property)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($property['image'])
                                                <img src="{{ $property['image'] }}" alt="Property" class="img-thumbnail mr-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <strong>{{ Str::limit($property['title'], 30) }}</strong><br>
                                                <small class="text-muted">{{ $property['location'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $property['status'] === 'active' ? 'success' : ($property['status'] === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($property['status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $property['price'] }}</td>
                                    <td>{{ $property['owner'] }}</td>
                                    <td>{{ $property['created_at'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.properties.index') }}" class="btn btn-primary btn-sm">View All Properties</a>
                </div>
            </div>
        </div>

        <!-- Recent Leads -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Leads</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Lead</th>
                                    <th>Status</th>
                                    <th>Source</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLeads as $lead)
                                <tr>
                                    <td>
                                        <strong>{{ $lead['name'] }}</strong><br>
                                        <small class="text-muted">{{ $lead['email'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $lead['status'] === 'new' ? 'primary' : ($lead['status'] === 'converted' ? 'success' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $lead['status'])) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $lead['source'])) }}</td>
                                    <td>{{ $lead['assigned_to'] ?? 'Unassigned' }}</td>
                                    <td>{{ $lead['created_at'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.leads.index') }}" class="btn btn-primary btn-sm">View All Leads</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-tag"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Featured</span>
                    <span class="info-box-number">{{ $stats['properties']['featured'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">For Sale</span>
                    <span class="info-box-number">{{ $stats['properties']['for_sale'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-key"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">For Rent</span>
                    <span class="info-box-number">{{ $stats['properties']['for_rent'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Conversion Rate</span>
                    <span class="info-box-number">{{ $stats['leads']['conversion_rate'] }}%</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Properties Chart
    const propertiesData = @json($chartData['properties_by_day']);
    const ctx1 = document.getElementById('propertiesChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: propertiesData.map(item => item.date),
            datasets: [{
                label: 'Properties Added',
                data: propertiesData.map(item => item.count),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true
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

    // Properties by City Chart
    const cityData = @json($chartData['properties_by_city']);
    const ctx2 = document.getElementById('cityChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: cityData.map(item => item.city),
            datasets: [{
                label: 'Properties',
                data: cityData.map(item => item.count),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
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

    // Leads Status Chart
    const leadsData = @json($chartData['leads_by_status']);
    const ctx3 = document.getElementById('leadsChart').getContext('2d');
    new Chart(ctx3, {
        type: 'doughnut',
        data: {
            labels: leadsData.map(item => item.status),
            datasets: [{
                data: leadsData.map(item => item.count),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Properties by Type Chart
    const typeData = @json($chartData['properties_by_type']);
    const ctx4 = document.getElementById('typeChart').getContext('2d');
    new Chart(ctx4, {
        type: 'pie',
        data: {
            labels: typeData.map(item => item.type),
            datasets: [{
                data: typeData.map(item => item.count),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#36A2EB'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush