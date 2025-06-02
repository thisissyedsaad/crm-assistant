@extends('admin.layouts.app')

@section('title', 'Users List | CRM Assistant')

@push('links')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select.dataTables.min.css') }}">
@endpush

@section('content')
<!-- Main Content Area -->
<div class="main-content introduction-farm">
        <div class="content-wraper-area">
                <div class="data-table-area">
                <div class="container-fluid">
                        <div class="row g-4">
                        <div class="col-12">
                                <div class="card">
                                <div class="card-body card-breadcrumb">
                                        <div class="page-title-box d-flex align-items-center justify-content-between">
                                                <h4 class="mb-0">Users List</h4>
                                                <div class="page-title-right">
                                                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Add New</a>
                                                </div>
                                        </div>
                                </div>
                                </div>
                        </div>

                        <div class="col-12">
                                <div class="card">
                                <div class="card-body">
                                        <!-- <div class="card-title d-flex justify-content-between align-items-center">
                                        <h4>Users Table</h4>
                                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Add New</a>
                                        </div> -->

                                        <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area">
                                        <thead>
                                                <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                                </tr>
                                        </thead>

                                        <tbody>
                                                @foreach ($users as $user)
                                                <tr>
                                                        <td>{{ $user->name }}</td>
                                                        <td>{{ $user->email }}</td>
                                                        <td>{{ ucfirst($user->role) }}</td>
                                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                                        <td>
                                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>

                                                        @if(auth()->id() != $user->id)
                                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                                        </form>
                                                        @else
                                                        <button class="btn btn-danger btn-sm" disabled title="You cannot delete yourself">Delete</button>
                                                        @endif
                                                        </td>
                                                </tr>
                                                @endforeach
                                        </tbody>

                                        </table>
                                </div>
                                </div>
                        </div>

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
@endpush