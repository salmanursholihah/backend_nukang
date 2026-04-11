@extends('layouts.auth')

@section('title', 'Login')

@section('main')
    <div class="card card-primary">
        <div class="card-header">
            <h4>Login</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>

                <div class="form-group">
                    <div class="d-block">
                        <label>Password</label>
                    </div>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Login
                    </button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('password.request') }}">Forgot Password?</a><br>
                <a href="{{ route('register') }}">Register</a>
            </div>
        </div>
    </div>
@endsection
