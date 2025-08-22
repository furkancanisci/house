<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Lead Info</th>
                <th>Contact</th>
                <th>Source</th>
                <th>Status</th>
                <th>Property</th>
                <th>Budget</th>
                <th>Assigned To</th>
                <th>Quality</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            <tr class="{{ $lead->status == 'new' ? 'bg-light' : '' }}">
                <td>{{ $lead->id }}</td>
                <td>
                    <div>
                        <strong>{{ $lead->name }}</strong>
                        @if($lead->status == 'new')
                            <span class="badge badge-warning ml-1">NEW</span>
                        @endif
                        @if($lead->preferred_location)
                            <br><small class="text-muted">📍 {{ $lead->preferred_location }}</small>
                        @endif
                        @if($lead->bedrooms || $lead->bathrooms)
                            <br><small class="text-muted">
                                @if($lead->bedrooms) {{ $lead->bedrooms }} bed @endif
                                @if($lead->bathrooms) {{ $lead->bathrooms }} bath @endif
                            </small>
                        @endif
                    </div>
                </td>
                <td>
                    <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                    @if($lead->phone)
                        <br><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                    @endif
                </td>
                <td>
                    @php
                        $sourceColors = [
                            'website' => 'primary',
                            'contact_form' => 'info',
                            'listing_inquiry' => 'success',
                            'phone' => 'warning',
                            'walk_in' => 'secondary'
                        ];
                    @endphp
                    <span class="badge badge-{{ $sourceColors[$lead->source] ?? 'secondary' }}">
                        {{ ucfirst(str_replace('_', ' ', $lead->source)) }}
                    </span>
                </td>
                <td>
                    @can('edit leads')
                    <select class="form-control form-control-sm lead-status" data-lead="{{ $lead->id }}">
                        <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option>
                        <option value="in_progress" {{ $lead->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="unqualified" {{ $lead->status == 'unqualified' ? 'selected' : '' }}>Unqualified</option>
                        <option value="closed" {{ $lead->status == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                    @else
                    @php
                        $statusColors = [
                            'new' => 'warning',
                            'in_progress' => 'info',
                            'qualified' => 'success',
                            'unqualified' => 'secondary',
                            'closed' => 'dark'
                        ];
                    @endphp
                    <span class="badge badge-{{ $statusColors[$lead->status] ?? 'secondary' }}">
                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                    </span>
                    @endcan
                </td>
                <td>
                    @if($lead->property)
                        <a href="{{ route('admin.properties.show', $lead->property) }}">
                            {{ Str::limit($lead->property->title, 20) }}
                        </a>
                    @elseif($lead->property_type)
                        <span class="text-muted">{{ $lead->property_type }}</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($lead->budget_min || $lead->budget_max)
                        @if($lead->budget_min && $lead->budget_max)
                            ${{ number_format($lead->budget_min) }} - ${{ number_format($lead->budget_max) }}
                        @elseif($lead->budget_min)
                            Min: ${{ number_format($lead->budget_min) }}
                        @else
                            Max: ${{ number_format($lead->budget_max) }}
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($lead->assignedTo)
                        <span class="badge badge-info">{{ $lead->assignedTo->full_name }}</span>
                    @else
                        @can('assign leads')
                        <button class="btn btn-sm btn-outline-primary assign-lead" data-lead="{{ $lead->id }}">
                            <i class="fas fa-user-plus"></i> Assign
                        </button>
                        @else
                        <span class="text-muted">Unassigned</span>
                        @endcan
                    @endif
                </td>
                <td>
                    @if($lead->quality_score)
                        <div class="text-center">
                            <span class="badge badge-{{ $lead->quality_score >= 7 ? 'success' : ($lead->quality_score >= 4 ? 'warning' : 'danger') }}">
                                {{ $lead->quality_score }}/10
                            </span>
                        </div>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    {{ $lead->created_at->format('M d, Y') }}
                    <br><small class="text-muted">{{ $lead->created_at->diffForHumans() }}</small>
                    @if($lead->last_contacted_at)
                        <br><small class="text-info">
                            <i class="fas fa-phone"></i> {{ $lead->last_contacted_at->diffForHumans() }}
                        </small>
                    @endif
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.leads.show', $lead) }}" 
                           class="btn btn-info btn-sm" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        
                        @can('edit leads')
                        <a href="{{ route('admin.leads.edit', $lead) }}" 
                           class="btn btn-warning btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        <button type="button" class="btn btn-secondary btn-sm add-note" 
                                data-lead="{{ $lead->id }}" title="Add Note">
                            <i class="fas fa-comment"></i>
                        </button>
                        @endcan

                        @can('delete leads')
                        <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm delete-lead" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">No leads found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($leads->hasPages())
<div class="d-flex justify-content-center">
    {{ $leads->appends(request()->query())->links() }}
</div>
@endif