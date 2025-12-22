<?php

namespace App\Service\Scramble;

final class ThreeByThreeScrambler implements ScrambleGeneratorInterface
{

    private const  POSSIBLE_MOVES  = ['R', 'U', 'L', 'D', 'F', 'B'];
    private const  OPPOSITE_MOVES = array(
        'R' => 'L',
        'L' => 'R',
        'F' => 'B',
        'B' => 'F',
        'U' => 'D',
        'D' => 'U'
    );
    private const APOSTROPHE = '\'';
    private const DOUBLE = '2';


    public function generate(): string
    {

        $scramble = '';
        $movestoDo = rand(21, 23);
        $scrambleToBuild = array();

        for ($i = 0; $i <= $movestoDo; $i++) {

            $isDouble = rand(0, 1);

            do {
                $key = array_rand(self::POSSIBLE_MOVES);
                $potentialMove = self::POSSIBLE_MOVES[$key];
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

        //var_dump($scrambleToBuild[$actualRank - 1][0]);
        if ($lastMoveFace == $myOppMove  && $beforeLastMoveFace == $moveToAdd) {
            return false;
        }

        return true;
    }
}
