@extends('layouts.app')

@section('title', 'Service Management')

@section('main')
<section class="section">

    <div class="section-header">
        <h1>Service Management</h1>
    </div>

    <div class="section-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Service List</h4>

                <div class="card-header-action">
                    <a href="{{ route('services.create') }}" class="btn btn-primary">
                        + Add Service
                    </a>
                </div>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($services as $index => $service)
                                <tr>
                                    <td>{{ $services->firstItem() + $index }}</td>
                                    <td>{{ $service->category->name ?? '-' }}</td>
                                    <td>{{ $service->name }}</td>
                                    <td>{{ $service->description }}</td>
                                    <td>Rp {{ number_format($service->price) }}</td>
                                    <td>

                                        <a href="{{ route('services.edit', $service->id) }}"
                                           class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('services.destroy', $service->id) }}"
                                              method="POST"
                                              style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete service?')">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No Data</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="mt-3">
                    {{ $services->links() }}
                </div>

            </div>

        </div>

    </div>

</section>
@endsection
