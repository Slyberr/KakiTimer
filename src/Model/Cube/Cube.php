<?php

namespace App\Model\Cube;

use InvalidArgumentException;

final class Cube implements CubeInterface
{

    private array $cube = [];

    public function __construct(private int $n,private bool $initialise = true) {
        
        $this->n = $n;

        if ($initialise) {
            $this->makeCube($n);
        }
    }

    public function makeCube(int $size) {

        foreach(CubeInterface::INITIAL_STATE as $key => $value) {

            $face = self::createFace($value,$size);
            $this->cube[$key] = $face;
        }
    }

    public function getNSize() : int {
        if ($this->n < 1) {
            throw new InvalidArgumentException("Taille de cube non conforme",500);
        }
        return $this->n;
    }

  
    public function getCube() : array {
        return $this->cube;
    }

  
    public function setCube(array $newCube){
        return $this->cube = $newCube;
    }

    /**
    * Création d'une face d'un cube
    *
    * @param string  $color la couleur de la face.
    * @param int $cubeSize la taille n du cube.
    * @return array le tableau réprésentant la face en 1 dimension.
    */
    private function createFace(string $color,int $cubeSize): array {

        $face = [];

        for ($i = 0; $i < $cubeSize*$cubeSize; $i++) {
            array_push($face, $color);
        }
        return $face;
    }
  
}
