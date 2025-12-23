<?php

namespace App\Service\DrawCube;

use App\Service\Cube\AbstractCubeDrawer;

final class ThreeByThreeDraw extends AbstractCubeDrawer
{

    function drawThreeByThree(string $scramble, array $cube)
    {
        return parent::draw($scramble, $cube, 3);
    }

}
