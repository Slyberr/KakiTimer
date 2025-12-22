<?php

namespace App\Service\DrawCube;

use App\Service\Scramble\ScrambleGeneratorInterface;

interface CubeDrawerInterface 
{
    public function draw(ScrambleGeneratorInterface $scramble) : string;
    
   
}


