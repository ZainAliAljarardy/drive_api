@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row" style="min-height: 100vh; background: linear-gradient(135deg, #1f1c2c, #928dab); padding: 20px;">
    <div class="col-12 mb-4">
        <div class="d-flex justify-content-between align-items-center text-white">
            <h2 class="mb-0"><i class="bi bi-shield-check"></i> Admin Dashboard</h2>
            <form action="{{ route('admin.refresh') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </form>
        </div>
    </div>

    <!-- User Management -->
    <div class="col-12 mb-4">
        <div class="card shadow-lg border-0" style="border-radius: 12px;">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-people"></i> User Management</h4>
            </div>
            <div class="card-body bg-dark text-light" style="border-radius: 0 0 12px 12px;">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle">
                        <thead class="table-light text-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Current Plan</th>
                                <th>Storage Usage</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user['name'] }}</td>
                                    <td>{{ $user['email'] }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ number_format($user['storage_limit'], 0) }}MB
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                @php
                                                    $usagePercent = $user['storage_limit'] > 0 
                                                        ? ($user['used_storage'] / $user['storage_limit']) * 100 
                                                        : 0;
                                                @endphp
                                                <div class="progress-bar {{ $usagePercent > 90 ? 'bg-danger' : ($usagePercent > 70 ? 'bg-warning' : 'bg-success') }}" 
                                                     style="width: {{ min($usagePercent, 100) }}%">
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ number_format($user['used_storage'], 2) }}MB
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user['is_blocked'])
                                            <span class="badge bg-danger">Blocked</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.toggle-block', $user['id']) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $user['is_blocked'] ? 'btn-success' : 'btn-warning' }}">
                                                <i class="bi bi-{{ $user['is_blocked'] ? 'unlock' : 'lock' }}"></i>
                                                {{ $user['is_blocked'] ? 'Unblock' : 'Block' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Storage Request Management -->
    <div class="col-12">
        <div class="card shadow-lg border-0" style="border-radius: 12px;">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="bi bi-inbox"></i> Storage Upgrade Requests</h4>
            </div>
            <div class="card-body bg-dark text-light" style="border-radius: 0 0 12px 12px;">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle">
                        <thead class="table-light text-dark">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Requested Plan</th>
                                <th>Status</th>
                                <th>Requested At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingRequests as $request)
                                <tr>
                                    <td>{{ $request['user']['name'] ?? 'N/A' }}</td>
                                    <td>{{ $request['user']['email'] ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $request['requested_plan'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ ucfirst($request['status']) }}</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($request['created_at'])->format('M d, Y H:i') }}</td>
                                    <td>
                                        <form action="{{ route('admin.process-request', $request['id']) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i> Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.process-request', $request['id']) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this request?')">
                                                <i class="bi bi-x-circle"></i> Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="bi bi-inbox"></i> No pending requests
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
