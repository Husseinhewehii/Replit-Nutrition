@extends('layouts.app')

@section('title', 'Daily Entries')

@section('content')
<h1>Daily Entries</h1>

@foreach($portions->groupBy('consumed_at') as $date => $dayPortions)
<div class="card">
    <h2>{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</h2>
    
    <div class="stats">
        <div class="stat-card">
            <div class="stat-value">{{ $dailyTotals[$date]['kcal'] }}</div>
            <div class="stat-label">Calories</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $dailyTotals[$date]['protein'] }}g</div>
            <div class="stat-label">Protein</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $dailyTotals[$date]['carbs'] }}g</div>
            <div class="stat-label">Carbs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $dailyTotals[$date]['fat'] }}g</div>
            <div class="stat-label">Fat</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Food</th>
                <th>Grams</th>
                <th>Calories</th>
                <th>Protein</th>
                <th>Carbs</th>
                <th>Fat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dayPortions as $portion)
            <tr>
                <td>{{ $portion->food->name }}</td>
                <td>{{ $portion->grams }}g</td>
                <td>{{ round($portion->food->kcal_per_100g * $portion->grams / 100, 1) }}</td>
                <td>{{ round($portion->food->protein_per_100g * $portion->grams / 100, 1) }}g</td>
                <td>{{ round($portion->food->carbs_per_100g * $portion->grams / 100, 1) }}g</td>
                <td>{{ round($portion->food->fat_per_100g * $portion->grams / 100, 1) }}g</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach

<div style="margin-top: 2rem;">
    {{ $portions->links() }}
</div>

@if($portions->count() == 0)
<div class="card">
    <p>No entries yet. Start tracking your nutrition from the dashboard!</p>
</div>
@endif
@endsection
