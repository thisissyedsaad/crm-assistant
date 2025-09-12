@extends('admin.layouts.app')

@section('title', 'IP Whitelist Management | CSD Assistant')

@push('links')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/buttons.bootstrap5.min.css') }}">
    <style>
        .ip-address { 
            font-family: 'Courier New', monospace; 
            background: #f8f9fa; 
            padding: 2px 6px; 
            border-radius: 3px; 
        }
        .bulk-actions { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            padding: 0.75rem; 
            margin-bottom: 1rem; 
        }

        .dataTables_wrapper {
            width: 100% !important;
        }

        table.dataTable {
            width: 100% !important;
        }
    </style>
@endpush

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="data-table-area">
            <div class="container-fluid">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body card-breadcrumb">
                                <div class="page-title-box d-flex align-items-center justify-content-between">
                                    <h4 class="mb-0">IP Whitelist Management</h4>
                                    <div class="page-title-right">
                                        <button type="button" id="getCurrentIp" class="btn btn-info btn-sm me-2">
                                            <i class="fas fa-globe"></i> Get My IP
                                        </button>
                                        <a href="{{ route('admin.ip-whitelist.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i> Add New IP
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Success Message -->
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <!-- Bulk Actions -->
                                <div class="bulk-actions" id="bulkActions" style="display: none;">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-success btn-sm" id="bulkActivate">
                                            <i class="fas fa-check"></i> Activate Selected
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" id="bulkDeactivate">
                                            <i class="fas fa-times"></i> Deactivate Selected
                                        </button>
                                    </div>
                                </div>

                                <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area">
                                    <thead>
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th>IP Address</th>
                                            <th>Label</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ipWhitelists ?? [] as $ip)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input ip-checkbox" value="{{ $ip->id }}">
                                            </td>
                                            <td>
                                                <code class="ip-address">{{ $ip->ip_address }}</code>
                                            </td>
                                            <td>{{ $ip->label ?: '-' }}</td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle" type="checkbox" 
                                                           data-id="{{ $ip->id }}" 
                                                           {{ $ip->is_active ? 'checked' : '' }}>
                                                    <span class="badge bg-{{ $ip->is_active ? 'success' : 'danger' }} ms-2">
                                                        {{ $ip->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>{{ $ip->creator->name ?? 'Unknown' }}</td>
                                            <td>{{ $ip->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <a href="{{ route('admin.ip-whitelist.edit', $ip) }}" 
                                                   class="btn btn-sm btn-primary">Edit</a>
                                                <form method="POST" action="{{ route('admin.ip-whitelist.destroy', $ip) }}" 
                                                      class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty

                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Current IP Modal -->
<div class="modal fade" id="currentIpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Your Current IP Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-globe fa-3x text-info mb-3"></i>
                <p class="mb-2">Your current IP address is:</p>
                <h4 id="currentIpAddress" class="text-primary"></h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="copyIpBtn">Copy IP</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/admin/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/jszip.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/dataTables-custom.js') }}"></script>

<script>
$(document).ready(function() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable().destroy();
    }
    
    // Initialize DataTable
    $('#datatable').DataTable({
        responsive: true, // We handle responsiveness with CSS
        scrollX: true,
        scrollCollapse: false,
        autoWidth: true,
        columnDefs: [
            { targets: 0, className: 'text-wrap' }, // Checkbox
            { targets: 1, className: 'text-wrap' }, // IP Address  
            { targets: 2, className: 'text-nowrap' }, // Label
            { targets: 3, className: 'text-nowrap' }, // Status
            { targets: 4, className: 'text-nowrap' }, // Created By
            { targets: 5, className: 'text-nowrap' }, // Created At
            { targets: 6, className: 'text-nowrap', orderable: false } // Actions
        ],
        language: {
            search: "Search IP Addresses:",
            lengthMenu: "Show _MENU_ IP addresses per page",
            info: "Showing _START_ to _END_ of _TOTAL_ IP addresses",
            emptyTable: "No IP addresses found."
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[5, 'desc']], // Sort by Created At descending
        initComplete: function() {
            $('.dataTables_wrapper').css('width', '100%');
        },
        drawCallback: function() {
            $('.dataTables_wrapper').css('width', '100%');
        }
    });

    // Select all functionality
    $('#selectAll').change(function() {
        $('.ip-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkActions();
    });

    $(document).on('change', '.ip-checkbox', function() {
        toggleBulkActions();
    });

    function toggleBulkActions() {
        const checkedCount = $('.ip-checkbox:checked').length;
        $('#bulkActions').toggle(checkedCount > 0);
    }

    // Status toggle
    $(document).on('change', '.status-toggle', function() {
        const ipId = $(this).data('id');
        const toggle = $(this);
        
        $.post(`{{ url('admin/ip-whitelist') }}/${ipId}/toggle-status`, {
            _token: '{{ csrf_token() }}'
        }).done(function(response) {
            if (response.success) {
                const badge = toggle.siblings('.badge');
                if (response.is_active) {
                    badge.removeClass('bg-danger').addClass('bg-success').text('Active');
                } else {
                    badge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                }
                showToast('success', response.message);
            }
        }).fail(function() {
            // Revert toggle if failed
            toggle.prop('checked', !toggle.prop('checked'));
            showToast('error', 'Failed to update status');
        });
    });

    // Get current IP
    $('#getCurrentIp').click(function() {
        $.get('{{ route("admin.ip-whitelist.current-ip") }}')
        .done(function(response) {
            $('#currentIpAddress').text(response.ip);
            $('#currentIpModal').modal('show');
        })
        .fail(function() {
            showToast('error', 'Failed to get current IP');
        });
    });

    // Copy IP to clipboard
    $('#copyIpBtn').click(function() {
        const ip = $('#currentIpAddress').text();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(ip).then(function() {
                showToast('success', 'IP copied to clipboard!');
                $('#currentIpModal').modal('hide');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = ip;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast('success', 'IP copied to clipboard!');
            $('#currentIpModal').modal('hide');
        }
    });

    // Bulk actions
    $('#bulkActivate').click(function() { bulkStatusUpdate(1); });
    $('#bulkDeactivate').click(function() { bulkStatusUpdate(0); });

    function bulkStatusUpdate(status) {
        const selectedIds = $('.ip-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            showToast('warning', 'Please select at least one IP address');
            return;
        }

        $.post('{{ route("admin.ip-whitelist.bulk-status") }}', {
            _token: '{{ csrf_token() }}',
            ids: selectedIds,
            status: status
        }).done(function(response) {
            if (response.success) {
                showToast('success', response.message);
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }
        }).fail(function() {
            showToast('error', 'Failed to update IP addresses');
        });
    }

    function showToast(type, message) {
        // Remove existing toasts
        $('.toast-notification').remove();
        
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 'alert-danger';
        
        const toast = $(`<div class="alert ${alertClass} alert-dismissible fade show toast-notification position-fixed" 
                          style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">${message}
                          <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
        
        $('body').append(toast);
        
        setTimeout(() => {
            toast.fadeOut(500, function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>
@endpush