<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ImageService;
use App\Services\FunctionService;
use App\Services\PatterneService;

class MetalCuttingController extends Controller
{
    protected $patterneService ;
    protected $imageService ;
    protected $functionService ;
    protected $dxfService ;

    public function __construct(PatterneService $patterneService ,
                                ImageService $imageService ,
                                FunctionService $functionService ,
                                ){
        $this->patterneService  = $patterneService ;
        $this->imageService  = $imageService ;
        $this->functionService  = $functionService ;
    }

    // Affiche le formulaire
    public function index()
    {
        return view('image-cut.index');
    }

    // Traite les données du formulaire et génère l'image
    public function process(Request $request)
    {
        // Validation des champs du formulaire
        $validatedData = $request->validate([
            'part_size_x' => 'required|numeric|min:100',
            'number_of_tools' => 'required|numeric|min:1',
            'min_tool_diameter' => 'required|numeric|min:1',
            'max_tool_diameter' => 'required|numeric|gt:min_tool_diameter',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Upload image
            'shape' => 'required|in:circle,square,rectangle,triangle,hexagon,random',
            'angle' => 'required|numeric|min:0|max:360',
            'espace' => 'required|numeric|min:2|gt:max_tool_diameter',
            'ignoreThreshold' => 'required|numeric|min:0|max:100',
            'alignment' => 'required|in:straight,diagonal' ,
            'pen' => 'required|integer|between:1,16',
            'punchtime' => 'required|numeric|min:0',
        ]);

        $part_size_x = $request->input('part_size_x');
        $numberOfTools = $request->input('number_of_tools');
        $minToolDiameter = $request->input('min_tool_diameter');
        $maxToolDiameter = $request->input('max_tool_diameter');
        $shape = $request->input('shape');
        $angle = $request->input('angle');
        $espace = $request->input('espace');
        $fade = $request->has('fade');
        $invert = $request->has('invert');
        $ignoreThreshold = $request->input('ignoreThreshold');
        $alignment = $request->input('alignment');
        $pen = $request->input('pen');
        $punchtime = $request->input('punchtime');
        
        // Vérifier si une image a été uploadée
        if ($request->hasFile('image')) {
            // Sauvegarder l'image dans le répertoire public/uploads
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('uploads', $imageName, 'public'); // stocker dans public/uploads
    
            // Obtenir le chemin complet de l'image sauvegardée
            $fullImagePath = storage_path('app/public/' . $imagePath);
            $publicImageUrl = asset('storage/' . $imagePath);

            // Initialiser le fichier DXF si export activé
            $imageBaseName = pathinfo($imagePath, PATHINFO_FILENAME); 
            $dxfFilename = asset('storage/uploads/' . $imageBaseName .'.dxf');
            $gcodeFilename = asset('storage/uploads/' . $imageBaseName .'.nc');

            // Générer le motif avec l'image sauvegardée
            $result =  $this->patterneService->generatePattern(  
                                                            $part_size_x, 
                                                            $numberOfTools, 
                                                            $minToolDiameter,
                                                            $maxToolDiameter, 
                                                            $fullImagePath, 
                                                            $shape, 
                                                            $fade,
                                                            $invert,  
                                                            $alignment, 
                                                            $angle, 
                                                            $ignoreThreshold, 
                                                            $espace,
                                                            $pen
                                                        );
            
            // Chemin de l'image générée
            $imagePath = $result['imagePath'];

            // Liste des tailles d'outils généré
            $generateTools = $result['toolSizes'];

            // Liste des tailles d'outils utilisés
            $usedTools = $result['usedTools'];

            // Nombre de coups générés
            $hitcount = $result['hitcount'];

            // valeur Y de la taille de la pièce
            $partSizeY = $result['partSizeY'];

            // Rediriger avec l'URL du pattern généré
            return redirect()->route('image-cut.index')
                                ->withInput(input: $validatedData)
                                ->with('generateTools', $generateTools) 
                                ->with('usedTools', $usedTools) 
                                ->with('hitcount', $hitcount)
                                ->with('shape', $shape)
                                ->with('angle', $angle) 
                                ->with('espace', $espace) 
                                ->with('fade', $fade)
                                ->with('invert', $invert)
                                ->with('ignoreThreshold', $ignoreThreshold) 
                                ->with('partSizeY', $partSizeY) 
                                ->with('pen', $pen) 
                                ->with('punchtime', $punchtime)
                                ->with('success', 'Image processée avec succès.')
                                ->with('imageUrl', $result['imagePath'])
                                ->with('dxfPath', $dxfFilename)
                                ->with('gcodePath', $gcodeFilename)
                                ->with('originalImageUrl', $publicImageUrl);
        }
    
        return redirect()->route('image-cut.index')->with('error', 'Aucune image n\'a été uploadée.');
    }
}
