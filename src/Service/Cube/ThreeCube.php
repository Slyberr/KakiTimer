<?php

namespace App\Service\Cube;


final class ThreeCube extends AbstractCube 
{
    
    public function makeCube(): array
    {
        $cube = [];

        for ($i = 0; $i < 6; $i++) {

            $face = parent::createFace(CubeInterface::COLORS[$i],3);
            array_push($cube,$face);
        }

        return $cube;
    }

  
}
