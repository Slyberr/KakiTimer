<?php

namespace App\Model\Cube;


interface CubeInterface
{

   // L'état inital d'un cube Face => Couleur.
   const INITIAL_STATE = ['U' => 'W', 'L' => 'O', 'F' => 'G', 'R' => 'R', 'B' => 'B', 'D' => 'Y'];


   /**
    * Création d'un cube résolu
    * @param int  $size Défini la taille n du cube.
    * @return array un tableau de faces résolues.
    */
   function makeCube(int $size);

   /**
    * Retourne la taille du cube.
    * @return int 
    */
   function getNSize(): int;

   /**
    * Retourne le cube.
    * @return array
    */
   function getCube(): array;

   /**
    * setCube.
    * @param array $newCube
    */
   function setCube(array $newCube);
}
