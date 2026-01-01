<?php

namespace App\Service\Scramble;


interface ScrambleGeneratorInterface 
{
    
   
    Const APOSTROPHE = '\'';
    Const DOUBLE = '2';
    Const WIDE = 'w';
    Const TRIPLEWIDE = '3';

    /**
    * Génération d'un mélange de rubik's cube 3x3
    * @return string le mélange généré.
    */
    public function generate() : string;

    /**
     * Récupération des mouvements possibles pour un type de rubik's cube
     */
    public function getPossibleMoves() : array;
}


