<?php

namespace App\Service\Cube;

use App\Service\Scramble\ScrambleGeneratorInterface;

final class ThreeByThreeCube extends AbstractCube 
{
    
    public function makeCube(): array
    {
        $cube = [];

        foreach(CubeInterface::INITIAL_STATE as $key => $value) {

            $face = parent::createFace($value,3);
            $cube[$key] = $face;
        }
        return $cube;
    }

  
}
