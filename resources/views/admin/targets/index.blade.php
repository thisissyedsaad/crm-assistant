@extends('admin.layouts.app')

@section('title', 'Managing Targets | CSD Assistant')

@push('links')
<style>
    /* Table styling */
    .table input.form-control-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border: 1px solid #dee2e6;
    }
    
    .monthly-target {
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .gap-2 {
        gap: 0.5rem;
    }

    /* Alert styling */
    .alert-info {
        background-color: #e7f3ff;
        border-color: #bee5eb;
        color: #0c5460;
    }

    /* Table responsive */
    .table-responsive {
        overflow-x: auto;
    }

    /* Input focus */
    .form-control-sm:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Separator styling */
    .align-self-center {
        color: #6c757d;
        font-weight: 500;
    }

    /* Card styling */
    .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    /* Button styling */
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    .btn-success {
        min-width: 80px;
    }
</style>
@endpush

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="data-table-area">
            <div class="container-fluid">
                <div class="row g-4">
                    <!-- Page Header -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body card-breadcrumb">
                                <div class="page-title-box d-flex align-items-center justify-content-between">
                                    <h4 class="mb-0">Managing Targets</h4>
                                    <!-- <button type="button" class="btn btn-primary" id="saveAllBtn">
                                        <i class="bx bx-save"></i> Save All Changes
                                    </button> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Table Card -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <!-- Targets Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="targetsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 15%;">Team</th>
                                                <th style="width: 35%;">Daily Target<small class="text-muted" style="color:green !important;"> (Total / New / Existing)</small></th>
                                                <th style="width: 15%;"># of Working Days</th>
                                                <th style="width: 15%;">Monthly Target</th>
                                                <th style="width: 20%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($staffUsers as $user)

                                            <tr data-user-id="{{ $user->id }}">
                                                <td>
                                                    <strong>{{ $user->name }}</strong>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                                        <input 
                                                            type="number" 
                                                            class="form-control form-control-sm daily-target-total" 
                                                            placeholder="Total"
                                                            value="{{ $user->dailyTarget->daily_target_total ?? 0 }}"
                                                            min="0"
                                                            style="width: 80px;"
                                                        >
                                                        <span class="align-self-center">/</span>
                                                        <input 
                                                            type="number" 
                                                            class="form-control form-control-sm daily-target-new" 
                                                            placeholder="New"
                                                            value="{{ $user->dailyTarget->daily_target_new ?? 0 }}"
                                                            min="0"
                                                            style="width: 80px;"
                                                        >
                                                        <span class="align-self-center">/</span>
                                                        <input 
                                                            type="number" 
                                                            class="form-control form-control-sm daily-target-existing" 
                                                            placeholder="Existing"
                                                            value="{{ $user->dailyTarget->daily_target_existing ?? 0 }}"
                                                            min="0"
                                                            style="width: 80px;"
                                                        >
                                                    </div>
                                                </td>
                                                <td>
                                                    <input 
                                                        type="number" 
                                                        class="form-control form-control-sm working-days" 
                                                        value="{{ $user->dailyTarget->working_days ?? 0 }}"
                                                        min="0"
                                                        style="width: 100px;"
                                                    >
                                                </td>
                                                <td>
                                                    <strong class="monthly-target text-primary">
                                                        {{ $user->dailyTarget ? $user->dailyTarget->monthly_target : 0 }}
                                                    </strong>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-success save-row-btn" data-user-id="{{ $user->id }}">
                                                        <i class="bx bx-save"></i> Save
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    <i class="bx bx-user-x" style="font-size: 2rem;"></i>
                                                    <p class="mb-0 mt-2">No staff members found</p>
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
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Auto-calculate monthly target on input change
    function calculateMonthlyTarget(row) {
        const total = parseInt(row.find('.daily-target-total').val()) || 0;
        const workingDays = parseInt(row.find('.working-days').val()) || 0;
        const monthlyTarget = total * workingDays;
        
        row.find('.monthly-target').text(monthlyTarget);
    }

    // Listen to input changes
    $('#targetsTable tbody').on('input', '.daily-target-total, .working-days', function() {
        const row = $(this).closest('tr');
        calculateMonthlyTarget(row);
    });

    // Save individual row
    $(document).on('click', '.save-row-btn', function() {
        const button = $(this);
        const row = button.closest('tr');
        const userId = row.data('user-id');
        
        if (!userId) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'User ID not found',
            });
            return;
        }

        // Disable button and show loading
        button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');

        const data = {
            user_id: userId,
            daily_target_total: parseInt(row.find('.daily-target-total').val()) || 0,
            daily_target_new: parseInt(row.find('.daily-target-new').val()) || 0,
            daily_target_existing: parseInt(row.find('.daily-target-existing').val()) || 0,
            working_days: parseInt(row.find('.working-days').val()) || 0,
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route("admin.targets.update") }}',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    // Update monthly target
                    row.find('.monthly-target').text(response.monthly_target);
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Target updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to save target. Please try again.',
                });
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="bx bx-save"></i> Save');
            }
        });
    });

    // Save all targets (top button functionality)
    $('#saveAllBtn').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');

        const targets = [];
        
        $('#targetsTable tbody tr').each(function() {
            const row = $(this);
            const userId = row.data('user-id');
            
            if (userId) {
                targets.push({
                    user_id: userId,
                    daily_target_total: parseInt(row.find('.daily-target-total').val()) || 0,
                    daily_target_new: parseInt(row.find('.daily-target-new').val()) || 0,
                    daily_target_existing: parseInt(row.find('.daily-target-existing').val()) || 0,
                    working_days: parseInt(row.find('.working-days').val()) || 0,
                });
            }
        });

        if (targets.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data',
                text: 'No staff members to save',
            });
            button.prop('disabled', false).html('<i class="bx bx-save"></i> Save All Changes');
            return;
        }

        $.ajax({
            url: '{{ route("admin.targets.save-all") }}',
            method: 'POST',
            data: {
                targets: targets,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to save targets. Please try again.',
                });
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="bx bx-save"></i> Save All Changes');
            }
        });
    });
});
</script>
@endpush