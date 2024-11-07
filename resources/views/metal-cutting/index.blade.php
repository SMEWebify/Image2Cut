@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 bg-slate-800">
    <h1 class="text-3xl font-bold my-4  text-slate-300">Découpe de Métal - Génération de Motifs</h1>

    <form action="{{ route('metal-cutting.process') }}" method="POST" enctype="multipart/form-data" class="bg-slate-800 shadow-lg rounded-lg p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Taille de la pièce (X) -->
            <div>
                <label for="part_size_x" class="block text-sm font-medium text-slate-100">Taille de la pièce (X) en mm :</label>
                <input type="number" name="part_size_x" id="part_size_x" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('part_size_x', session('part_size_x')) }}" required>
                @error('part_size_x')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if(session('partSizeY'))
            <div>
                <label for="part_size_y" class="block text-sm font-medium text-slate-100">Largeur (Y) en mm :</label>
                <input type="number" name="part_size_x" id="part_size_x" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ session('partSizeY') }}" disabled>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Nombre d'outils -->
            <div>
                <label for="number_of_tools" class="block text-sm font-medium text-slate-100">Nombre d'outils :</label>
                <input type="number" name="number_of_tools" id="number_of_tools" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('number_of_tools', session('number_of_tools')) }}" required>
                @error('number_of_tools')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <!-- Sélection de la forme -->
            <div>
                <label for="shape" class="block text-sm font-medium text-slate-100">Type de forme</label>
                <select name="shape" id="shape" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" required>
                    <option value="circle" @if(old('shape', session('shape')) == 'circle') selected @endif>Rond</option>
                    <option value="square" @if(old('shape', session('shape')) == 'square') selected @endif>Carré</option>
                    <option value="rectangle" @if(old('shape', session('shape')) == 'rectangle') selected @endif>Rectangle</option>
                    <option value="triangle" @if(old('shape', session('shape')) == 'triangle') selected @endif>Triangle</option>
                    <option value="random" @if(old('shape', session('shape')) == 'random') selected @endif>Random</option>
                </select>
                @error('shape')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Diamètre minimum de l'outil -->
            <div>
                <label for="min_tool_diameter" class="block text-sm font-medium text-slate-100">Diamètre minimum de l'outil (mm) :</label>
                <input type="number" name="min_tool_diameter" id="min_tool_diameter" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('min_tool_diameter', session('min_tool_diameter')) }}" required>
                @error('min_tool_diameter')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        
            <!-- Diamètre maximum de l'outil -->
            <div>
                <label for="max_tool_diameter" class="block text-sm font-medium text-slate-100">Diamètre maximum de l'outil (mm) :</label>
                <input type="number" name="max_tool_diameter" id="max_tool_diameter" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('max_tool_diameter', session('max_tool_diameter')) }}" required>
                @error('max_tool_diameter')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Ajout du fondu dégrader  -->
            <div class="flex items-center space-x-3">
                <input type="checkbox" id="fade" name="fade" value="1" class="h-5 w-5 text-lime-500 border-gray-300 rounded focus:ring-lime-500" @if(session('fade')) checked @endif>
                <label for="fade" class="text-sm font-medium text-slate-100">Activer le fondu de gauche à droite</label>
            </div>

            <!-- Choix de l'alignement (straight ou diagonal) -->
            <div class="form-group">
                <label for="alignment" class="block text-sm font-medium text-slate-100">Mode d'alignement :</label>
                <select id="alignment" name="alignment" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" required>
                    <option value="straight" @if(old('alignment', session('alignment')) == 'straight') selected @endif>Alignement droit (straight)</option>
                    <option value="diagonal" @if(old('alignment', session('alignment')) == 'diagonal') selected @endif>Alignement diagonal (45°)</option>
                </select>
                @error('alignment')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Upload de l'image -->
        <div>
            <label for="image" class="block text-sm font-medium text-slate-100">Télécharger l'image à découper :</label>
            <input type="file" name="image" id="image" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" required>
            @error('image')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    
        <!-- Bouton de soumission -->
        <div class="flex justify-end">
            <button type="submit" class="bg-lime-500 hover:bg-lime-600 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-400">Générer le motif</button>
        </div>
    </form>
    

    <div class="container mx-auto py-8">
        <!-- Message de succès -->
        @if(session('success'))
            <div class="bg-lime-500 text-lime-700 p-4 rounded-lg border border-lime-300 shadow-sm mb-6">
                <p class="text-lg font-medium">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('hitcount'))
            <div class="bg-slate-600 p-4 rounded-lg  border border-gray-200 mb-6 shadow-sm">
                <p class="text-lg font-medium">{{ session('hitcount') }} Coups</p>
            </div>
        @endif
        
    
        <div class="container mx-auto py-8">
            <!-- Liste des outils utilisés -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Liste des outils générés -->
                @if(session('generateTools'))
                <div class="bg-slate-500 p-4 rounded-lg border border-gray-200 mb-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-800 mb-2">Outils générés :</h2>
                    <ul class="list-disc pl-5 text-gray-700">
                        @foreach(session('generateTools') as $tool)
                            <li class="py-1">{{ session('shape') }} {{ $tool }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Grille à deux colonnes -->
                @if(session('usedTools'))
                    <div class="bg-slate-500 p-4 rounded-lg border border-gray-200 mb-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-800 mb-2">Outils utilisés :</h2>
                        <ul class="list-disc pl-5 text-gray-700">
                            @foreach(session('usedTools') as $tool)
                                <li class="py-1">{{ session('shape') }} {{ $tool }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Affichage de l'image originale -->
                @if(session('originalImageUrl'))
                <div class="text-center">
                    <h2 class="text-xl font-bold text-slate-100 mb-4">Image originale :</h2>
                    <div class="inline-block">
                        <!-- Assurez-vous de remplacer "image" par l'URL de votre image originale -->
                        <img src="{{ session('originalImageUrl') }}" alt="Original image" class="border border-gray-300 shadow-lg rounded-lg">
                    </div>
                </div>
                @endif

                <!-- Affichage de l'image générée -->
                @if(session('imageUrl'))
                <div class="text-center">
                    <h2 class="text-xl font-bold text-slate-100 mb-4">Image générée :</h2>
                    <div class="inline-block">
                        <img src="{{ session('imageUrl') }}" alt="Generated pattern" class="border border-gray-300 shadow-lg rounded-lg">
                    </div>
                </div>
                @endif
            </div>
        </div>
</div>
@endsection
