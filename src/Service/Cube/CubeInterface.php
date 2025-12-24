<?php

namespace App\Service\Cube;


interface CubeInterface
{

   // L'état inital d'un cube Face => Couleur.
   const INITIAL_STATE = ['U' => 'W', 'L' => 'O', 'F' => 'G', 'R' => 'R', 'B' => 'B', 'D' => 'Y'];

   function makeCube(): array;
}
