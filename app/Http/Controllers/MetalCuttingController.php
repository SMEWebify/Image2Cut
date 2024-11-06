<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MetalCuttingController extends Controller
{
    // Affiche le formulaire
    public function index()
    {
        return view('metal-cutting.index');
    }

    // Traite les données du formulaire et génère l'image
    public function process(Request $request)
    {
        // Validation des champs du formulaire
        $validatedData = $request->validate([
            'part_size_x' => 'required|numeric|min:10',
            'part_size_y' => 'required|numeric|min:10',
            'number_of_tools' => 'required|numeric|min:1',
            'min_tool_diameter' => 'required|numeric|min:1',
            'max_tool_diameter' => 'required|numeric|gt:min_tool_diameter',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Upload image
            'shape' => 'required|in:circle,square,rectangle' 
        ]);

        
        $partSizeX = $request->input('part_size_x');
        $partSizeY = $request->input('part_size_y');
        $numberOfTools = $request->input('number_of_tools');
        $minToolDiameter = $request->input('min_tool_diameter');
        $maxToolDiameter = $request->input('max_tool_diameter');
        $shape = $request->input('shape');
        
        // Vérifier si une image a été uploadée
        if ($request->hasFile('image')) {
            // Sauvegarder l'image dans le répertoire public/uploads
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('uploads', $imageName, 'public'); // stocker dans public/uploads
    
            // Obtenir le chemin complet de l'image sauvegardée
            $fullImagePath = storage_path('app/public/' . $imagePath);
    
            // Générer le motif avec l'image sauvegardée
            $generatedPatternPath = $this->generatePattern($partSizeX, $partSizeY, $numberOfTools, $minToolDiameter, $maxToolDiameter, $fullImagePath, $shape);

            // Rediriger avec l'URL du pattern généré
            return redirect()->route('metal-cutting.index')
                                ->withInput(input: $validatedData)
                                ->with('success', 'Image processée avec succès.')
                                ->with('imageUrl', asset('patterns/generated-pattern.png'));
        }
    
        return redirect()->route('metal-cutting.index')->with('error', 'Aucune image n\'a été uploadée.');
    }

    public function generatePattern($partSizeX, $partSizeY, $numberOfTools, $minToolDiameter, $maxToolDiameter, $imagePath, $shape)
    {
        // Détecter le type MIME de l'image d'entrée pour obtenir les dimensions
        $imageInfo = getimagesize($imagePath);
        $imageWidth = $imageInfo[0];  // Largeur de l'image
        $imageHeight = $imageInfo[1]; // Hauteur de l'image
    
        // Créer une nouvelle image vierge (couleur noir)
        $newImage = imagecreatetruecolor($imageWidth, $imageHeight);
        $black = imagecolorallocate($newImage, 0, 0, 0); // Couleur noir
        imagefill($newImage, 0, 0, $black); // Remplir l'image de noir
    
        // Charger l'image d'origine pour récupérer les informations de gris
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                throw new \Exception('Type de fichier image non supporté');
        }
    
         // Générer les tailles d'outils en fonction du nombre d'outils et des diamètres min/max
        $toolSizes = $this->generateToolSizes($numberOfTools, $minToolDiameter, $maxToolDiameter);


        // Parcourir chaque pixel de l'image originale (on peut ignorer certains pour éviter une trop grande densité de formes)
        for ($y = 0; $y < $imageHeight; $y += 10) { // Incrémenter par 10 pour réduire la densité des formes
            for ($x = 0; $x < $imageWidth; $x += 10) {
    
                // Récupérer la couleur du pixel de l'image originale
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
    
                // Calculer la nuance de gris (moyenne des canaux R, G, B)
                $grayValue = ($r + $g + $b) / 3;
    
                 // Mapper la nuance de gris sur une taille d'outil dans le tableau généré
                $toolSize = $this->mapGrayToToolSize($grayValue, $toolSizes);

    
                // Dessiner la forme en fonction du type sélectionné
                $white = imagecolorallocate($newImage, 255, 255, 255); // Couleur blanc pour les formes
                switch ($shape) {
                    case 'circle':
                        imagefilledellipse($newImage, $x, $y, $toolSize, $toolSize, $white);
                        break;
                    case 'square':
                        imagefilledrectangle($newImage, $x, $y, $x + $toolSize, $y + $toolSize, $white);
                        break;
                    case 'rectangle':
                        $width = $toolSize * 2; // Exemple : rectangle deux fois plus large
                        $height = $toolSize;
                        imagefilledrectangle($newImage, $x, $y, $x + $width, $y + $height, $white);
                        break;
                }
            }
        }
    
        // Sauvegarder l'image nouvellement créée
        $outputPath = public_path('patterns/generated-pattern.png');
        imagepng($newImage, $outputPath);
    
        // Libérer la mémoire
        imagedestroy($image);
        imagedestroy($newImage);
    
        return $outputPath;
    }
    

    public function mapGrayToToolSize($grayValue, $toolSizes)
    {
        // Mapper la valeur grise à une taille d'outil
        $index = (int) ($grayValue / 255 * (count($toolSizes) - 1)); // Calculer l'indice en fonction de la nuance de gris
        return $toolSizes[$index]; // Retourner la taille d'outil correspondante
    }

    public function generateToolSizes($numberOfTools, $minToolDiameter, $maxToolDiameter)
    {
        $sizes = [];
        if ($numberOfTools == 1) {
            // Si un seul outil, ajouter directement la taille moyenne
            $sizes[] = ($minToolDiameter + $maxToolDiameter) / 2;
        } else {
            // Générer des tailles réparties entre le min et le max
            $step = ($minToolDiameter - $maxToolDiameter) / ($numberOfTools - 1);
            for ($i = 0; $i < $numberOfTools; $i++) {
                $sizes[] = $maxToolDiameter + ($i * $step);
            }
        }
        return $sizes;
    }
    
}
