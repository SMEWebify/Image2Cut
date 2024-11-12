<?php

namespace App\Services;

use App\Services\FunctionService;

class DxfService
{
    protected $functionService ;

    public function __construct(FunctionService $functionService , ){
        $this->functionService  = $functionService ;
    }
    private $dxfFile;

    // Sauvegarde du fichier DXF en initialisant la structure de base
    public function saveDXFFile($filename)
    {
        // Ouvre le fichier en mode écriture (si le fichier n'existe pas, il sera créé)
        $this->dxfFile = fopen($filename, 'w');

        if ($this->dxfFile === false) {
            throw new \Exception("Impossible d'ouvrir le fichier DXF pour écriture.");
        }

        // Initialisation de la structure DXF (version, header, etc.)
        fwrite($this->dxfFile, "0\nSECTION\n2\nHEADER\n0\nENDSEC\n0\nSECTION\n2\nTABLES\n0\nENDSEC\n");
        fwrite($this->dxfFile, "0\nSECTION\n2\nBLOCKS\n0\nENDSEC\n0\nSECTION\n2\nENTITIES\n");
    }

    // Dessine une forme à une position donnée dans le fichier DXF
    public function drawShapeAtPositionDXF($resizedImage, $x, $y, $toolSizes, $shape, &$positions, $angle = null, $ignoreThreshold, $invert, $pen)
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

        // Ajouter la forme dans le fichier DXF
        switch ($shape) {
        
            case 'circle':
                $radius = $toolSize / 2;
                $this->writeCircleToDXF($x, -$y, $radius, $angle, $pen);
                break;
            case 'square':
                $this->writeSquareToDXF($x, -$y, $toolSize, $angle, $pen);
                break;
            case 'rectangle':
                $this->writeRectangleToDXF($x, -$y, $toolSize*2, $toolSize,$angle, $pen);
                break;
            case 'triangle':
                $this->writeTriangleToDXF($x, -$y, $toolSize, $angle, $pen);
                break;
            case 'hexagon':
                // Calcul des points pour un hexagone
                $points = $this->functionService->calculateHexagonPoints($toolSize);
                $this->writeHexagonToDXF($x, -$y, $toolSize, $angle, $pen);
                break;
        }
        
        // Ajouter la position de la forme dessinée
        $positions[] = ['x' => $x, 'y' => $y, 'size' => $toolSize];

        // On met à jour le fichier DXF
        return $toolSize;
    }


    // Fonction pour écrire un cercle dans le fichier DXF (tracé d'un cercle)
    private function writeCircleToDXF($x, $y, $radius, $angle = null, $pen)
    {
        // Écrire les entités DXF pour un cercle (avec une position et un rayon)
        fwrite($this->dxfFile, "0\nCIRCLE\n");
        fwrite($this->dxfFile, "8\n0\n"); // Layer 0
        fwrite($this->dxfFile, "6\nCONTINUOUS\n"); // Style de ligne "CONTINUOUS"
        fwrite($this->dxfFile, "62\n" . $pen ."\n"); // Code couleur, ici 1 correspond à la couleur rouge (selon le standard DXF)
        fwrite($this->dxfFile, "10\n" . $x . "\n"); // Coordonnée X
        fwrite($this->dxfFile, "20\n" . $y . "\n"); // Coordonnée Y
        fwrite($this->dxfFile, "30\n0.0\n"); // Coordonnée Z (0 pour un plan 2D)
        fwrite($this->dxfFile, "40\n" . $radius . "\n");
        fwrite($this->dxfFile, "0"); // Fin de l'entité
    }

    private function writeSquareToDXF($x, $y, $size, $angle, $pen) { 
        // Génère un carré au format DXF (les coordonnées des 4 points après rotation)
        $halfSize = $size / 2;
    
        // Calcul des points avec rotation
        $points = $this->rotatePoints([
            ['x' => $x - $halfSize, 'y' => $y - $halfSize],
            ['x' => $x + $halfSize, 'y' => $y - $halfSize],
            ['x' => $x + $halfSize, 'y' => $y + $halfSize],
            ['x' => $x - $halfSize, 'y' => $y + $halfSize],
            ['x' => $x - $halfSize, 'y' => $y - $halfSize],
        ], $x, $y, $angle);
    
        return $this->generateDXFPolygon($points, $pen);
    }

    private function writeRectangleToDXF($x, $y, $width, $height, $angle, $pen) {
        // Génère un rectangle au format DXF (les coordonnées des 4 points après rotation)
        $halfWidth = $width / 2;
        $halfHeight = $height / 2;
        
        // Calcul des points avec rotation
        $points = $this->rotatePoints([
            ['x' => $x - $halfWidth, 'y' => $y - $halfHeight], // coin supérieur gauche
            ['x' => $x + $halfWidth, 'y' => $y - $halfHeight], // coin supérieur droit
            ['x' => $x + $halfWidth, 'y' => $y + $halfHeight], // coin inférieur droit
            ['x' => $x - $halfWidth, 'y' => $y + $halfHeight], // coin inférieur gauche
            ['x' => $x - $halfWidth, 'y' => $y - $halfHeight], // retour au coin supérieur gauche
        ], $x, $y, $angle);

        return $this->generateDXFPolygon($points, $pen);
    }

    private function  writeTriangleToDXF($x, $y, $size, $angle, $pen) {
        // Génère un triangle au format DXF (les coordonnées des 3 points après rotation)
        $halfSize = $size / 2;
        $points = [
            ['x' => $x, 'y' => $y - $halfSize], // Sommet supérieur
            ['x' => $x - $halfSize, 'y' => $y + $halfSize], // Coin inférieur gauche
            ['x' => $x + $halfSize, 'y' => $y + $halfSize], // Coin inférieur droit
            ['x' => $x, 'y' => $y - $halfSize], // On ferme au sommet supérieur
        ];
    
        $rotatedPoints = $this->rotatePoints($points, $x, $y, $angle);
    
        return $this->generateDXFPolygon($rotatedPoints, $pen);
    }

    private function writeHexagonToDXF($x, $y, $toolSize, $angle, $pen) {
        // Calcul des points de l'hexagone
        $points = $this->calculateHexagonPoints($x, $y, $toolSize);
        
        // Appliquer la rotation aux points
        $rotatedPoints = $this->rotatePoints($points, $x, $y, $angle);
    
        // Générer l'hexagone en DXF avec les points tournés
        return $this->generateDXFPolygon($rotatedPoints, $pen);
    }

    public function calculateHexagonPoints($x, $y, $toolSize) {
        $radius = $toolSize / 2; // Le rayon du cercle circonscrit
        $angleStep = 60; // Chaque angle entre les sommets est de 60 degrés

        $points = [];
        for ($i = 0; $i < 7; $i++) {
            $angle = deg2rad($i * $angleStep); // Convertir l'angle en radians
            $points[] = [
                'x' => $x + $radius * cos($angle), // Calcul de la coordonnée x
                'y' => $y + $radius * sin($angle)  // Calcul de la coordonnée y
            ];
        }

        return $points;
    }

    private function generateDXFPolygon($points, $pen) {
        // Génère un polygone DXF à partir d'une liste de points
        $dxfData = "0\nPOLYLINE\n8\n0\n66\n1\n62\n{$pen}\n";
        foreach ($points as $point) {
            $dxfData .= "0\nVERTEX\n8\n0\n10\n{$point['x']}\n20\n{$point['y']}\n30\n0.0\n";
        }
        $dxfData .= "0\nSEQEND\n";
        fwrite($this->dxfFile,  $dxfData);
        return $dxfData;
    }

    private function rotatePoints($points, $cx, $cy, $angle) {
        $rad = deg2rad($angle);
        $cos = cos($rad);
        $sin = sin($rad);
    
        foreach ($points as &$point) {
            $dx = $point['x'] - $cx;
            $dy = $point['y'] - $cy;
    
            $point['x'] = $cx + ($dx * $cos - $dy * $sin);
            $point['y'] = $cy + ($dx * $sin + $dy * $cos);
        }
    
        return $points;
    }

    // Ferme le fichier DXF une fois que toutes les entités ont été ajoutées
    public function closeDXFFile()
    {
        // Ferme la section des entités
        fwrite($this->dxfFile, "0\nENDSEC\n");

        // Section pour les objets (vide pour l'instant)
        fwrite($this->dxfFile, "0\nSECTION\n2\nOBJECTS\n0\nENDSEC\n");

        // Ferme le fichier DXF
        fwrite($this->dxfFile, "0\nEOF\n");
        fclose($this->dxfFile);
    }
    
}
