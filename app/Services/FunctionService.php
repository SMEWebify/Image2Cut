<?php

namespace App\Services;

class FunctionService
{

    public function mapGrayToToolSize($grayValue, $toolSizes, $invert = false)
    {
        // Calculer l'indice en fonction de la nuance de gris
        $index = (int) ($grayValue / 255 * (count($toolSizes) - 1));
    
        // Si l'inversion est activée, inverser l'indice
        if ($invert) {
            $index = (count($toolSizes) - 1) - $index; // Inverser l'indice
        }
    
        // Retourner la taille d'outil correspondante
        return $toolSizes[$index];
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

    public function calculateHexagonPoints($toolSize)
    {
        $halfSize = $toolSize / 2;
        $radius = $toolSize / 2; // Rayon de l'hexagone
    
        // Points pour un hexagone régulier (angle entre chaque point : 60 degrés)
        $points = [];
        for ($i = 0; $i < 6; $i++) {
            // Calculer les angles pour chaque sommet de l'hexagone
            $angle = deg2rad(60 * $i);
            // Calculer les coordonnées en fonction du rayon et de l'angle
            $x = $halfSize + $radius * cos($angle); // Décalage en X
            $y = $halfSize + $radius * sin($angle); // Décalage en Y
            $points[] = $x;
            $points[] = $y;
        }
    
        return $points;
    }

}