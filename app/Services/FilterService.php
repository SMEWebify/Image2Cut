<?php

namespace App\Services;


class FilterService
{
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
}