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
            'part_size_x' => 'required|numeric|min:100',
            'number_of_tools' => 'required|numeric|min:1',
            'min_tool_diameter' => 'required|numeric|min:1',
            'max_tool_diameter' => 'required|numeric|gt:min_tool_diameter',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Upload image
            'shape' => 'required|in:circle,square,rectangle,triangle,random',
            'alignment' => 'required|in:straight,diagonal' 
        ]);

        $part_size_x = $request->input('part_size_x');
        $numberOfTools = $request->input('number_of_tools');
        $minToolDiameter = $request->input('min_tool_diameter');
        $maxToolDiameter = $request->input('max_tool_diameter');
        $shape = $request->input('shape');
        $fade = $request->has('fade');
        $alignment = $request->input('alignment');
        
        // Vérifier si une image a été uploadée
        if ($request->hasFile('image')) {
            // Sauvegarder l'image dans le répertoire public/uploads
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('uploads', $imageName, 'public'); // stocker dans public/uploads
    
            // Obtenir le chemin complet de l'image sauvegardée
            $fullImagePath = storage_path('app/public/' . $imagePath);
            $publicImageUrl = asset('storage/' . $imagePath);

            // Générer le motif avec l'image sauvegardée
            $result =  $this->generatePattern(  $part_size_x, $numberOfTools, $minToolDiameter, $maxToolDiameter, $fullImagePath, $shape, $fade, $alignment);
            
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
            return redirect()->route('metal-cutting.index')
                                ->withInput(input: $validatedData)
                                ->with('generateTools', $generateTools) 
                                ->with('usedTools', $usedTools) 
                                ->with('hitcount', $hitcount)
                                ->with('shape', $shape)
                                ->with('fade', $fade) 
                                ->with('partSizeY', $partSizeY) 
                                ->with('success', 'Image processée avec succès.')
                                ->with('imageUrl', asset('patterns/generated-pattern.png'))
                                ->with('originalImageUrl', $publicImageUrl);
        }
    
        return redirect()->route('metal-cutting.index')->with('error', 'Aucune image n\'a été uploadée.');
    }

    public function generatePattern($partSizeX, $numberOfTools, $minToolDiameter, $maxToolDiameter, $imagePath, $shape, $fade, $alignment = 'straight')
    {
        // Détecter le type MIME de l'image d'entrée pour obtenir les dimensions
        $imageInfo = getimagesize($imagePath);
        $imageWidth = $imageInfo[0];  // Largeur de l'image
        $imageHeight = $imageInfo[1]; // Hauteur de l'image
        $hitcount = 0;
    
        // Calculer la nouvelle hauteur pour conserver le ratio Y
        $partSizeY = (int)(($partSizeX / $imageWidth) * $imageHeight);
        // Créer une nouvelle image vierge (couleur noir)
        $newImage = imagecreatetruecolor($partSizeX, $partSizeY);
    
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
    
        // Redimensionner l'image d'origine pour qu'elle corresponde à la taille cible (en gardant le ratio)
        $resizedImage = imagecreatetruecolor($partSizeX, $partSizeY);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $partSizeX, $partSizeY, $imageWidth, $imageHeight);
    
        // Convertir l'image redimensionnée en niveaux de gris
        imagefilter($resizedImage, IMG_FILTER_GRAYSCALE);
    
        // Appliquer un fade horizontal à l'image si nécessaire
        if ($fade) {
            $this->applyHorizontalFade($resizedImage, $fade);
        }
    
        // Générer les tailles d'outils en fonction du nombre d'outils et des diamètres min/max
        $toolSizes = $this->generateToolSizes($numberOfTools, $minToolDiameter, $maxToolDiameter);
    
        // Stocker les positions des formes déjà dessinées
        $positions = [];
    
        // Stocker les outils utilisés pour le rapport à l'utilisateur
        $usedTools = [];

        // Variables pour ajuster la densité du motif
        $spacing = 10; // Espace entre chaque forme

        for ($y = 0; $y < imagesy($resizedImage); $y += $spacing) {
            // Vérification du mode d'alignement
            if ($alignment === 'diagonal') {
                // Si on est sur une ligne paire
                if ($y % ($spacing * 2) == 0) {
                    // Tracer des formes sur les colonnes paires
                    for ($x = 0; $x < imagesx($resizedImage); $x += ($spacing * 2)) {
                        // Récupérer la couleur du pixel et dessiner la forme
                        $this->drawShapeAtPosition($resizedImage, $newImage, $x, $y, $toolSizes, $shape, $positions);
                        $hitcount++;
                    }
                } else {
                    // Si on est sur une ligne impaire, tracer des formes sur les colonnes impaires
                    for ($x = $spacing; $x < imagesx($resizedImage); $x += ($spacing * 2)) {
                        // Récupérer la couleur du pixel et dessiner la forme
                        $this->drawShapeAtPosition($resizedImage, $newImage, $x, $y, $toolSizes, $shape, $positions);
                        $hitcount++;
                    }
                }
            } elseif ($alignment === 'straight') {
                // Mode "straight" : dessiner les formes de manière régulière
                for ($x = 0; $x < imagesx($resizedImage); $x += $spacing) {
                    // Récupérer la couleur du pixel et dessiner la forme
                    $this->drawShapeAtPosition($resizedImage, $newImage, $x, $y, $toolSizes, $shape, $positions);
                    $hitcount++;
                }
            }
        }
    
        // Sauvegarder l'image nouvellement créée
        $outputPath = public_path('patterns/generated-pattern.png');
        imagepng($newImage, $outputPath);
    
        // Libérer la mémoire
        imagedestroy($image);
        imagedestroy($resizedImage);
        imagedestroy($newImage);
    
        // Retourner le chemin de l'image générée et la liste des outils utilisés
        return [
            'imagePath' => $outputPath,
            'usedTools' => $usedTools,
            'toolSizes' => $toolSizes,
            'hitcount' => $hitcount,
            'partSizeY' => $partSizeY,
        ];
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
            $sizes[] = round(($minToolDiameter + $maxToolDiameter) / 2,1);
        } else {
            // Générer des tailles réparties entre le min et le max
            $step = ($maxToolDiameter - $minToolDiameter) / ($numberOfTools - 1);
            for ($i = 0; $i < $numberOfTools; $i++) {
                $sizes[] = round($minToolDiameter + ($i * $step),1);
            }
        }
        return $sizes;
    }
    
    public function isOverlapping($x, $y, $toolSize, $positions)
    {
        foreach ($positions as $pos) {
            // Calculer la distance entre les centres des formes
            $distance = sqrt(pow($x - $pos['x'], 2) + pow($y - $pos['y'], 2));

            // Vérifier si la distance est inférieure à la somme des rayons
            if ($distance < ($toolSize / 2 + $pos['size'] / 2)) {
                return true; // Les formes se chevauchent
            }
        }
        return false; // Aucune forme ne se chevauche
    }

    public function applyHorizontalFade($image, $fade)
    {
        $imageWidth = imagesx($image); // Largeur de l'image
        $imageHeight = imagesy($image); // Hauteur de l'image

        // Itérer sur chaque pixel de l'image pour appliquer le fade horizontalement
        for ($y = 0; $y < $imageHeight; $y++) {
            for ($x = 0; $x < $imageWidth; $x++) {

                // Récupérer la couleur actuelle du pixel
                $rgb = imagecolorat($image, $x, $y);

                // Extraire les composantes de la couleur (rouge, vert, bleu)
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                $blue = $rgb & 0xFF;

                // Calculer la variation de luminosité selon la position horizontale
                // Le facteur de fade sera calculé comme une proportion de la largeur de l'image
                $fadeFactor = ($x / $imageWidth) * 255;

                // Appliquer l'effet de fade sur chaque composante de couleur (réduire la luminosité)
                $newRed = max(0, $red - $fadeFactor);
                $newGreen = max(0, $green - $fadeFactor);
                $newBlue = max(0, $blue - $fadeFactor);

                // Recomposer la couleur modifiée
                $newColor = imagecolorallocate($image, $newRed, $newGreen, $newBlue);

                // Appliquer la nouvelle couleur au pixel
                imagesetpixel($image, $x, $y, $newColor);
            }
        }
    }

    private function drawShapeAtPosition($resizedImage, $newImage, $x, $y, $toolSizes, $shape, &$positions) {
        // Vérifier que $x et $y sont dans les limites de l'image redimensionnée
        if ($x >= imagesx($resizedImage) || $y >= imagesy($resizedImage)) {
            return; // Si $x ou $y sont hors limites, on retourne sans dessiner
        }
    
        // Récupérer la couleur du pixel de l'image redimensionnée (en niveaux de gris)
        $grayValue = imagecolorat($resizedImage, $x, $y) & 0xFF; // Extraire la composante grise
    
        // Ignorer les pixels très sombres
        if ($grayValue < 10) {
            return;
        }
    
        // Mapper la nuance de gris sur une taille d'outil dans le tableau généré
        $toolSize = $this->mapGrayToToolSize($grayValue, $toolSizes);
    
        // Vérifier si l'outil est trop proche des bords pour éviter de dessiner hors de l'image
        if ($x - $toolSize / 2 < 0 || $x + $toolSize / 2 > imagesx($resizedImage) || $y - $toolSize / 2 < 0 || $y + $toolSize / 2 > imagesy($resizedImage)) {
            return;
        }
    
        // Vérifier si l'outil chevauche une forme déjà dessinée
        if ($this->isOverlapping($x, $y, $toolSize, $positions)) {
            return;
        }
    
        // Dessiner la forme en fonction du type sélectionné
        $white = imagecolorallocate($newImage, 255, 255, 255); // Couleur blanc pour les formes

        // Si le mode "random" est activé, choisir une forme aléatoire
        if ($shape === 'random') {
            $shapes = ['circle', 'square', 'triangle'];
            $shape = $shapes[array_rand($shapes)]; // Sélectionne une forme aléatoire
        }

        switch ($shape) {
            case 'circle':
                imagefilledellipse($newImage, $x, $y, $toolSize, $toolSize, $white);
                break;
            case 'square':
                imagefilledrectangle($newImage, $x - $toolSize / 2, $y - $toolSize / 2, $x + $toolSize / 2, $y + $toolSize / 2, $white);
                break;
            case 'rectangle':
                $width = $toolSize * 2; // Exemple : rectangle deux fois plus large
                $height = $toolSize;
                imagefilledrectangle($newImage, $x - $width / 2, $y - $height / 2, $x + $width / 2, $y + $height / 2, $white);
                break;
            case 'triangle':
                // Coordonnées des trois points du triangle
                $points = [
                    $x, $y - $toolSize / 2, // Sommet supérieur
                    $x - $toolSize / 2, $y + $toolSize / 2, // Coin inférieur gauche
                    $x + $toolSize / 2, $y + $toolSize / 2  // Coin inférieur droit
                ];
                // Dessiner un triangle avec les points spécifiés
                imagefilledpolygon($newImage, $points, 3, $white);
                break;
        }
    
        // Ajouter la position de la forme dessinée
        $positions[] = ['x' => $x, 'y' => $y, 'size' => $toolSize];

        // Ajouter l'outil utilisé à la liste des outils seulement s'il n'existe pas déjà
        //if (!in_array($toolSize, $usedTools)) {
        //    $usedTools[] = $toolSize;
        //}
    }
    

}
