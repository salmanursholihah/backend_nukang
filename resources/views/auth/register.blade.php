@extends('layouts.app')

@section('main')
    <div class="container mt-5">
        <div class="card col-md-4 mx-auto">
            <div class="card-body">
                <h4>Register</h4>

                <form method="POST" action="{{ url('/register') }}">
                    @csrf

                    <input type="text" name="name" class="form-control mb-2" placeholder="Nama">
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email">
                    <input type="password" name="password" class="form-control mb-2" placeholder="Password">

                    <button class="btn btn-success w-100">Register</button>
                </form>
            </div>
        </div>
    </div>
@endsection
