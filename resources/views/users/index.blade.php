@extends('layouts.app')
@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    .badge {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }
</style>
@endpush
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>User Management</h1>
                <div class="search-container">
                    <div class="input-group">
                        <input type="text" id="userSearch" class="form-control" 
                               placeholder="Search users by name or email...">
                        <button class="btn btn-primary" type="button" id="searchButton">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Wins</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        @php
                        $no=1;
                        @endphp
                        @foreach($users as $user)
                        <tr>
                            <td>{{$no}}</td>
                            <td>
                                    {{ $user->name }}
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>{{ $user->wonProducts->count() }}</td>
                            <td>
                                @if($user->is_blocked)
                                <span class="badge bg-danger">Blocked</span>
                                @else
                                <span class="badge bg-success">Approved</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if($user->is_blocked)
                                    <button class="btn btn-sm btn-success approve-user" 
                                            data-user-id="{{ $user->id }}">
                                        <i class="bi bi-check-circle"></i> Approve
                                    </button>
                                    @else
                                    <button class="btn btn-sm btn-warning block-user" 
                                            data-user-id="{{ $user->id }}">
                                        <i class="bi bi-slash-circle"></i> Block
                                    </button>
                                    @endif
                                    
                                </div>
                            </td>
                        </tr>
                          @php
                        $no++;
                        @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection



@push('scripts')
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.approve-user').click(function() {
        const userId = $(this).data('user-id');
        updateUserStatus(userId, false);
    });
    $('.block-user').click(function() {
        const userId = $(this).data('user-id');
        updateUserStatus(userId, true);
    });
    $('#userSearch').on('keyup', function() {
        const searchTerm = $(this).val();
        if(searchTerm.length > 2 || searchTerm.length === 0) {
            searchUsers(searchTerm);
        }
    });

    $('#searchButton').click(function() {
        searchUsers($('#userSearch').val());
    });

    function updateUserStatus(userId, isBlocked) {
        if(confirm(`Are you sure you want to ${isBlocked ? 'block' : 'approve'} this user?`)) {
            $.ajax({
                url: '/users/' + userId + '/status',
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_blocked: isBlocked ? 1 : 0  
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error updating user status');
                }
            });
        }
    }

    function searchUsers(searchTerm) {
    $.ajax({
        url: '{{ route("admin.users.search") }}',
        type: 'GET',
        data: { search: searchTerm },
        success: function(response) {
            const users = response.users;
            const currentPage = response.currentPage;
            const perPage = response.perPage;
            let rowsHtml = '';
            let startNo = (currentPage - 1) * perPage + 1;

            users.forEach(function(user, index) {
                rowsHtml += '<tr>';
                rowsHtml += `<td>${startNo + index}</td>`;
                rowsHtml += `<td>${user.name}</td>`;
                rowsHtml += `<td>${user.email}</td>`;
                rowsHtml += `<td>${formatDate(user.created_at)}</td>`;
                rowsHtml += `<td>${user.won_products_count}</td>`;
                rowsHtml += `<td>`;
                rowsHtml += user.is_blocked 
                    ? '<span class="badge bg-danger">Blocked</span>' 
                    : '<span class="badge bg-success">Approved</span>';
                rowsHtml += `</td>`;
                rowsHtml += `<td><div class="d-flex gap-2">`;

                if(user.is_blocked) {
                    rowsHtml += `<button class="btn btn-sm btn-success approve-user" data-user-id="${user.id}">
                                    <i class="bi bi-check-circle"></i> Approve
                                 </button>`;
                } else {
                    rowsHtml += `<button class="btn btn-sm btn-warning block-user" data-user-id="${user.id}">
                                    <i class="bi bi-slash-circle"></i> Block
                                 </button>`;
                }

                rowsHtml += `</div></td>`;
                rowsHtml += '</tr>';
            });

            $('#userTableBody').html(rowsHtml);
            bindActionButtons();
        },
        error: function() {
            alert('Error searching users');
        }
    });
}


function formatDate(dateStr) {
    if(!dateStr) return '';
    const d = new Date(dateStr);
    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    return d.toLocaleDateString('en-US', options);
}


function bindActionButtons() {
    $('.approve-user').off('click').on('click', function() {
        const userId = $(this).data('user-id');
        updateUserStatus(userId, false);
    });

    $('.block-user').off('click').on('click', function() {
        const userId = $(this).data('user-id');
        updateUserStatus(userId, true);
    });
}
});
</script>
@endpush