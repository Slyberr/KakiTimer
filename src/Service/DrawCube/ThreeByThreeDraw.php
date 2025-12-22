<?php

namespace App\Service\DrawCube;

use App\Service\Scramble\ScrambleGeneratorInterface;

final class ThreeByThreeDraw implements CubeDrawerInterface
{
    //private ThreeCube $cu
    
    public function draw(ScrambleGeneratorInterface $scramble): string
    {
        return "";     
    }
  
}
