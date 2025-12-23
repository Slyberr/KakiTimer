<?php

namespace App\Service\Cube;

use App\Service\Scramble\ScrambleGeneratorInterface;

final class ThreeByThreeCube extends AbstractCube 
{
    
    public function makeCube(): array
    {
        $cube = [];


        for ($i = 0; $i < count(CubeInterface::COLORS) ; $i++) {

            $face = parent::createFace(CubeInterface::COLORS[$i],3);
            $cube[ScrambleGeneratorInterface::POSSIBLE_MOVES[$i]] = $face;
        }

        return $cube;
    }

  
}
