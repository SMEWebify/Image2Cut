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
    public function drawShapeAtPositionGCode($resizedImage, $x, $y, $toolSizes, $shape, &$positions, $angle = null, $ignoreThreshold)
    {
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

        // Ajouter la forme dans le fichier Gcode
        switch ($shape) {
            case 'circle':
                $radius = $toolSize / 2;
                $this->writeCircleToGcode($x, -$y, $radius, $angle);
                break;
            case 'square':
                $this->writeSquareToGcode($x, -$y, $toolSize, $angle);
                break;
            case 'triangle':
                $this->writeTriangleToGcode($x, -$y, $toolSize, $angle);
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
        $this->writeLine("G0 X" . ($x - $halfSize) . " Y" . ($y - $halfSize) . " ; Move to first corner");
        $this->writeLine("G1 Z-1.0 F500 ; Lower to cutting depth");

        // Dessiner les côtés du carré
        $this->writeLine("G1 X" . ($x + $halfSize) . " Y" . ($y - $halfSize) . " F300 ; Draw side");
        $this->writeLine("G1 X" . ($x + $halfSize) . " Y" . ($y + $halfSize) . " ; Draw side");
        $this->writeLine("G1 X" . ($x - $halfSize) . " Y" . ($y + $halfSize) . " ; Draw side");
        $this->writeLine("G1 X" . ($x - $halfSize) . " Y" . ($y - $halfSize) . " ; Draw side");

        $this->writeLine("G1 Z5.0 F500 ; Raise to safe height");
    }

    private function  writeTriangleToGcode($x, $y, $size, $angle) {
        $halfSize = $size / 2;

        $this->writeLine("G0 X$x Y" . ($y - $halfSize) . " ; Move to top point");
        $this->writeLine("G1 Z-1.0 F500 ; Lower to cutting depth");

        $this->writeLine("G1 X" . ($x - $halfSize) . " Y" . ($y + $halfSize) . " F300 ; Draw side");
        $this->writeLine("G1 X" . ($x + $halfSize) . " Y" . ($y + $halfSize) . " ; Draw side");
        $this->writeLine("G1 X$x Y" . ($y - $halfSize) . " ; Draw side");

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
    
}
