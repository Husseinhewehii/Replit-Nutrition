@extends('layouts.app')

@section('title', 'Foods')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Foods</h1>
    <a href="{{ route('foods.create') }}" class="btn">Create Food</a>
</div>

<div class="card">
    <h2>Your Foods</h2>
    @if($userFoods->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Calories</th>
                <th>Protein</th>
                <th>Carbs</th>
                <th>Fat</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($userFoods as $food)
            <tr>
                <td>{{ $food->name }}</td>
                <td>{{ $food->slug }}</td>
                <td>{{ $food->kcal_per_100g }}</td>
                <td>{{ $food->protein_per_100g }}g</td>
                <td>{{ $food->carbs_per_100g }}g</td>
                <td>{{ $food->fat_per_100g }}g</td>
                <td>
                    <a href="{{ route('foods.edit', $food) }}" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                    <form action="{{ route('foods.destroy', $food) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem;">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>You haven't created any foods yet.</p>
    @endif
</div>

<div class="card">
    <h2>Global Foods</h2>
    @if($globalFoods->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Calories</th>
                <th>Protein</th>
                <th>Carbs</th>
                <th>Fat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($globalFoods as $food)
            <tr>
                <td>{{ $food->name }}</td>
                <td>{{ $food->slug }}</td>
                <td>{{ $food->kcal_per_100g }}</td>
                <td>{{ $food->protein_per_100g }}g</td>
                <td>{{ $food->carbs_per_100g }}g</td>
                <td>{{ $food->fat_per_100g }}g</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No global foods available.</p>
    @endif
</div>
@endsection
