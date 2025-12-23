<?php

namespace App\Service\Scramble;

interface ScrambleGeneratorInterface 
{
    Const POSSIBLE_MOVES  = ['U', 'L', 'F', 'R', 'B', 'D'];
    Const APOSTROPHE = '\'';
    Const DOUBLE = '2';
    
    public function generate() : string;
}


