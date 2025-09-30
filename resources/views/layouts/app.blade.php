<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nutrition Tracker')</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    @auth
    <nav>
        <div class="container">
            <div>
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('foods') }}">Foods</a>
                <a href="{{ route('entries.index') }}">Entries</a>
            </div>
            <div>
                <span style="margin-right: 1rem;">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" style="background: transparent; border: 1px solid white; padding: 0.5rem 1rem;">Logout</button>
                </form>
            </div>
        </div>
    </nav>
    @endauth

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="list-style: none;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="/js/app.js" defer></script>
</body>
</html>
