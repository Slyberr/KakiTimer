<?php

namespace App\Service\Cube;

use App\Service\DrawCube\CubeDrawerInterface;
use App\Service\Scramble\ScrambleGeneratorInterface;

use function DeepCopy\deep_copy;

abstract class AbstractCubeDrawer implements CubeDrawerInterface
{

    //Function pour dessiner le patron d'un cube
    public function drawScramble(string $scramble, array $cube, int $n): array
    {
        $matches = [];
        $occur = preg_match_all('/[^\s]+/', $scramble, $matches);

        if ($occur > 0) {
            foreach ($matches[0] as $match) {

                $moveType = self::NORMAL;

                if (str_contains($match, ScrambleGeneratorInterface::APOSTROPHE)) {
                    $moveType = self::REVERSE;
                }

                if (str_contains($match, ScrambleGeneratorInterface::DOUBLE)) {
                    $moveType = self::DOUBLE;
                }

                $cube[$match[0]] = self::permuteStickersOnFace($cube[$match[0]], $moveType, $n);
                $cube = self::permuteStickersAdj($cube, $match[0], $moveType, 0, $n);
            }
        }
        return $cube;
    }




    //Permutation des stickers de la face pricipale. 
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

    //Permutation des stickers des faces adjacentes.
    private function permuteStickersAdj(array $cube, string $moveToDo, string $moveType, string $deep, int $n): array
    {

        //On garde l'état initial pour calculer les pièces une à une.
        //Même pour un 7x7, cela reste efficace en terme de performance.
        $cube2 = deep_copy($cube);

        //Récupération des spécificités du mouvement à réaliser.
        $getSpecif = self::FACENEIGHBORS[$moveToDo];

        //On boucle sur les 4 faces adjacentes.
        for ($i = 0; $i < count($getSpecif); $i++) {

            //Recupération des infos de la face courante.
            $faceOfLeavedStickers = $cube2[$getSpecif[$i]["face"]];
            $type = $getSpecif[$i]["type"];
            $index = $getSpecif[$i]["index"];

            //Si oui la liste des stickers de la face courante à permuter devra être inversée sur la face destination.
            $isReverse =  array_search($moveType, $getSpecif[$i]["inverse"]);

            //On détermine si on bouge la première/dernière colonne/ligne de la face.
            //On profite du type mixed.
            if ($index != '0') {
                $index = $n - 1;
            }

            //Création de la liste des stickers à permuter.
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


            //On profite du type mixed.
            if ($indexFaceToUpdate != '0') {
                $indexFaceToUpdate = $n - 1;
            }

            //On affecte les valeurs dans sur la face de destination.
            $cube[$faceToUpdate] = self::stickersToReaffect($cube2[$faceToUpdate], $stickersToMove, $indexFaceToUpdate, $typeFaceToUpdate, $n);
        }

        return $cube;
    }

    //Récupération des stickers à permuter.
    private function stickersToMove(array $face, int $index, string $type, int $n): array
    {
        return self::stickersToTakeOrUpdate($face, null, $index, $type, $n);
    }

    //Réaffectation des stickers sur la face destination.
    private function stickersToReaffect(array $face, array $stickersToReaffect, int $index, string $type, int $n): array
    {
        return self::stickersToTakeOrUpdate($face, $stickersToReaffect, $index, $type, $n);  
    }

    //Fonction commune pour la récupération et la réaffectation des stickers.
    private function stickersToTakeOrUpdate(array $face,?array $listOfStickers, int $index, string $type, int $n,): array
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
            if($listOfStickers !== null) {
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

        if($listOfStickers === null) {
            return $stickers;
        }else {
            return $face;
        }
    }
}