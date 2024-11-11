@extends('layouts.app')

@section('content')
<div class=" mx-auto px-4 bg-slate-500">
    <h1 class="text-4xl font-bold text-center my-6 text-slate-100">
        Image 2 cut
    </h1>

    <form action="{{ route('metal-cutting.process') }}" method="POST" enctype="multipart/form-data" class="bg-slate-800 shadow-lg rounded-lg p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Taille de la pièce (X) -->
            <div>
                <label for="part_size_x" class="block text-sm font-medium text-slate-100">Taille de la pièce (X) en mm :</label>
                <input type="number" name="part_size_x" id="part_size_x" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('part_size_x', session('part_size_x', 500)) }}" required>
                @error('part_size_x')
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if(session('partSizeY'))
            <div>
                <label for="part_size_y" class="block text-sm font-medium text-slate-300">Largeur (Y) en mm :</label>
                <input type="number" name="part_size_x" id="part_size_x" class="mt-1 block w-full bg-slate-900 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ session('partSizeY') }}" disabled>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
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
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="angle" class="block text-sm font-medium text-slate-100">Angle (°) :</label>
                <input type="number" name="angle" id="angle" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('angle', session('angle', 0)) }}" min="0" max="360" required>
                @error('angle')
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Espace entre deux formes-->
            <div>
                <label for="espace" class="block text-sm font-medium text-slate-100">Espace :</label>
                <input type="number" name="espace" id="espace" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('espace', session('espace', 11)) }}" min="2"  required>
                @error('espace')
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Nombre d'outils -->
            <div>
                <label for="number_of_tools" class="block text-sm font-medium text-slate-100">Nombre d'outils différents:</label>
                <input type="number" name="number_of_tools" id="number_of_tools" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('number_of_tools', session('number_of_tools', 3)) }}" min="1"  required>
                @error('number_of_tools')
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Diamètre minimum de l'outil -->
            <div>
                <label for="min_tool_diameter" class="block text-sm font-medium text-slate-100">Diamètre minimum de l'outil (mm) :</label>
                <input type="number" name="min_tool_diameter" id="min_tool_diameter" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('min_tool_diameter', session('min_tool_diameter', 2)) }}" required>
                @error('min_tool_diameter')
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        
            <!-- Diamètre maximum de l'outil -->
            <div>
                <label for="max_tool_diameter" class="block text-sm font-medium text-slate-100">Diamètre maximum de l'outil (mm) :</label>
                <input type="number" name="max_tool_diameter" id="max_tool_diameter" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('max_tool_diameter', session('max_tool_diameter', 10)) }}" required>
                @error('max_tool_diameter')
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Ajout du fondu dégrader  -->
            <div class="flex items-center space-x-3">
                <input type="checkbox" id="fade" name="fade" value="1" class="shrink-0 mt-0.5 border-gray-200 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800" @if(session('fade')) checked @endif>
                <label for="fade" class="text-sm font-medium text-slate-100">Activer le fondu de gauche à droite</label>
            </div>

            <!-- Profondeur de seuil -->
            <div>
                <label for="ignoreThreshold" class="block text-sm font-medium text-slate-100">Niveau de seuil ignoré :</label>
                <input type="range" class="w-full bg-transparent cursor-pointer appearance-none disabled:opacity-50 disabled:pointer-events-none focus:outline-none
                [&::-webkit-slider-thumb]:w-2.5
                [&::-webkit-slider-thumb]:h-2.5
                [&::-webkit-slider-thumb]:-mt-0.5
                [&::-webkit-slider-thumb]:appearance-none
                [&::-webkit-slider-thumb]:bg-white
                [&::-webkit-slider-thumb]:shadow-[0_0_0_4px_rgba(37,99,235,1)]
                [&::-webkit-slider-thumb]:rounded-full
                [&::-webkit-slider-thumb]:transition-all
                [&::-webkit-slider-thumb]:duration-150
                [&::-webkit-slider-thumb]:ease-in-out
                [&::-webkit-slider-thumb]:dark:bg-neutral-700

                [&::-moz-range-thumb]:w-2.5
                [&::-moz-range-thumb]:h-2.5
                [&::-moz-range-thumb]:appearance-none
                [&::-moz-range-thumb]:bg-slate-700
                [&::-moz-range-thumb]:border-4
                [&::-moz-range-thumb]:border-blue-600
                [&::-moz-range-thumb]:rounded-full
                [&::-moz-range-thumb]:transition-all
                [&::-moz-range-thumb]:duration-150
                [&::-moz-range-thumb]:ease-in-out

                [&::-webkit-slider-runnable-track]:w-full
                [&::-webkit-slider-runnable-track]:h-2
                [&::-webkit-slider-runnable-track]:bg-gray-100
                [&::-webkit-slider-runnable-track]:rounded-full
                [&::-webkit-slider-runnable-track]:dark:bg-neutral-700

                [&::-moz-range-track]:w-full
                [&::-moz-range-track]:h-2
                [&::-moz-range-track]:bg-gray-100
                [&::-moz-range-track]:rounded-full" id="ignoreThreshold" name="ignoreThreshold" aria-orientation="horizontal" value="{{ old('ignoreThreshold', session('ignoreThreshold', 10)) }}" min="0" max="100">
                @error('ignoreThreshold')
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Choix de l'alignement (straight ou diagonal) -->
            <div class="form-group">
                <label for="alignment" class="block text-sm font-medium text-slate-100">Mode d'alignement :</label>
                <select id="alignment" name="alignment" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" required>
                    <option value="straight" @if(old('alignment', session('alignment')) == 'straight') selected @endif>Alignement droit (straight)</option>
                    <option value="diagonal" @if(old('alignment', session('alignment')) == 'diagonal') selected @endif>Alignement diagonal (45°)</option>
                </select>
                @error('alignment')
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Couleur de sortie du dxf -->
            <div>
                <label for="pen" class="block text-sm font-medium text-slate-100">Couleur du dxf (1-16) :</label>
                <input type="number" name="pen" id="pen" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" value="{{ old('pen', session('pen', 8)) }}" required min="1" max="16">
                @error('pen')
                    <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Upload de l'image -->
        <div>
            <label for="image" class="block text-sm font-medium text-slate-100">Télécharger l'image à découper :</label>
            <input type="file" name="image" id="image" class="mt-1 block w-full bg-slate-700 text-slate-100 border border-slate-600 rounded-lg py-2 px-3 focus:ring-slate-500 focus:border-slate-500" required>
            @error('image')
                <p class="text-lime-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
<!--
        <div id="hs-file-upload" data-hs-file-upload='{
        "url": "/upload",
        "extensions": {
            "default": {
            "class": "shrink-0 size-5"
            },
            "xls": {
            "class": "shrink-0 size-5"
            },
            "zip": {
            "class": "shrink-0 size-5"
            },
            "csv": {
            "icon": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4\"/><path d=\"M14 2v4a2 2 0 0 0 2 2h4\"/><path d=\"m5 12-3 3 3 3\"/><path d=\"m9 18 3-3-3-3\"/></svg>",
            "class": "shrink-0 size-5"
            }
        }
        }'>
        <template data-hs-file-upload-preview="">
            <div class="p-3 bg-white border border-solid border-gray-300 rounded-xl dark:bg-neutral-800 dark:border-neutral-600">
                <div class="mb-2 flex justify-between items-center">
                    <div class="flex items-center gap-x-3">
                        <span class="size-8 flex justify-center items-center border border-gray-200 text-gray-500 rounded-lg dark:border-neutral-700 dark:text-neutral-500" data-hs-file-upload-file-icon="">
                            <img class="rounded-lg hidden" data-dz-thumbnail="">
                        </span>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white">
                            <span class="truncate inline-block max-w-[300px] align-bottom" data-hs-file-upload-file-name=""></span>.<span data-hs-file-upload-file-ext=""></span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-neutral-500" data-hs-file-upload-file-size=""></p>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-x-2">
                        <button type="button" class="text-gray-500 hover:text-gray-800 focus:outline-none focus:text-gray-800 dark:text-neutral-500 dark:hover:text-neutral-200 dark:focus:text-neutral-200" data-hs-file-upload-remove="">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            <line x1="10" x2="10" y1="11" y2="17"></line>
                            <line x1="14" x2="14" y1="11" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            
                <div class="flex items-center gap-x-3 whitespace-nowrap">
                    <div class="flex w-full h-2 bg-gray-200 rounded-full overflow-hidden dark:bg-neutral-700" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" data-hs-file-upload-progress-bar="">
                        <div class="flex flex-col justify-center rounded-full overflow-hidden bg-blue-600 text-xs text-white text-center whitespace-nowrap transition-all duration-500 hs-file-upload-complete:bg-green-600 dark:bg-blue-500" style="width: 0" data-hs-file-upload-progress-bar-pane=""></div>
                    </div>
                    <div class="w-10 text-end">
                        <span class="text-sm text-gray-800 dark:text-white">
                            <span data-hs-file-upload-progress-bar-value="">0</span>%
                        </span>
                    </div>
                </div>
            </div>
        </template>
        
        <div class="cursor-pointer p-12 flex justify-center bg-slate-700 border border-dashed border-gray-300 rounded-xl dark:bg-neutral-800 dark:border-neutral-600" data-hs-file-upload-trigger="">
            <div class="text-center">
                <svg class="w-16 text-gray-400 mx-auto dark:text-neutral-400" width="70" height="46" viewBox="0 0 70 46" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.05172 9.36853L17.2131 7.5083V41.3608L12.3018 42.3947C9.01306 43.0871 5.79705 40.9434 5.17081 37.6414L1.14319 16.4049C0.515988 13.0978 2.73148 9.92191 6.05172 9.36853Z" fill="currentColor" stroke="currentColor" stroke-width="2" class="fill-white stroke-gray-400 dark:fill-neutral-800 dark:stroke-neutral-500"></path>
                    <path d="M63.9483 9.36853L52.7869 7.5083V41.3608L57.6982 42.3947C60.9869 43.0871 64.203 40.9434 64.8292 37.6414L68.8568 16.4049C69.484 13.0978 67.2685 9.92191 63.9483 9.36853Z" fill="currentColor" stroke="currentColor" stroke-width="2" class="fill-white stroke-gray-400 dark:fill-neutral-800 dark:stroke-neutral-500"></path>
                    <rect x="17.0656" y="1.62305" width="35.8689" height="42.7541" rx="5" fill="currentColor" stroke="currentColor" stroke-width="2" class="fill-white stroke-gray-400 dark:fill-neutral-800 dark:stroke-neutral-500"></rect>
                    <path d="M47.9344 44.3772H22.0655C19.3041 44.3772 17.0656 42.1386 17.0656 39.3772L17.0656 35.9161L29.4724 22.7682L38.9825 33.7121C39.7832 34.6335 41.2154 34.629 42.0102 33.7025L47.2456 27.5996L52.9344 33.7209V39.3772C52.9344 42.1386 50.6958 44.3772 47.9344 44.3772Z" stroke="currentColor" stroke-width="2" class="stroke-gray-400 dark:stroke-neutral-500"></path>
                    <circle cx="39.5902" cy="14.9672" r="4.16393" stroke="currentColor" stroke-width="2" class="stroke-gray-400 dark:stroke-neutral-500"></circle>
                </svg>
            
                <div class="mt-4 flex flex-wrap justify-center text-sm leading-6 text-gray-600">
                    <span class="pe-1 font-medium text-gray-800 dark:text-neutral-200">
                    Drop your file here or
                    </span>
                    <span class="bg-slate-500 font-semibold text-blue-600 hover:text-blue-700 rounded-lg decoration-2 hover:underline focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 dark:bg-neutral-800 dark:text-blue-500 dark:hover:text-blue-600">browse</span>
                </div>
            
                <p class="mt-1 text-xs text-gray-400 dark:text-neutral-400">
                    Pick a photo up to 2MB.
                </p>
            </div>
        </div> -->
    
        <!-- Bouton de soumission -->
        <div class="flex justify-end">
            <button type="submit" class="bg-lime-500 hover:bg-lime-600 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-400">Générer le motif</button>
        </div>
    </form>
    <div align="center">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1279966095557282"
        crossorigin="anonymous"></script>
        <!-- Tempo -->
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-1279966095557282"
            data-ad-slot="8185039017"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
    
    <div class="container mx-auto py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div>
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
            </div>

            <!-- Liste des outils générés -->
            @if(session('generateTools'))
            <div class="bg-slate-600 p-4 rounded-lg border border-gray-200 mb-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Outils générés :</h2>
                <ul class="list-disc pl-5 text-gray-700">
                    @foreach(session('generateTools') as $tool)
                        <li class="py-1">{{ session('shape') }} {{ $tool }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Liste des outils utilisés -->
            @if(session('usedTools'))
                <div class="bg-slate-600 p-4 rounded-lg border border-gray-200 mb-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-800 mb-2">Outils utilisés :</h2>
                    <ul class="list-disc pl-5 text-gray-700">
                        @foreach(session('usedTools') as $tool)
                            <li class="py-1">{{ session('shape') }} {{ $tool }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">
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
            
            @if(session('imageUrl'))
            <div class="text-center flex flex-col  space-y-4">
                <!-- Bouton de téléchargement pour le fichier DXF -->
                <a href="{{ session('dxfPath') }}" download class="inline-block px-4 py-2 bg-slate-800 text-white font-semibold rounded-md shadow hover:bg-lime-600 transition">
                    Télécharger le fichier DXF
                </a>
                
                <!-- Bouton de téléchargement pour le fichier NC -->
                <a href="{{ session('gcodePath') }}" download class="inline-block px-4 py-2 bg-slate-900 text-white font-semibold rounded-md shadow hover:bg-lime-600 transition">
                    Télécharger le fichier NC
                </a>
            </div>

            <!-- Affichage de l'image générée -->
            
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
