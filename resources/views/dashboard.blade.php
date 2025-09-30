@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1>Dashboard</h1>

<div class="stats">
    <div class="stat-card">
        <div class="stat-value">{{ $todayTotals['kcal'] }}</div>
        <div class="stat-label">Calories</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $todayTotals['protein'] }}g</div>
        <div class="stat-label">Protein</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $todayTotals['carbs'] }}g</div>
        <div class="stat-label">Carbs</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $todayTotals['fat'] }}g</div>
        <div class="stat-label">Fat</div>
    </div>
</div>

<div class="card">
    <h2>Quick Add Portion</h2>
    <p>Enter food slug and grams: e.g., <code>chicken_breast-150</code></p>
    <form action="{{ route('portions.quick-add') }}" method="POST" class="quick-add-form">
        @csrf
        <input type="text" name="slug_grams" placeholder="chicken_breast-150" required>
        <button type="submit">Add</button>
    </form>
</div>

<h2>Foods</h2>
<div class="food-grid">
    @foreach($foods as $food)
    <div class="food-card">
        <div class="food-name">{{ $food->name }}</div>
        <div class="food-slug">Slug: {{ $food->slug }}</div>
        <div class="food-macros">
            <span>{{ $food->kcal_per_100g }} kcal</span>
            <span>P: {{ $food->protein_per_100g }}g</span>
            <span>C: {{ $food->carbs_per_100g }}g</span>
            <span>F: {{ $food->fat_per_100g }}g</span>
        </div>
        <form action="{{ route('portions.add') }}" method="POST" style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
            @csrf
            <input type="hidden" name="food_id" value="{{ $food->id }}">
            <input type="number" step="0.01" name="grams" placeholder="Grams" required style="flex: 1;">
            <button type="submit">Add</button>
        </form>
    </div>
    @endforeach
</div>
@endsection
