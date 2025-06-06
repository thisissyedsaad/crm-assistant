@extends('admin.layouts.app')

@section('title', 'Edit User | CSD Assistant')

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body card-breadcrumb">
                                <div class="page-title-box d-flex align-items-center justify-content-between">
                                        <h4 class="mb-0">Edit User </h4>
                                        <div class="page-title-right">
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm">All Users</a>
                                        </div>
                                </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <!-- <div class="card-title d-flex justify-content-between align-items-center">
                            <h4>Edit User Form</h4>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm">All Users</a>
                            </div> -->

                            <!-- <h4 class="card-title">Edit User Form</h4> -->

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Note the method PUT for update -->
                            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" id="name" 
                                        value="{{ old('name', $user->name) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $user->email) }}" readonly disabled>
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select name="role" id="role" class="form-select" required>
                                        <option value="">-- Select Role --</option>
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>Staff</option>
                                    </select>
                                </div>

                                <div class="mb-3 position-relative">
                                    <label for="password" class="form-label">Password <small>(Leave blank if you don't want to change)</small></label>
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control" id="password" autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                            <i class="bi bi-eye" id="icon-password"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3 position-relative">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirmation">
                                            <i class="bi bi-eye" id="icon-password_confirmation"></i>
                                        </button>
                                    </div>
                                </div>                                

                                <button type="submit" class="btn btn-primary w-md">Update User</button>
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
    // Password eye toggle script same as create form
    document.querySelectorAll('.toggle-password').forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = document.getElementById('icon-' + targetId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
</script>
@endpush
