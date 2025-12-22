<?php

namespace App\Service\Cube;


abstract class AbstractCube implements CubeInterface
{
    
    abstract public function makeCube(): array;

    protected function createFace(string $color,string $cubeSize): array {

        $face = [];

        for ($i=0; $i < $cubeSize*$cubeSize; $i++) {

            array_push($face, $color);
        }
        
        return $face;
    }
  
}
