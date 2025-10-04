@extends('layouts.app')

@section('title', 'Daily Entries')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/entries.css') }}">
    <script src="{{ asset('js/entries.js') }}" defer></script>
    <h1>Daily Entries</h1>

    @foreach ($portions->groupBy('consumed_at') as $date => $dayPortions)
        @php
            $isToday = \Carbon\Carbon::parse($date)->isToday();
            $cardClass = $isToday ? 'card current-day' : 'card';
            $contentClass = $isToday ? 'day-content' : 'day-content collapsed';
        @endphp
        <div class="{{ $cardClass }}">
            <h2 class="day-header" onclick="toggleDay('{{ $date }}')">
                {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                <span class="toggle-icon" id="icon-{{ $date }}">{{ $isToday ? '▼' : '▶' }}</span>
            </h2>

            <div class="{{ $contentClass }}" id="content-{{ $date }}">
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
                        @foreach ($dayPortions as $portion)
                            <tr>
                                <td>{{ $portion->food->name }}</td>
                                <td>{{ $portion->grams }}g</td>
                                <td>{{ round(($portion->food->kcal_per_100g * $portion->grams) / 100, 1) }}</td>
                                <td>{{ round(($portion->food->protein_per_100g * $portion->grams) / 100, 1) }}g</td>
                                <td>{{ round(($portion->food->carbs_per_100g * $portion->grams) / 100, 1) }}g</td>
                                <td>{{ round(($portion->food->fat_per_100g * $portion->grams) / 100, 1) }}g</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div style="margin-top: 2rem;">
        {{ $portions->links() }}
    </div>

    @if ($portions->count() == 0)
        <div class="card">
            <p>No entries yet. Start tracking your nutrition from the dashboard!</p>
        </div>
    @endif
@endsection
