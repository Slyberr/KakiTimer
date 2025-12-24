<?php

namespace App\Service\Scramble;

use App\Service\Cube\CubeInterface;

final class ThreeByThreeScrambler implements ScrambleGeneratorInterface
{

    private const  OPPOSITE_MOVES = array(
        'R' => 'L',
        'L' => 'R',
        'F' => 'B',
        'B' => 'F',
        'U' => 'D',
        'D' => 'U'
    );
    


    public function generate(): string
    {

        $scramble = '';
        $movestoDo = rand(21, 23);
        $scrambleToBuild = array();
        $possible_moves = self::getPossibleMoves();

        for ($i = 0; $i <= $movestoDo; $i++) {

            $isDouble = rand(0, 1);
            

            do {
                $randomMove = rand(0,5);
                $potentialMove = $possible_moves[$randomMove];
            } while (! self::mouvIsOk($scrambleToBuild, $potentialMove, $i));

            if ($isDouble == 0) {

                $isApostrophed = rand(0, 1);
                if ($isApostrophed == 1) {
                    $potentialMove = $potentialMove . self::APOSTROPHE;
                }
            } else {

                $potentialMove = $potentialMove . self::DOUBLE;
            }
            $scramble = $scramble . ' ' . $potentialMove;
            array_push($scrambleToBuild, $potentialMove);
        }
       
        return $scramble;
    }

    //Cette fonction vérifie si le nouveau mouvement peut s'inscrire dans le mélange

    private static function mouvIsOk(array $scrambleToBuild, string $moveToAdd, int $actualRank): bool
    {
         
        //Déjà un mouvement dans la séquence ?
        if (count($scrambleToBuild) == 0) {
            return true;
        } else {
            //Mouvement identique au précédent 

            if ($scrambleToBuild[$actualRank - 1][0] != $moveToAdd && self::NotAUselessMove($scrambleToBuild, $moveToAdd, $actualRank)) {
                return true;
            }
        }

        return false;
    }

    //Cette fonction avancée dépend fortement du contexte du mélange (Exemple : L2 R L. Le dernier mouvement n'est pas possible à réaliser.)
    private static function NotAUselessMove(array $scrambleToBuild, string $moveToAdd, int $actualRank): bool
    {

        if (count($scrambleToBuild) <= 2) {
            return true;
        }
        $myOppMove = self::OPPOSITE_MOVES[$moveToAdd];
        $lastMoveFace = $scrambleToBuild[$actualRank - 1][0];
        $beforeLastMoveFace = $scrambleToBuild[$actualRank - 2][0];
        //Si le dernier mouvement enregistré est un mouvement opposé et que le mouvement encore antérieur est identique au mouvement à rentrer.
        //On prend le premier char de la string pour ne pas avoir le facteur double move (2)

        if ($lastMoveFace == $myOppMove  && $beforeLastMoveFace == $moveToAdd) {
            return false;
        }

        return true;
    }

    public function getPossibleMoves(): array
    {
        return array_keys(CubeInterface::INITIAL_STATE);
    }
}
