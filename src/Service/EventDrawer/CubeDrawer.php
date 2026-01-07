<?php

namespace App\Service\EventDrawer;

use App\Model\Cube\CubeInterface;
use App\Service\Scramble\ScrambleGeneratorInterface;
use function DeepCopy\deep_copy;

final class CubeDrawer implements CubeDrawerInterface
{

    public function drawScramble(string $scramble, CubeInterface $objcube): CubeInterface
    {

        //Récupérations des mouvements du scramble
        $matches = [];
        $occur = preg_match_all('/[^\s]+/', $scramble, $matches);
        $n = $objcube->getNSize();
        $cubeScrambled = $objcube->getCube();

        if ($occur > 0) {
            foreach ($matches[0] as $match) {

                $moveType = self::NORMAL;
                $deep = 1;

                //L'index de l'information du mouvement.
                $indexOfMove = 0;
                //S'il s'agit d'un wide move (4x4+)
                if (str_contains($match, ScrambleGeneratorInterface::WIDE)) {

                    //S'il s'agit d'un wide move de 3 tranches (6x6+) Doit être plus robuste à l'avenir pour générer des mélanges de 9x9 par exemple.
                    if ($match[0] == ScrambleGeneratorInterface::TRIPLEWIDE) {
                        //Car 3Xw2 -> X est en position 1.
                        $indexOfMove = 1;
                        $deep = 3;
                    } else {
                        $deep = 2;
                    }
                }

                if (str_contains($match, ScrambleGeneratorInterface::APOSTROPHE)) {
                    $moveType = self::REVERSE;
                }

                //On ne regarde pas le premier char qui peut être un nombre (3Lw2 par exemple).
                if (str_contains(substr($match, 1), ScrambleGeneratorInterface::DOUBLE)) {
                    $moveType = self::DOUBLE;
                }

                //Permutation des stickers de la face principale puis des 4 faces adjs.
                $cubeScrambled[$match[$indexOfMove]] = self::permuteStickersOnFace($cubeScrambled[$match[$indexOfMove]], $moveType, $n);
                $cubeScrambled = self::permuteStickersAdj($cubeScrambled, $match[$indexOfMove], $moveType, $deep, $n);
            }
        }
        $objcube->setCube($cubeScrambled);
        return $objcube;
    }




    /**
     * fonction utilitaire pour gérer la permutation des stickers sur la face principale.
     * @param array $face la face à modifier.
     * @param string $moveType le type de mouvement à réaliser.
     * @param int $n la taille du cube.
     * @return array la même face modifiée.
     */
    private static function permuteStickersOnFace(array $face, string $moveType, int $n): array
    {

        //On garde l'état initial pour calculer les pièces une à une.
        $beforeTurnState = deep_copy($face);

        switch ($moveType) {

            case self::NORMAL:

                for ($y = 0; $y < $n; $y++) {
                    for ($x = 0; $x < $n; $x++) {
                        $newX = $n - 1 - $y;
                        $newY = $x;

                        $face[($newY * $n) + $newX] = $beforeTurnState[($y * $n)  + $x];
                    }
                }
                break;

            case self::REVERSE:

                for ($y = 0; $y < $n; $y++) {
                    for ($x = 0; $x < $n; $x++) {
                        $newX = $y;
                        $newY = $n - 1 - $x;

                        $face[($newY * $n) + $newX]  = $beforeTurnState[($y * $n)  + $x];
                    }
                }
                break;
            case self::DOUBLE:

                for ($y = 0; $y < $n; $y++) {
                    for ($x = 0; $x < $n; $x++) {
                        $newX = $n - 1 - $x;
                        $newY = $n - 1 - $y;

                        $face[($newY * $n) + $newX]  = $beforeTurnState[($y * $n)  + $x];
                    }
                }
                break;
        }
        return $face;
    }

    /**
     * fonction utilitaire pour gérer la permutation des stickers sur les faces adjacentes au mouvement.
     * @param array $cube pour gérer les faces adjacentes du cube.
     * @param string $moveToDo le mouvement à réaliser.
     * @param string $moveType S'il s'agit d'un mouvement amélioré Inverse ou double.
     * @param int $deep la profondeur du mouvement à réaliser. Ex: Lw' -> deep = 2.
     * @param int $n la taille du cube.
     * @return array le même cube modifié. 
     */
    private function permuteStickersAdj(array $cube, string $moveToDo, string $moveType, int $deep, int $n): array
    {

        //On garde l'état initial pour calculer les pièces une à une.
        $cube2 = deep_copy($cube);

        //Récupération des spécificités du mouvement à réaliser.
        $getSpecif = self::FACENEIGHBORS[$moveToDo];

        //On boucle sur les 4 faces adjacentes.
        for ($i = 0; $i < count($getSpecif); $i++) {

            $faceOfLeavedStickers = $cube2[$getSpecif[$i]["face"]];
            $type = $getSpecif[$i]["type"];
            $index = $getSpecif[$i]["index"];

            //Les stickers de la face courante à permuter pourrait être inversée sur la face destination.
            $isReverse =  in_array($moveType, $getSpecif[$i]["inverse"]);

            //On détermine si on bouge la première/dernière colonne/ligne de la face.
            //On profite du type mixed.
            if ($index !== '0') {
                $index = $n - 1;
            } else {
                $index = 0;
            }

            //Création de la liste des stickers à permuter.
            $stickersToMove = self::stickersToTake($faceOfLeavedStickers, $index, $deep, $isReverse, $type, $n);

            $indexFaceToUpdate = 0;

            //On recherche la face de destination des stickers de la face courante.
            if ($moveType === self::DOUBLE) {
                $indexFaceToUpdate = ($i + 2);
            } else if ($moveType === self::REVERSE) {
                $indexFaceToUpdate = ($i - 1);
            } else {
                $indexFaceToUpdate = ($i + 1);
            }

            //Dans le cas où $i = 0 et que le mouvement est 'REVERSE'. (-1 % 4 = -1 en PHP)
            if ($indexFaceToUpdate === -1) {
                $indexFaceToUpdate = 3;
            }

            //Recupération des infos de la face destination.
            $faceToUpdate = $getSpecif[$indexFaceToUpdate % 4]["face"];
            $typeFaceToUpdate =  $getSpecif[$indexFaceToUpdate % 4]["type"];
            $indexFaceToUpdate = $getSpecif[$indexFaceToUpdate % 4]["index"];

            if ($indexFaceToUpdate !== '0') {
                $indexFaceToUpdate = $n - 1;
            } else {
                $indexFaceToUpdate = 0;
            }

            //On affecte les valeurs dans sur la face de destination.
            $cube[$faceToUpdate] = self::stickersToUpdate($cube2[$faceToUpdate], $stickersToMove, $indexFaceToUpdate, $deep, $typeFaceToUpdate, $n);
        }

        return $cube;
    }

     /**
     * Fonction pour la récupération de stickers.
     * 
     * @param array $face la face concernée à update
     * @param int $index le départ du premier sticker à modifier.
     * @param int $deep la profondeur du mouvement à faire.
     * @param bool $isReverse dans le cas d'une récupération, savoir si les stickers doivent être inversés sur chaque ligne/colonne de destination.
     * @param string $type s'il s'agit d'une modification d'une colonne ou d'une ligne de la face.
     * @param string $n la taille du cube.
     * @return array les stickers de la face à réaffecter.
     */
    private function stickersToTake(array $face, int $index, int $deep, bool $isReverse, string $type, int $n): array
    {
        $stickers = [];
        $i = self::defDeparture($type, $index, $n); 
        $acc = 0;

        //La ligne ou la colonne courante.
        $currentRowOrCol = [];

        while ($acc < ($n * $deep)) {

            array_push($currentRowOrCol, $face[$i]);
            
            $acc++;
           
             //Traitement à réaliser lorsque la colonne/ligne est terminée.
            if ($acc !== 0 && $acc % $n === 0) {
                
                if ($isReverse) {
                    $currentRowOrCol = array_reverse($currentRowOrCol);
                }

                array_push($stickers,$currentRowOrCol);
                $currentRowOrCol = [];

                
                //On se décale pour accéder la nouvelle ligne/Colonne.
                //Lors de la dernière itération, $i aura une valeur hors plage -> c'est normal.
                $i += self::newIPos($type,$index,$n);
            } else {

                //Dans le cas d'une colonne -> (x,y + 1)
                if ($type === 'col') {
                    $i += $n;
                } else {
                    $i++;
                }
            }

            
        }

        return $stickers;
    }

    /**
     * Fonction pour la réaffectation des stickers.
     * 
     * @param array $face la face concernée à update
     * @param array $stickersToReaffect Un tableau de lignes ou colonnes de stickers.
     * @param int $deep la profondeur du mouvement à faire.
     * @param string $type s'il s'agit de la modification d'une colonne ou d'une ligne de la face.
     * @param string $n la taille du cube.
     * @return array la face modifiée avec les stickers qui devaient être réaffectées.
     */
    private function stickersToUpdate(array $face, array $stickersToReaffect, int $index, int $deep, string $type, int $n): array
    {
        $i = self::defDeparture($type, $index, $n);
        $acc = 0;

        while ($acc < ($n * $deep)) {

            //On va chercher le bon sticker dans la bonne ligne/colonne.
            $numberRowOrCol = intdiv($acc,$n);            
            $face[$i] = $stickersToReaffect[$numberRowOrCol][$acc - ($numberRowOrCol * $n)];

            $acc++;

            //Traitement à réaliser lorsque la colonne/ligne est terminée.
            if ($acc !== 0 && $acc % $n === 0) {
                    
                //On se décale pour accéder la nouvelle ligne/Colonne.
                //Lors de la dernière itération, $i aura une valeur hors plage -> c'est normal.
                $i += self::newIPos($type, $index, $n);

            } else {
                //Dans le cas d'une colonne -> (x,y + 1)
                if ($type === 'col') {
                    $i += $n;
                } else {
                    $i++;
                }
            } 
        }
        return $face;
    }

    /**
     * Fonction utilitaire pour des récupérations/réaffectations de stickers.
     * 
     * @param $type le type de donnée
     * @param $index le commencement de la donnée
     * @param $n la taille du cube
     * 
     * @return int la position de départ pour commencer la boucle.
     */
    private function defDeparture(string $type, int $index,int $n) : int {
         
        //Si on est dans une dernière colonne, on se place en (n-1,0);
        if ($type === 'col' && $index === $n - 1) {
            return $index;
        }

        //Si on est dans une dernière ligne, on se place en (0,n-1);
        if ($type === 'row' && $index === $n - 1) {
            return $n * ($n - 1);
        }

        return 0;
    }

    /**
     * Fonction utilitaire pour les récupérations/réaffections de stickers.
     * 
     * @param $type le type de donnée
     * @param $index le commencement de la donnée
     * @param $n la taille du cube
     * 
     * @return int le décalage à réaliser.
     */
    private function newIPos(string $type, int $index, int $n) : int {

        //On se place: 

        //Sur le début de la colonne suivante.
        if ($type === 'col' && $index === 0) {
            return - ((($n - 1) * $n) - 1);
        //Sur le début de la colonne précédente.
        } else if ($type === 'col' && $index === $n - 1) {
           return - ((($n - 1) * $n) + 1);
        
        //Sur le début de la ligne suivante.
        } else if ($type === 'row' && $index === 0) {
            return 1;

        //sur le début de la ligne précédente.
        } else {
            return - ((2 * $n) - 1);
        }
    }

}
