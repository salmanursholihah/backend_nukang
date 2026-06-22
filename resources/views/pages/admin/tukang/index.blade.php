@extends('layouts.app')

@section('title', 'Tukang Management')

@section('main')

<section class="section">

    <div class="section-header">
        <h1>Tukang Management</h1>
    </div>

    <div class="section-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Partner Tukang List</h4>

                <div class="card-header-action">
                    <a href="{{ route('tukangs.create') }}" class="btn btn-primary">
                        + Add Tukang
                    </a>
                </div>
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
                                        @if($tukang->is_verified)
                                            <span class="badge badge-success">Verified</span>
                                        @else
                                            <span class="badge badge-danger">Pending</span>
                                        @endif
                                    </td>

                                    <td>

                                        <a href="{{ route('tukangs.edit', $tukang->id) }}"
                                           class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        <a href="{{ route('tukangs.verify', $tukang->id) }}"
                                           class="btn btn-success btn-sm">
                                            Verify
                                        </a>

                                        <a href="{{ route('tukangs.reject', $tukang->id) }}"
                                           class="btn btn-secondary btn-sm">
                                            Reject
                                        </a>

                                        <form action="{{ route('tukangs.destroy', $tukang->id) }}"
                                              method="POST"
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
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No Data</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">
                    {{ $tukangs->links() }}
                </div>

            </div>

        </div>

    </div>

</section>

@endsection
