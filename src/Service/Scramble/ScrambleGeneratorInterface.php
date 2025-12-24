<?php

namespace App\Service\Scramble;


interface ScrambleGeneratorInterface 
{
    Const APOSTROPHE = '\'';
    Const DOUBLE = '2';
    
    public function generate() : string;
    public function getPossibleMoves() : array;
}


