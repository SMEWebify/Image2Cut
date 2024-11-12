<?php

namespace App\Services;

use App\Services\FunctionService;

class GcodeService
{
    protected $functionService ;

    public function __construct(FunctionService $functionService , ){
        $this->functionService  = $functionService ;
    }
    private $gcodeFile;
    private $lineNumber = 1;
    
    // Sauvegarde du fichier Gcode en initialisant la structure de base
    public function saveGcodeFile($filename)
    {
        // Ouvre le fichier G-code en mode écriture
        $this->gcodeFile = fopen($filename, 'w');
        
        if ($this->gcodeFile === false) {
            throw new \Exception("Impossible d'ouvrir le fichier G-code pour écriture.");
        }

        // Ajouter les commandes initiales au fichier G-code (par exemple, pour initialiser la machine)
        fwrite($this->gcodeFile, "G21 ; Set units to millimeters\n");
        fwrite($this->gcodeFile, "G90 ; Absolute positioning\n");
        fwrite($this->gcodeFile, "G1 Z5.0 F500 ; Raise to safe height\n"); // Monter la tête avant de commencer
    }

    // Dessine une forme à une position donnée dans le fichier Gcode
    public function drawShapeAtPositionGCode($resizedImage, $x, $y, $toolSizes, $shape, &$positions, $angle = null, $ignoreThreshold, $invert)
    {
        // Vérifier que $x et $y sont dans les limites de l'image redimensionnée
        if ($x >= imagesx($resizedImage) || $y >= imagesy($resizedImage)) {
            return; // Si $x ou $y sont hors limites, on retourne sans dessiner
        }
        // Récupérer la couleur du pixel de l'image redimensionnée (en niveaux de gris)
        $grayValue = imagecolorat($resizedImage, $x, $y) & 0xFF; // Extraire la composante grise
    
        // Ignorer les pixels très sombres
        if ($invert) {
            // Ignorer les pixels très clairs si on inverse la logique
            if ($grayValue > (255 - $ignoreThreshold)) {
                return;
            }
        } else {
            // Ignorer les pixels très sombres (logique normale)
            if ($grayValue < $ignoreThreshold) {
                return;
            }
        }

        // Mapper la nuance de gris sur une taille d'outil dans le tableau généré
        $toolSize = $this->functionService->mapGrayToToolSize($grayValue, $toolSizes, $invert);
        
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

        // Ajouter la forme dans le fichier Gcode
        switch ($shape) {
            case 'circle':
                $radius = $toolSize / 2;
                $this->writeCircleToGcode($x, -$y, $radius, $angle);
                break;
            case 'square':
                $this->writeSquareToGcode($x, -$y, $toolSize, $angle);
                break;
            case 'rectangle':
                $this->writeRectangleToGcode($x, -$y, $toolSize*2, $toolSize,$angle);
                break;
            case 'triangle':
                $this->writeTriangleToGcode($x, -$y, $toolSize, $angle);
                break;
            case 'hexagon':
                $this->writeHexagonToGcode($x, -$y, $toolSize, $angle);
                break;
        }
        
        // Ajouter la position de la forme dessinée
        $positions[] = ['x' => $x, 'y' => $y, 'size' => $toolSize];

        // On met à jour le fichier Gcode
        return $toolSize;
    }

    // Fonction pour écrire une ligne dans le fichier avec numérotation
    private function writeLine($command)
    {
        fwrite($this->gcodeFile, "N{$this->lineNumber} $command\n");
        $this->lineNumber++; // Incrémenter la numérotation à chaque ligne
    }

    // Fonction pour écrire un cercle dans le fichier Gcode (tracé d'un cercle)
    private function writeCircleToGcode($x, $y, $radius, $angle = null)
    {
        $this->writeLine("G0 X$x Y$y ; Move to start position");
        $this->writeLine("G1 Z-1.0 F500 ; Lower to cutting depth");
        
        // G2 pour faire un mouvement circulaire dans le sens horaire
        $this->writeLine("G2 X$x Y$y I$radius J0 F300 ; Draw circle");
        
        $this->writeLine("G1 Z5.0 F500 ; Raise to safe height");
    }

    private function writeSquareToGcode($x, $y, $size, $angle) {
        $halfSize = $size / 2;
        
        // Points du carré avant rotation (coin supérieur gauche, coin supérieur droit, etc.)
        $points = [
            ['x' => $x - $halfSize, 'y' => $y - $halfSize], // Coin supérieur gauche
            ['x' => $x + $halfSize, 'y' => $y - $halfSize], // Coin supérieur droit
            ['x' => $x + $halfSize, 'y' => $y + $halfSize], // Coin inférieur droit
            ['x' => $x - $halfSize, 'y' => $y + $halfSize], // Coin inférieur gauche
            ['x' => $x - $halfSize, 'y' => $y - $halfSize]  // Retour au premier coin pour fermer le carré
        ];
        
        // Appliquer la rotation des points
        $rotatedPoints = $this->rotatePoints($points, $x, $y, $angle);
    
        // Déplacer vers le premier coin après rotation
        $this->writeLine("G0 X" . $rotatedPoints[0]['x'] . " Y" . $rotatedPoints[0]['y'] . " ; Move to first corner");
    
        // Descendre à la profondeur de coupe
        $this->writeLine("G1 Z-1.0 F500 ; Lower to cutting depth");
    
        // Dessiner les côtés du carré
        for ($i = 1; $i < count($rotatedPoints); $i++) {
            $this->writeLine("G1 X" . $rotatedPoints[$i]['x'] . " Y" . $rotatedPoints[$i]['y'] . " F300 ; Draw side");
        }
    
        // Remonter à une hauteur sécuritaire
        $this->writeLine("G1 Z5.0 F500 ; Raise to safe height");
    }
    

    private function writeRectangleToGcode($x, $y, $width, $height, $angle) {
        // Calculer la moitié des dimensions du rectangle
        $halfWidth = $width / 2;
        $halfHeight = $height / 2;
    
        // Calcul des 4 coins avant rotation
        $points = [
            ['x' => $x - $halfWidth, 'y' => $y - $halfHeight], // coin supérieur gauche
            ['x' => $x + $halfWidth, 'y' => $y - $halfHeight], // coin supérieur droit
            ['x' => $x + $halfWidth, 'y' => $y + $halfHeight], // coin inférieur droit
            ['x' => $x - $halfWidth, 'y' => $y + $halfHeight], // coin inférieur gauche
        ];
    
        // Appliquer la rotation à chaque point
        $rotatedPoints = $this->rotatePoints($points, $x, $y, $angle);
    
        // Commencer à dessiner le rectangle
        $this->writeLine("G0 X" . $rotatedPoints[0]['x'] . " Y" . $rotatedPoints[0]['y'] . " ; Move to first corner");
        $this->writeLine("G1 Z-1.0 F500 ; Lower to cutting depth");
    
        // Dessiner les côtés du rectangle
        $this->writeLine("G1 X" . $rotatedPoints[1]['x'] . " Y" . $rotatedPoints[1]['y'] . " F300 ; Draw top side");
        $this->writeLine("G1 X" . $rotatedPoints[2]['x'] . " Y" . $rotatedPoints[2]['y'] . " ; Draw right side");
        $this->writeLine("G1 X" . $rotatedPoints[3]['x'] . " Y" . $rotatedPoints[3]['y'] . " ; Draw bottom side");
        $this->writeLine("G1 X" . $rotatedPoints[0]['x'] . " Y" . $rotatedPoints[0]['y'] . " ; Draw left side");
    
        $this->writeLine("G1 Z5.0 F500 ; Raise to safe height");
    }

    private function writeTriangleToGcode($x, $y, $size, $angle) {
        $halfSize = $size / 2;
    
        // Définir les trois points du triangle
        $points = [
            ['x' => $x, 'y' => $y - $halfSize], // Point du sommet du triangle (au-dessus)
            ['x' => $x - $halfSize, 'y' => $y + $halfSize], // Coin inférieur gauche
            ['x' => $x + $halfSize, 'y' => $y + $halfSize], // Coin inférieur droit
            ['x' => $x, 'y' => $y - $halfSize] // Retour au sommet pour fermer le triangle
        ];
    
        // Appliquer la rotation des points
        $rotatedPoints = $this->rotatePoints($points, $x, $y, $angle);
    
        // Déplacer vers le premier point après rotation (sommet)
        $this->writeLine("G0 X" . $rotatedPoints[0]['x'] . " Y" . $rotatedPoints[0]['y'] . " ; Move to top point");
    
        // Descendre à la profondeur de coupe
        $this->writeLine("G1 Z-1.0 F500 ; Lower to cutting depth");
    
        // Dessiner les côtés du triangle
        for ($i = 1; $i < count($rotatedPoints); $i++) {
            $this->writeLine("G1 X" . $rotatedPoints[$i]['x'] . " Y" . $rotatedPoints[$i]['y'] . " F300 ; Draw side");
        }
    
        // Remonter à une hauteur sécuritaire
        $this->writeLine("G1 Z5.0 F500 ; Raise to safe height");
    }

    public function calculateHexagonPoints($x, $y, $toolSize) {
        $radius = $toolSize / 2; // Le rayon du cercle circonscrit
        $angleStep = 60; // Chaque angle entre les sommets est de 60 degrés

        $points = [];
        for ($i = 0; $i < 6; $i++) {
            $angle = deg2rad($i * $angleStep); // Convertir l'angle en radians
            $points[] = [
                'x' => $x + $radius * cos($angle), // Calcul de la coordonnée x
                'y' => $y + $radius * sin($angle)  // Calcul de la coordonnée y
            ];
        }
        
        return $points;
    }

    private function writeHexagonToGcode($x, $y, $toolSize, $angle) {
        // Calcul des points de l'hexagone
        $points = $this->calculateHexagonPoints($x, $y, $toolSize);
        
        // Appliquer la rotation aux points
        $rotatedPoints = $this->rotatePoints($points, $x, $y, $angle);

        // Déplacer vers le premier point après rotation
        $this->writeLine("G0 X" . $rotatedPoints[0]['x'] . " Y" . $rotatedPoints[0]['y'] . " ; Move to first corner");

        // Descendre à la profondeur de coupe
        $this->writeLine("G1 Z-1.0 F500 ; Lower to cutting depth");

        // Dessiner les côtés de l'hexagone
        for ($i = 1; $i < count($rotatedPoints); $i++) {
            $this->writeLine("G1 X" . $rotatedPoints[$i]['x'] . " Y" . $rotatedPoints[$i]['y'] . " F300 ; Draw side");
        }

        // Revenir au premier point pour fermer l'hexagone
        $this->writeLine("G1 X" . $rotatedPoints[0]['x'] . " Y" . $rotatedPoints[0]['y'] . " ; Close hexagon");

        // Remonter à une hauteur sécuritaire
        $this->writeLine("G1 Z5.0 F500 ; Raise to safe height");
    }    
    

    // Ferme le fichier code une fois que toutes les entités ont été ajoutées
    public function closeGcodeFile()
    {
        $this->writeLine("M05 ; Stop spindle");
        $this->writeLine("G1 Z5.0 F500 ; Raise to safe height");
        $this->writeLine("G0 X0 Y0 ; Return to home position");
        $this->writeLine("M30 ; End program");
        fclose($this->gcodeFile);
    }

    private function rotatePoints($points, $cx, $cy, $angle) {
        // Convertir l'angle de degrés en radians
        $rad = deg2rad($angle);
        
        $rotatedPoints = [];
        
        // Appliquer la rotation à chaque point
        foreach ($points as $point) {
            $x = $point['x'];
            $y = $point['y'];
            
            // Calcul des nouvelles coordonnées après rotation
            $newX = $cx + ($x - $cx) * cos($rad) - ($y - $cy) * sin($rad);
            $newY = $cy + ($x - $cx) * sin($rad) + ($y - $cy) * cos($rad);
            
            // Ajouter le point tourné à la liste des nouveaux points
            $rotatedPoints[] = ['x' => $newX, 'y' => $newY];
        }
        
        return $rotatedPoints;
    }
    
}
