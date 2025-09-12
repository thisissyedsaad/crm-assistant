@extends('admin.layouts.app')

@section('title', 'Add New IP Address | CSD Assistant')

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="data-table-area">
            <div class="container-fluid">
                <div class="row ">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body card-breadcrumb">
                                <h4 class="mb-0">Add New IP Address</h4>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <form method="POST" action="{{ route('admin.ip-whitelist.store') }}">
                                @csrf
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('ip_address') is-invalid @enderror" 
                                               id="ip_address" name="ip_address" value="{{ old('ip_address') }}" 
                                               placeholder="e.g., 192.168.1.1">
                                        @error('ip_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Enter a valid IPv4 or IPv6 address.
                                            <button type="button" class="btn btn-sm btn-link p-0" id="useCurrentIp">
                                                Use my current IP
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="label" class="form-label">Label</label>
                                        <input type="text" class="form-control" id="label" name="label" 
                                               value="{{ old('label') }}" placeholder="e.g., Office Network">
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  placeholder="Optional description">{{ old('description') }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active">
                                            <label class="form-check-label" for="is_active">
                                                Active (Allow access from this IP)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save IP Address
                                    </button>
                                    <a href="{{ route('admin.ip-whitelist.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to List
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#useCurrentIp').click(function() {
        $.get('{{ route("admin.ip-whitelist.current-ip") }}')
        .done(function(response) {
            $('#ip_address').val(response.ip);
        });
    });
});
</script>
@endpush