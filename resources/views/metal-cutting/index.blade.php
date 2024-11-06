@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 bg-Sky-800">
    <h1 class="text-3xl font-bold my-4  text-Sky-300">Découpe de Métal - Génération de Motifs</h1>

    <form action="{{ route('metal-cutting.process') }}" method="POST" enctype="multipart/form-data" class=" shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf

        <!-- Taille de la pièce (X) -->
        <div class="mb-4">
            <label for="part_size_x" class="block text-Sky-100 text-sm font-bold mb-2">Taille de la pièce (X) en mm :</label>
            <input type="number" name="part_size_x" id="part_size_x" class="shadow appearance-none border rounded w-full py-2 px-3 text-Sky-100 bg-Sky-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('part_size_x', session('part_size_x')) }}" required>
            @error('part_size_x')
                <p class="text-Red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <!-- Taille de la pièce (Y) -->
        <div class="mb-4">
            <label for="part_size_y" class="block text-Sky-100 text-sm font-bold mb-2">Taille de la pièce (Y) en mm :</label>
            <input type="number" name="part_size_y" id="part_size_y" class="shadow appearance-none border rounded w-full py-2 px-3 text-Sky-100 bg-Sky-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('part_size_y', session('part_size_y')) }}" required>
            @error('part_size_y')
                <p class="text-Red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nombre d'outils -->
        <div class="mb-4">
            <label for="number_of_tools" class="block text-Sky-100 text-sm font-bold mb-2">Nombre d'outils :</label>
            <input type="number" name="number_of_tools" id="number_of_tools" class="shadow appearance-none border rounded w-full py-2 px-3 text-Sky-100 bg-Sky-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('number_of_tools', session('number_of_tools')) }}" required>
            @error('number_of_tools')
                <p class="text-Red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <!-- Diamètre minimum de l'outil -->
        <div class="mb-4">
            <label for="min_tool_diameter" class="block text-Sky-100 text-sm font-bold mb-2">Diamètre minimum de l'outil (mm) :</label>
            <input type="number" name="min_tool_diameter" id="min_tool_diameter" class="shadow appearance-none border rounded w-full py-2 px-3 text-Sky-100 bg-Sky-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('min_tool_diameter', session('min_tool_diameter')) }}" required>
            @error('min_tool_diameter')
                <p class="text-Red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <!-- Diamètre maximum de l'outil -->
        <div class="mb-4">
            <label for="max_tool_diameter" class="block text-Sky-100 text-sm font-bold mb-2">Diamètre maximum de l'outil (mm) :</label>
            <input type="number" name="max_tool_diameter" id="max_tool_diameter" class="shadow appearance-none border rounded w-full py-2 px-3 text-Sky-100 bg-Sky-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('max_tool_diameter', session('max_tool_diameter'))  }}" required>
            @error('max_tool_diameter')
                <p class="text-Red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <!-- Upload de l'image -->
        <div class="mb-4">
            <label for="image" class="block text-Sky-100 text-sm font-bold mb-2">Télécharger l'image à découper :</label>
            <input type="file" name="image" id="image" class="w-full py-2 px-3 text-Sky-100 bg-Sky-700 border rounded leading-tight focus:outline-none focus:shadow-outline" required>
            @error('image')
                <p class="text-Red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        
        <div class="mb-4">
            <label for="shape" class="block text-Sky-100 text-sm font-bold mb-2">Type de forme</label>
            <select name="shape" id="shape" class="w-full py-2 px-3 text-Sky-100 bg-Sky-700 border rounded leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="circle">Rond</option>
                <option value="square">Carré</option>
                <option value="rectangle">Rectangle</option>
            </select>
            @error('shape')
                <p class="text-Red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <!-- Bouton de soumission -->
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-Sky-500 hover:bg-Sky-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Générer le motif</button>
        </div>
    </form>

    <div class="container mx-auto py-8">
        @if(session('success'))
            <div class="bg-Green-100 text-Green-700 p-4 rounded mb-4">
                {{ session('success') }}Yes !
            </div>
        @endif

        @if(session('imageUrl'))
            <div class="mt-4">
                <h2 class="text-xl font-bold mb-2">Image générée :</h2>
                <img src="{{ session('imageUrl') }}" alt="Generated pattern" class="border border-gray-300 shadow-lg">
            </div>
        @endif
    </div>
</div>
@endsection
