@extends('admin.layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="py-5">
                                <i class="fas fa-ban text-danger mb-4" style="font-size: 4rem;"></i>
                                
                                <h1 class="text-danger mb-3">Access Denied</h1>
                                
                                <p class="lead mb-4">
                                    Your IP address is not authorized to access this website.
                                </p>
                                
                                <div class="alert alert-warning">
                                    <strong>Your IP Address:</strong> 
                                    <code>{{ $ip }}</code>
                                </div>
                                
                                <p class="text-muted">
                                    If you believe this is an error, please contact the system administrator 
                                    and provide your IP address shown above.
                                </p>
                                
                                <div class="mt-4">
                                    <button onclick="history.back()" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Go Back
                                    </button>
                                </div>
                                
                                <div class="mt-4 text-muted">
                                    <small>
                                        <i class="fas fa-clock"></i>
                                        {{ now()->format('F j, Y \a\t g:i A') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto refresh every 30 seconds in case IP gets whitelisted
setTimeout(function() {
    location.reload();
}, 30000);
</script>
@endsection