@extends('layouts.app')

@section('title', 'Edit Food')

@section('content')
<h1>Edit Food</h1>

<div class="card">
    <form action="{{ route('foods.update', $food) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Food Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $food->name) }}" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $food->slug) }}" pattern="[a-z0-9_]+" placeholder="e.g., chicken_breast" required>
            <small style="color: #666;">Lowercase letters, numbers, and underscores only.</small>
        </div>
        <div class="form-group">
            <label for="kcal_per_100g">Calories per 100g</label>
            <input type="number" step="0.01" id="kcal_per_100g" name="kcal_per_100g" value="{{ old('kcal_per_100g', $food->kcal_per_100g) }}" required>
        </div>
        <div class="form-group">
            <label for="protein_per_100g">Protein per 100g (g)</label>
            <input type="number" step="0.01" id="protein_per_100g" name="protein_per_100g" value="{{ old('protein_per_100g', $food->protein_per_100g) }}" required>
        </div>
        <div class="form-group">
            <label for="carbs_per_100g">Carbs per 100g (g)</label>
            <input type="number" step="0.01" id="carbs_per_100g" name="carbs_per_100g" value="{{ old('carbs_per_100g', $food->carbs_per_100g) }}" required>
        </div>
        <div class="form-group">
            <label for="fat_per_100g">Fat per 100g (g)</label>
            <input type="number" step="0.01" id="fat_per_100g" name="fat_per_100g" value="{{ old('fat_per_100g', $food->fat_per_100g) }}" required>
        </div>
        <button type="submit">Update Food</button>
        <a href="{{ route('foods.index') }}" class="btn btn-secondary" style="margin-left: 0.5rem;">Cancel</a>
    </form>
</div>
@endsection
