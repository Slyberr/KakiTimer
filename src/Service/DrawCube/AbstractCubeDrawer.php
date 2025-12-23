<?php

namespace App\Service\Cube;

use App\Service\DrawCube\CubeDrawerInterface;
use App\Service\Scramble\ScrambleGeneratorInterface;

use function DeepCopy\deep_copy;

abstract class AbstractCubeDrawer implements CubeDrawerInterface
{

    public function draw(string $scramble, array $cube, int $n)
    {

        $matches = [];
        $occur = preg_match_all('/[^\s]+/', $scramble, $matches);

        if ($occur > 0) {
            foreach ($matches[0] as $match) {

                $face = $cube[$match[0]];
                if (str_contains(ScrambleGeneratorInterface::APOSTROPHE,$match)) {
                    self::principalFaceTurn($face,self::REVERSE,$n);
                } else if (str_contains(ScrambleGeneratorInterface::DOUBLE,$match)) {
                    self::principalFaceTurn($face,self::DOUBLE,$n);
                } else {
                    self::principalFaceTurn($face,null,$n);
                }
                
            }
        }
    }

    //Permutation des stickers associés sur la face pricipale 
    private static function principalFaceTurn(array $face, ?string $moveType, int $n)
    {

        //On garde l'état initial pour calculer les pièces une à une.
        $beforeTurnState = deep_copy($face);

        switch ($moveType) {

            case self::REVERSE:

                for ($y = 0; $y < $n; $y++) {
                    for ($x = 0; $x < $n; $x++) {
                        //On calcul la nouvelle position du sticker selon le mouvement réalisé
                        $newX = $n - 1 - $y;
                        $newY = $x;

                        self::affectNewPos($beforeTurnState, $face, $x, $y, $newX, $newY, $n);
                    }
                }
            case self::DOUBLE:

                for ($y = 0; $y < $n; $y++) {
                    for ($x = 0; $x < $n; $x++) {
                        //On calcul la nouvelle position du sticker selon le mouvement réalisé

                        $newX = $n - 1 - $x;
                        $newY = $n - 1 - $y;

                        self::affectNewPos($beforeTurnState, $face, $x, $y, $newX, $newY, $n);
                    }
                }
            default:

                for ($y = 0; $y < $n; $y++) {
                    for ($x = 0; $x < $n; $x++) {

                        //On calcul la nouvelle position du sticker selon le mouvement réalisé

                        $newX = $y;
                        $newY = $n - 1 - $x;

                        self::affectNewPos($beforeTurnState, $face, $x, $y, $newX, $newY, $n);
                    }
                }
        }
    }

    private static function affectNewPos(array $beforeTurnState, array $face, int $x, int $y, int $newX, int $newY, int $n)
    {

        //On accède à la valeur initiale
        $value = $beforeTurnState[$x + $y];
        //On imprime le sticker sur la nouvelle position (y * n + x sur un tableau 1D) 
        $face[($newY * $n) + $newX] =  $value;
    }

    private static function stickersChangeFace(array $cube, string $moveToDo, string $moveType,string $deep, int $n) {

        $cube2 = deep_copy($cube);

        //Récupération des instructions à réaliser pour ce mouvement.
        $getSpecif = self::FACENEIGHBORS[$moveToDo[0]];

        for($i = 0;$i < count($getSpecif); $i++) {

            //Recupération d'un tableau (face) concerné par le changement
            $faceToChange = $cube2[$getSpecif[$i]["face"]];
            $typeofData = $getSpecif[$i]["type"];
            $indexOfData = $getSpecif[$i]["index"];
            $realIndexOfData = 0;

            if ($indexOfData != '0') {
                $realIndexOfData = $n - 1;
            }
              
            //Si oui le tableau à renseigner dans la nouvelle face doit être inversé.
            $isReverse =  array_search($moveType, $getSpecif[$i]["reverse"]);
            
            if ($isReverse) {
                
            }
            
        }


    }
}
