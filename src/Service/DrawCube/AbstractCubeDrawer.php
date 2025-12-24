<?php

namespace App\Service\Cube;

use App\Service\DrawCube\CubeDrawerInterface;
use App\Service\Scramble\ScrambleGeneratorInterface;

use function DeepCopy\deep_copy;

abstract class AbstractCubeDrawer implements CubeDrawerInterface
{

    public function drawScramble(string $scramble, array $cube, int $n): array
    {

        $matches = [];
        $occur = preg_match_all('/[^\s]+/', $scramble, $matches);

        if ($occur > 0) {
            foreach ($matches[0] as $match) {



                $face = $cube[$match[0]];
                if (str_contains($match, ScrambleGeneratorInterface::APOSTROPHE)) {

                    $cube[$match[0]] = self::principalFaceTurn($face, self::REVERSE, $n);
                    $cube = self::stickersChangeFace($cube, $match[0], self::REVERSE, 0, $n);
                } else if (str_contains($match, ScrambleGeneratorInterface::DOUBLE)) {

                    $cube[$match[0]] = self::principalFaceTurn($face, self::DOUBLE, $n);
                    $cube = self::stickersChangeFace($cube, $match[0], self::DOUBLE, 0, $n);
                } else {

                    $cube[$match[0]] = self::principalFaceTurn($face, self::NORMAL, $n);
                    $cube = self::stickersChangeFace($cube, $match[0], self::NORMAL, 0, $n);
                }
            }
        }

        return $cube;
    }

    //Permutation des stickers associés sur la face pricipale 
    private static function principalFaceTurn(array $face, string $moveType, int $n): array
    {

        //On garde l'état initial pour calculer les pièces une à une.
        $beforeTurnState = deep_copy($face);

        switch ($moveType) {

            case self::REVERSE:

                for ($y = 0; $y < $n; $y++) {
                    for ($x = 0; $x < $n; $x++) {
                        //On calcul la nouvelle position du sticker selon le mouvement réalisé
                        $newX = $y;
                        $newY = $n - 1 - $x;

                        $face[($newY * $n) + $newX]  = $beforeTurnState[($y * $n)  + $x];
                    }
                }
                break;
            case self::DOUBLE:

                for ($y = 0; $y < $n; $y++) {
                    for ($x = 0; $x < $n; $x++) {
                        //On calcul la nouvelle position du sticker selon le mouvement réalisé

                        $newX = $n - 1 - $x;
                        $newY = $n - 1 - $y;

                        $face[($newY * $n) + $newX]  = $beforeTurnState[($y * $n)  + $x];
                    }
                }
                break;
            default:

                for ($y = 0; $y < $n; $y++) {
                    for ($x = 0; $x < $n; $x++) {

                        //On calcul la nouvelle position du sticker selon le mouvement réalisé

                        $newX = $n - 1 - $y;
                        $newY = $x;

                        $face[($newY * $n) + $newX] = $beforeTurnState[($y * $n)  + $x];
                    }
                }
                break;
        }
        return $face;
    }

    private function stickersChangeFace(array $cube, string $moveToDo, string $moveType, string $deep, int $n): array
    {
        $cube2 = deep_copy($cube);

        //Récupération des instructions à réaliser pour ce mouvement.
        $getSpecif = self::FACENEIGHBORS[$moveToDo];

        for ($i = 0; $i < count($getSpecif) ; $i++) {

            //Recupération d'un tableau (face) concerné par le changement
            $faceOfLeavedStickers = $cube2[$getSpecif[$i]["face"]];
            $type = $getSpecif[$i]["type"];
            $index = $getSpecif[$i]["index"];

            //Si oui le tableau à renseigner dans la nouvelle face doit être inversé.
            $isReverse =  array_search($moveType, $getSpecif[$i]["inverse"]);
            $realIndex = 0;

            //On détermine si on bouge la première/dernière colonne/ligne de la face.
            if ($index != '0') {
                $realIndex = $n - 1;
            }

            //création du tableau de stickers à bouger.
            $stickersToMove = self::stickersToMove($faceOfLeavedStickers, $realIndex, $type, $n);

           
            // Triple opérateur voir :
            // https://www.php.net/manual/fr/function.array-search.php -> User Contributed Notes ->  cue at openxbox dot com

            if ($isReverse !== false) {
                $stickersToMove = array_reverse($stickersToMove);
            }

            $indexFaceToUpdate = 0;

            //On recherche la face de destination des stickers.
            if ($moveType == self::DOUBLE) {
                $indexFaceToUpdate = ($i + 2);
            } else if ($moveType == self::REVERSE) {
                $indexFaceToUpdate = ($i - 1);
            } else {
                $indexFaceToUpdate = ($i + 1);
            }

            //Le modulo en PHP n'apprécie pas les nombres négatifs..
            if ($indexFaceToUpdate == -1) {
                $indexFaceToUpdate = 3;
            }
            $faceToUpdate = $getSpecif[$indexFaceToUpdate % 4]["face"];
            $typeFaceToUpdate =  $getSpecif[$indexFaceToUpdate % 4]["type"];
            $indexFaceToUpdate = $getSpecif[$indexFaceToUpdate % 4]["index"];

            $realIndexFaceToUpdate = 0;

            if ($indexFaceToUpdate != '0') {
                $realIndexFaceToUpdate = $n - 1;
            }


            //On affecte les valeurs dans sur la face de destination.
            $cube[$faceToUpdate] = self::stickersToReaffect($cube2[$faceToUpdate], $stickersToMove, $realIndexFaceToUpdate, $typeFaceToUpdate, $n);
        }
        return $cube;
    }

    private function stickersToMove(array $face, int $index, string $type, int $n): array
    {

        $stickersToMove = [];
        $deparure = 0;

        //Si on est dans une dernière colonne, on se place en (n-1,0);
        if ($type == 'col' && $index == $n - 1) {
            $deparure = $index;

            //Si on est dans une dernière ligne, on se plade en (0,n-1);
        }
        if ($type == 'row' && $index == $n - 1) {
            $deparure = $n * ($n - 1);
        }

        $i = $deparure;
        $acc = 0;
        while ($acc < $n) {

            array_push($stickersToMove, $face[$i]);

            //On saute toute la ligne pour arriver à (x,y + 1)
            if ($type == 'col') {
                $i += $n;
            } else {
                $i++;
            }
            $acc++;
        }
        return $stickersToMove;
    }

    private function stickersToReaffect(array $face, array $stickersToReaffect, int $index, string $type, int $n): array
    {


        $deparure = 0;

        //Si on est dans une dernière colonne, on se place en (n-1,0);
        if ($type == 'col' && $index == $n - 1) {
            $deparure = $index;

            //Si on est dans une dernière ligne, on se plade en (0,n-1);
        }
        if ($type == 'row' && $index == $n - 1) {
            $deparure = $n * ($n - 1);
        }

        $i = $deparure;
        $acc = 0;

        while ($acc < $n) {

            $face[$i] = $stickersToReaffect[$acc];

            //On saute toute la ligne pour arriver à (x,$i + 1)
            if ($type == 'col') {
                $i += $n;
            } else {
                $i++;
            }
            $acc++;
        }
        return $face;
    }
}
