<?php

namespace App\Service\EventDrawer;

use App\Model\Cube\CubeInterface;


/**
 * Interface de génération du patron de mélange pour chaque épreuve.
 */
interface EventDrawerInterface
{

/**
    * Création du patron de mélange d'un cube de dimension NxN
    * @param string $scramble le mélange à réaliser.
    * @param CubeInterface $cube l'objet cube à l'état initial.
    * @return CubeInterface le cube mélangé
    */
    public function drawScramble(string $scramble, CubeInterface $objcube) : CubeInterface;

}