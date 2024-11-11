<?php

namespace App\Services;

use App\Services\FunctionService;

class ImageService
{
    protected $functionService ;

    public function __construct(FunctionService $functionService , ){
        $this->functionService  = $functionService ;
    }

    public function drawShapeAtPosition($resizedImage, $newImage, $x, $y, $toolSizes, $shape, &$positions, $angle = null, $ignoreThreshold) {
        
        // Vérifier que $x et $y sont dans les limites de l'image redimensionnée
        if ($x >= imagesx($resizedImage) || $y >= imagesy($resizedImage)) {
            return; // Si $x ou $y sont hors limites, on retourne sans dessiner
        }
        // Récupérer la couleur du pixel de l'image redimensionnée (en niveaux de gris)
        $grayValue = imagecolorat($resizedImage, $x, $y) & 0xFF; // Extraire la composante grise
    
        // Ignorer les pixels très sombres
        if ($grayValue < $ignoreThreshold) {
            return;
        }
    
        // Mapper la nuance de gris sur une taille d'outil dans le tableau généré
        $toolSize = $this->functionService->mapGrayToToolSize($grayValue, $toolSizes);
    
        // Vérifier si l'outil est trop proche des bords pour éviter de dessiner hors de l'image
        if ($x - $toolSize / 2 < 0 || $x + $toolSize / 2 > imagesx($resizedImage) || $y - $toolSize / 2 < 0 || $y + $toolSize / 2 > imagesy($resizedImage)) {
            return;
        }
    
        // Vérifier si l'outil chevauche une forme déjà dessinée
        if ($this->functionService->isOverlapping($x, $y, $toolSize, $positions)) {
            return;
        }
    
        // Si le mode "random" est activé, choisir une forme et un angle aléatoire
        if ($shape === 'random') {
            $shapes = ['circle', 'square', 'triangle'];
            $shape = $shapes[array_rand($shapes)]; // Sélectionne une forme aléatoire
            $angle = rand(0, 360); // Angle aléatoire pour chaque forme
        }
    
        // Si aucun angle n'est spécifié, utiliser 0 (pas de rotation)
        if (is_null($angle)) {
            $angle = 0;
        }
    
        // Créer une image temporaire pour dessiner la forme
        $tempImage = imagecreatetruecolor($toolSize*2, $toolSize*2);
        imagesavealpha($tempImage, true); // Préserver la transparence
        $transparent = imagecolorallocatealpha($tempImage, 0, 0, 0, 127); // Couleur transparente
        imagefill($tempImage, 0, 0, $transparent); // Remplir avec la transparence
    
        // Dessiner la forme sur l'image temporaire
        $white = imagecolorallocate($tempImage, 255, 255, 255); // Couleur blanche pour les formes
        switch ($shape) {
            case 'circle':
                imagefilledellipse($tempImage, $toolSize, $toolSize, $toolSize, $toolSize, $white);
                break;
            case 'square':
                imagefilledrectangle($tempImage, 0, 0, $toolSize, $toolSize, $white);
                break;
            case 'triangle':
                $points = [
                    $toolSize/2, 0, // Sommet supérieur
                    0, $toolSize , // Coin inférieur gauche
                    $toolSize, $toolSize  // Coin inférieur droit
                ];
                imagefilledpolygon($tempImage, $points, 3, $white);
                break;
        }
    
        // Appliquer la rotation à l'image temporaire contenant la forme
        $rotatedImage = imagerotate($tempImage, $angle, 0);
    
        // Activer la transparence pour l'image tournée
        imagesavealpha($rotatedImage, true);
        imagealphablending($rotatedImage, false);
    
        // Copier la forme tournée sur l'image principale
        $dstX = $x - (imagesx($rotatedImage) / 2);
        $dstY = $y - (imagesy($rotatedImage) / 2);
        imagecopy($newImage, $rotatedImage, $dstX, $dstY, 0, 0, imagesx($rotatedImage), imagesy($rotatedImage));
        
        // Libérer la mémoire
        imagedestroy($tempImage);
        imagedestroy($rotatedImage);
    
        // Ajouter la position de la forme dessinée
        $positions[] = ['x' => $x, 'y' => $y, 'size' => $toolSize];

        return $toolSize;
    }
}
