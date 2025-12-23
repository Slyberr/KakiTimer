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
    const array FACENEIGHBORS = [
        'U' => [
            ['face' => 'F', 'type' => 'row', 'index' => '0', 'reverse' => []],
            ['face' => 'R', 'type' => 'row', 'index' => '0', 'reverse' => []],
            ['face' => 'B', 'type' => 'row', 'index' => '0', 'reverse' => []],
            ['face' => 'L', 'type' => 'row', 'index' => '0', 'reverse' => []]
        ],
        'D' => [
            ['face' => 'F', 'type' => 'row', 'index' => 'N-1', 'reverse' => []],
            ['face' => 'R', 'type' => 'row', 'index' => 'N-1', 'reverse' => []],
            ['face' => 'B', 'type' => 'row', 'index' => 'N-1', 'reverse' => []],
            ['face' => 'L', 'type' => 'row', 'index' => 'N-1', 'reverse' => []]
        ],
        'R' => [
            ['face' => 'U', 'type' => 'col', 'index' => 'N-1', 'reverse' => [self::NORMAL]],
            ['face' => 'F', 'type' => 'col', 'index' => 'N-1', 'reverse' => [self::DOUBLE]],
            ['face' => 'D', 'type' => 'col', 'index' => 'N-1', 'reverse' => [self::REVERSE]],
            ['face' => 'B', 'type' => 'col', 'index' => '0', 'reverse' => [self::NORMAL, self::DOUBLE, self::REVERSE]]
        ],
        'L' => [
            ['face' => 'U', 'type' => 'col', 'index' => '0', 'reverse' => [self::REVERSE]],
            ['face' => 'F', 'type' => 'col', 'index' => '0', 'reverse' => [self::DOUBLE]],
            ['face' => 'D', 'type' => 'col', 'index' => '0', 'reverse' => [self::NORMAL]],
            ['face' => 'B', 'type' => 'col', 'index' => 'N-1', 'reverse' => [self::NORMAL, self::DOUBLE, self::REVERSE]]
        ],
        'F' => [
            ['face' => 'U', 'type' => 'row', 'index' => 'N-1', 'reverse' => [self::DOUBLE, self::REVERSE]],
            ['face' => 'R', 'type' => 'col', 'index' => '0', 'reverse' => [self::NORMAL, self::DOUBLE]],
            ['face' => 'D', 'type' => 'row', 'index' => '0', 'reverse' => [self::DOUBLE, self::REVERSE]],
            ['face' => 'L', 'type' => 'col', 'index' => 'N-1', 'reverse' => [self::NORMAL, self::DOUBLE]]
        ],
        'B' => [
            ['face' => 'U', 'type' => 'row', 'index' => '0', 'reverse' => [self::NORMAL, self::DOUBLE]],
            ['face' => 'R', 'type' => 'col', 'index' => 'N-1', 'reverse' => [self::REVERSE, self::DOUBLE]],
            ['face' => 'D', 'type' => 'row', 'index' => 'N-1', 'reverse' => [self::NORMAL, self::DOUBLE]],
            ['face' => 'L', 'type' => 'col', 'index' => '0', 'reverse' => [self::REVERSE, self::DOUBLE]]
        ]
    ];


    public function draw(string $scramble, array $cube, int $n);
}
