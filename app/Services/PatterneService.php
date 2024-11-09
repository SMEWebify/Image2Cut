<?php

namespace App\Services;

use App\Services\ImageService;
use App\Services\FunctionService;

class PatterneService
{
    protected $imageService ;
    protected $functionService ;
    protected $dxfService ;
    protected $gcodeService ;
    protected $filterService ;

    public function __construct(ImageService $imageService ,
                                FunctionService $functionService , 
                                DxfService $dxfService , 
                                GcodeService $gcodeService ,
                                FilterService $filterService ,
                                ){
        $this->imageService  = $imageService ;
        $this->functionService  = $functionService ;
        $this->dxfService  = $dxfService ;
        $this->gcodeService  = $gcodeService ;
        $this->filterService  = $filterService ;
    }

    public function generatePattern($partSizeX, $numberOfTools, $minToolDiameter, $maxToolDiameter, $imagePath, $shape, $fade, $alignment = 'straight', $angle = null, $ignoreThreshold, $exportDXF=false)
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
        //imagefilter($resizedImage, IMG_FILTER_GRAYSCALE);
    
        // Appliquer un fade horizontal à l'image si nécessaire
        if ($fade) {
            $this->filterService->applyHorizontalFade($resizedImage, $fade);
        }
    
        // Générer les tailles d'outils en fonction du nombre d'outils et des diamètres min/max
        $toolSizes = $this->functionService->generateToolSizes($numberOfTools, $minToolDiameter, $maxToolDiameter);
    
        // Stocker les positions des formes déjà dessinées
        $positions = [];
    
        // Stocker les outils utilisés pour le rapport à l'utilisateur
        $usedTools = [];

        // Initialiser le fichier DXF si export activé
        
        $dxfFilename = $imagePath . '.dxf';
        //if ($exportDXF && $dxfFilename) {
            $this->dxfService->saveDXFFile($dxfFilename);
        //}
        $gcodeFilename = $imagePath . 'nc';
        //if ($exportDXF && $dxfFilename) {
            $this->gcodeService->saveGcodeFile($gcodeFilename);
        //}

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
                        $CurrenttoolSize = $this->imageService->drawShapeAtPosition($resizedImage, $newImage, $x, $y, $toolSizes, $shape, $positions, $angle, $ignoreThreshold);
                        $this->dxfService->drawShapeAtPositionDXF($resizedImage, $x, $y, $toolSizes, $shape, $positions, $angle , $ignoreThreshold);
                        $this->gcodeService->drawShapeAtPositionGCode($resizedImage, $x, $y, $toolSizes, $shape, $positions, $angle , $ignoreThreshold);
                    
                        if(isset($CurrenttoolSize) ){
                            $hitcount++;
                            if(!in_array($CurrenttoolSize, $usedTools)){
                                $usedTools[] = $CurrenttoolSize;
                            }
                        }
                    }
                } else {
                    // Si on est sur une ligne impaire, tracer des formes sur les colonnes impaires
                    for ($x = $spacing; $x < imagesx($resizedImage); $x += ($spacing * 2)) {
                        // Récupérer la couleur du pixel et dessiner la forme
                        $CurrenttoolSize = $this->imageService->drawShapeAtPosition($resizedImage, $newImage, $x, $y, $toolSizes, $shape, $positions, $angle, $ignoreThreshold);
                        $this->dxfService->drawShapeAtPositionDXF($resizedImage, $x, $y, $toolSizes, $shape, $positions, $angle , $ignoreThreshold);
                        $this->gcodeService->drawShapeAtPositionGCode($resizedImage, $x, $y, $toolSizes, $shape, $positions, $angle , $ignoreThreshold);
                    
                        if(isset($CurrenttoolSize)) {
                            $hitcount++;
                            if(!in_array($CurrenttoolSize, $usedTools)){
                                $usedTools[] = $CurrenttoolSize;
                            }
                        }
                    }
                }
            } elseif ($alignment === 'straight') {
                // Mode "straight" : dessiner les formes de manière régulière
                for ($x = 0; $x < imagesx($resizedImage); $x += $spacing) {
                    // Récupérer la couleur du pixel et dessiner la forme
                    $CurrenttoolSize = $this->imageService->drawShapeAtPosition($resizedImage, $newImage, $x, $y, $toolSizes, $shape, $positions, $angle, $ignoreThreshold);
                    $this->dxfService->drawShapeAtPositionDXF($resizedImage, $x, $y, $toolSizes, $shape, $positions, $angle , $ignoreThreshold);
                    $this->gcodeService->drawShapeAtPositionGCode($resizedImage, $x, $y, $toolSizes, $shape, $positions, $angle , $ignoreThreshold);
                    
                    if(isset($CurrenttoolSize)) {
                        $hitcount++;
                        if(!in_array($CurrenttoolSize, $usedTools)){
                            $usedTools[] = $CurrenttoolSize;
                        }
                    }
                }
            }
        }
        
        // Sauvegarder l'image nouvellement créée
        $outputPath = public_path('patterns/generated-pattern.png');
        imagepng($newImage, $outputPath);
    
        // Fermer le fichier DXF et Gcode
        $this->dxfService->closeDXFFile();
        $this->gcodeService->closeGcodeFile();

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
}