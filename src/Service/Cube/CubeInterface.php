<?php

namespace App\Service\Cube;


interface CubeInterface 
{
   const COLORS = ['W','O','G','R','B','Y'];
   
   function makeCube() : array;

}


