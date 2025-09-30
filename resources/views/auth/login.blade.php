@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-form">
    <div class="card">
        <h1>Login</h1>
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
            </div>
            <button type="submit">Login</button>
            <p style="margin-top: 1rem;">
                Don't have an account? <a href="{{ route('register') }}">Register</a>
            </p>
        </form>
    </div>
</div>
@endsection
