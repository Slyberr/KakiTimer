<?php

namespace App\Service\DrawCube;


interface CubeDrawerInterface
{

    const NORMAL = 'NORMAL';
    const DOUBLE = "DOUBLE";
    const REVERSE = "REVERSE";

    //Un tableau contenant l'influcence d'un mouvement sur les 4 autres faces adjacentes.
    //On sait s'il s'agit d'une colonne ou d'une ligne, du numéro de cette dernière.
    //On représente les inversions des stickers avec un tableau alimentés par des mouvements possible lors d'un mélange. 
    // Lorsqu'un cube sera plus gros, la logique d'inversion sera la même.

    //Attention ! NE PAS MODIFIER l'ordre des faces !  Il n'est pas choisi au hasard. Il permet de savoir ce que sur quelle face se trouve les nouveaux stickers.

    //Exemple : avec un mouvement U, les pièces de la Face F vont en L. Un mouvement U' : F -> R. Un U2 : F-> B.

    const array FACENEIGHBORS = [
        'U' => [
            ['face' => 'F', 'type' => 'row', 'index' => '0', 'inverse' => []],
            ['face' => 'L', 'type' => 'row', 'index' => '0', 'inverse' => []],
            ['face' => 'B', 'type' => 'row', 'index' => '0', 'inverse' => []],
            ['face' => 'R', 'type' => 'row', 'index' => '0', 'inverse' => []]
        ],
        'D' => [
            ['face' => 'F', 'type' => 'row', 'index' => 'N-1', 'inverse' => []],
            ['face' => 'R', 'type' => 'row', 'index' => 'N-1', 'inverse' => []],
            ['face' => 'B', 'type' => 'row', 'index' => 'N-1', 'inverse' => []],
            ['face' => 'L', 'type' => 'row', 'index' => 'N-1', 'inverse' => []]
        ],
        'R' => [
            ['face' => 'U', 'type' => 'col', 'index' => 'N-1', 'inverse' => [self::NORMAL]],
            ['face' => 'B', 'type' => 'col', 'index' => '0', 'inverse' => [self::NORMAL, self::DOUBLE, self::REVERSE]],
            ['face' => 'D', 'type' => 'col', 'index' => 'N-1', 'inverse' => [self::REVERSE]],
            ['face' => 'F', 'type' => 'col', 'index' => 'N-1', 'inverse' => [self::DOUBLE]]
        ],
        'L' => [
            ['face' => 'U', 'type' => 'col', 'index' => '0', 'inverse' => [self::REVERSE]],
            ['face' => 'F', 'type' => 'col', 'index' => '0', 'inverse' => [self::DOUBLE]],
            ['face' => 'D', 'type' => 'col', 'index' => '0', 'inverse' => [self::NORMAL]],
            ['face' => 'B', 'type' => 'col', 'index' => 'N-1', 'inverse' => [self::NORMAL, self::DOUBLE, self::REVERSE]]
        ],
        'F' => [
            ['face' => 'U', 'type' => 'row', 'index' => 'N-1', 'inverse' => [self::DOUBLE, self::REVERSE]],
            ['face' => 'R', 'type' => 'col', 'index' => '0', 'inverse' => [self::NORMAL, self::DOUBLE]],
            ['face' => 'D', 'type' => 'row', 'index' => '0', 'inverse' => [self::DOUBLE, self::REVERSE]],
            ['face' => 'L', 'type' => 'col', 'index' => 'N-1', 'inverse' => [self::NORMAL, self::DOUBLE]]
        ],
        'B' => [
            ['face' => 'U', 'type' => 'row', 'index' => '0', 'inverse' => [self::NORMAL, self::DOUBLE]],
            ['face' => 'L', 'type' => 'col', 'index' => '0', 'inverse' => [self::REVERSE, self::DOUBLE]],
            ['face' => 'D', 'type' => 'row', 'index' => 'N-1', 'inverse' => [self::NORMAL, self::DOUBLE]],
            ['face' => 'R', 'type' => 'col', 'index' => 'N-1', 'inverse' => [self::REVERSE, self::DOUBLE]]
            
            
        ]
    ];


    public function draw(string $scramble, array $cube, int $n);
}
