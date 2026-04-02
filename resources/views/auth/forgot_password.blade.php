@extends('layouts.app')

@section('main')
    <div class="container mt-5">
        <div class="card col-md-4 mx-auto">
            <div class="card-body">
                <h4>Forgot Password</h4>

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <input type="email" name="email" class="form-control mb-3" placeholder="Masukkan email">

                    <button class="btn btn-warning w-100">Kirim Link Reset</button>
                </form>
            </div>
        </div>
    </div>
@endsection
