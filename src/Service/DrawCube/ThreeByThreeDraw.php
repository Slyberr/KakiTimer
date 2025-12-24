<?php

namespace App\Service\DrawCube;

use App\Service\Cube\AbstractCubeDrawer;

final class ThreeByThreeDraw extends AbstractCubeDrawer
{

    function drawThreeByThree(string $scramble, array $cube) : array
    {
        $cube = parent::drawScramble($scramble, $cube, 3);

        return $cube;
    }

}
