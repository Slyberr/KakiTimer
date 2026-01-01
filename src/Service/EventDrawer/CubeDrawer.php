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
                //S'il s'agit d'un wide move (4x4+)
                if (str_contains($match, ScrambleGeneratorInterface::WIDE)) {

                    //S'il s'agit d'un wide move de 3 tranches (6x6+) Doit être plus robuste à l'avenir pour générer des mélanges de 9x9 par exemple.
                    if ($match[0] == ScrambleGeneratorInterface::TRIPLEWIDE) {
                        $deep = 3;
                    } else {
                        $deep = 2;
                    }

                }

                if (str_contains($match, ScrambleGeneratorInterface::APOSTROPHE)) {
                    $moveType = self::REVERSE;
                }

                //On ne regarde pas le premier char qui peut être un nombre (3Lw2 par exemple).
                if (str_contains(substr($match,1), ScrambleGeneratorInterface::DOUBLE)) {
                    $moveType = self::DOUBLE;
                }

                //Permutation des stickers de la face principale puis des 4 faces adjs.
                $cubeScrambled[$match[0]] = self::permuteStickersOnFace($cubeScrambled[$match[0]], $moveType, $n);
                $cubeScrambled = self::permuteStickersAdj($cubeScrambled, $match[0], $moveType, $deep, $n);
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
            $isReverse =  array_search($moveType, $getSpecif[$i]["inverse"]);

            //On détermine si on bouge la première/dernière colonne/ligne de la face.
            //On profite du type mixed.
            if ($index != '0') {
                $index = $n - 1;
            }
            
            //Création de la liste des stickers à permuter.
            for ($j = 0; $j < $deep; $j++) {

                //on change de tranche/pronfondeur à chaque itération.
                if ($index == 0) {
                    self::stickersToMove($faceOfLeavedStickers, $index + $j, $type, $n);
                } else {
                    self::stickersToMove($faceOfLeavedStickers, $index - $j, $type, $n);
                }
            }
            $stickersToMove = self::stickersToMove($faceOfLeavedStickers, $index, $type, $n);

            //Triple opérateur voir :
            //https://www.php.net/manual/fr/function.array-search.php -> User Contributed Notes ->  cue at openxbox dot com
            if ($isReverse !== false) {
                $stickersToMove = array_reverse($stickersToMove);
            }

            $indexFaceToUpdate = "";

            //On recherche la face de destination des stickers de la face courante.
            if ($moveType == self::DOUBLE) {
                $indexFaceToUpdate = ($i + 2);
            } else if ($moveType == self::REVERSE) {
                $indexFaceToUpdate = ($i - 1);
            } else {
                $indexFaceToUpdate = ($i + 1);
            }

            //Dans le cas où $i = 0 et que le mouvement est 'REVERSE'. (-1 % 4 = -1 en PHP)
            if ($indexFaceToUpdate == -1) {
                $indexFaceToUpdate = 3;
            }

            //Recupération des infos de la face destination.
            $faceToUpdate = $getSpecif[$indexFaceToUpdate % 4]["face"];
            $typeFaceToUpdate =  $getSpecif[$indexFaceToUpdate % 4]["type"];
            $indexFaceToUpdate = $getSpecif[$indexFaceToUpdate % 4]["index"];

            if ($indexFaceToUpdate != '0') {
                $indexFaceToUpdate = $n - 1;
            }

            //On affecte les valeurs dans sur la face de destination.
            $cube[$faceToUpdate] = self::stickersToReaffect($cube2[$faceToUpdate], $stickersToMove, $indexFaceToUpdate, $typeFaceToUpdate, $n);
        }

        return $cube;
    }

    /**
     * Récupération des stickers à permuter.
     */
    private function stickersToMove(array $face, int $index, string $type, int $n): array
    {
        return self::stickersToTakeOrUpdate($face, null, $index, $type, $n);
    }

    /**
     * Réaffectation des stickers sur la face destination.
     */
    private function stickersToReaffect(array $face, array $stickersToReaffect, int $index, string $type, int $n): array
    {
        return self::stickersToTakeOrUpdate($face, $stickersToReaffect, $index, $type, $n);
    }

    /**
     * Fonction pour la récupération et la réaffectation des stickers.
     * 
     * @param array $face la face concernée à update
     * @param ?array $listOfStickers, s'il s'agit d'une affectation de stickers, null sinon.
     * @param int $index le départ du premier sticker à modifier.
     * @param string $type s'il s'agit d'une modification d'une colonne ou d'une ligne de la face.
     * @param string $n la taille du cube.
     * @return array la face modifié à retourner.
    */
    private function stickersToTakeOrUpdate(array $face, ?array $listOfStickers, int $index, string $type, int $n): array
    {
        //Si $listOfStickers est null, on récupère les stickers, sinon on les réaffecte.
        $stickers = [];
        if ($listOfStickers !== null) {
            $stickers = $listOfStickers;
        }

        $deparure = 0;

        //Si on est dans une dernière colonne, on se place en (n-1,0);
        if ($type == 'col' && $index == $n - 1) {
            $deparure = $index;
        }

        //Si on est dans une dernière ligne, on se place en (0,n-1);
        if ($type == 'row' && $index == $n - 1) {
            $deparure = $n * ($n - 1);
        }

        $i = $deparure;
        $acc = 0;
        while ($acc < $n) {

            //On récupère ou on réaffecte le sticker.
            if ($listOfStickers !== null) {
                $face[$i] = $stickers[$acc];
            } else {
                array_push($stickers, $face[$i]);
            }

            //On saute toute la ligne pour arriver à (x,y + 1)
            if ($type == 'col') {
                $i += $n;
            } else {
                $i++;
            }
            $acc++;
        }

        if ($listOfStickers === null) {
            return $stickers;
        } else {
            return $face;
        }
    }
}
