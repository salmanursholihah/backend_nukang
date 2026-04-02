@extends('layouts.app')

@section('title', 'Tukang Management')

@section('main')

    <div class="section-header">
        <h1>Tukang Management</h1>
    </div>

    <div class="section-body">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Partner Tukang List</h4>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Skill</th>
                                <th>Experience</th>
                                <th>Rating</th>
                                <th>Verified</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($tukangs as $index => $tukang)
                                <tr>
                                    <td>{{ $tukangs->firstItem() + $index }}</td>
                                    <td>{{ $tukang->user->name ?? '-' }}</td>
                                    <td>{{ $tukang->skill }}</td>
                                    <td>{{ $tukang->experience }}</td>
                                    <td>{{ $tukang->rating }}</td>

                                    <td>
                                        @if ($tukang->is_verified)
                                            <span class="badge badge-success">Verified</span>
                                        @else
                                            <span class="badge badge-danger">Pending</span>
                                        @endif
                                    </td>

                                    <td>

                                        {{-- Edit --}}
                                        <button class="btn btn-warning btn-sm" data-toggle="modal"
                                            data-target="#editModal{{ $tukang->id }}">
                                            Edit
                                        </button>

                                        {{-- Verify --}}
                                        <a href="{{ route('tukang.verify', $tukang->id) }}" class="btn btn-success btn-sm">
                                            Verify
                                        </a>

                                        {{-- Reject --}}
                                        <a href="{{ route('tukang.reject', $tukang->id) }}"
                                            class="btn btn-secondary btn-sm">
                                            Reject
                                        </a>

                                        {{-- Delete --}}
                                        <form action="{{ route('tukang.destroy', $tukang->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete partner?')">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editModal{{ $tukang->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">

                                            <form action="{{ route('tukang.update', $tukang->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <h5>Edit Tukang</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">

                                                    <div class="form-group">
                                                        <label>Skill</label>
                                                        <input type="text" name="skill" class="form-control"
                                                            value="{{ $tukang->skill }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Experience</label>
                                                        <input type="text" name="experience" class="form-control"
                                                            value="{{ $tukang->experience }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Rating</label>
                                                        <input type="number" step="0.1" name="rating"
                                                            class="form-control" value="{{ $tukang->rating }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Verification</label>
                                                        <select name="is_verified" class="form-control">
                                                            <option value="1"
                                                                {{ $tukang->is_verified ? 'selected' : '' }}>Verified
                                                            </option>
                                                            <option value="0"
                                                                {{ !$tukang->is_verified ? 'selected' : '' }}>Pending
                                                            </option>
                                                        </select>
                                                    </div>

                                                </div>

                                                <div class="modal-footer">
                                                    <button class="btn btn-primary">Update</button>
                                                </div>

                                            </form>

                                        </div>
                                    </div>
                                </div>

                            @empty

                                <tr>
                                    <td colspan="7" class="text-center">No Data</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $tukangs->links() }}
                </div>

            </div>

        </div>

    </div>

@endsection
