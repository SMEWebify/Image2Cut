<?php

namespace App\Services;

class FunctionService
{

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

}