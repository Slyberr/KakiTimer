<?php

namespace App\Controller;

use App\Service\Scramble\ScrambleGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TimerController extends AbstractController
{
    #[Route('/timer', name: 'app_timer')]
    public function index(ScrambleGeneratorInterface $scramble): Response
    {
        $date  = time();
        return $this->render('timer/index.html.twig', [
            'controller_name' => 'TimerController',
             'date_actuel' => $date,
             'scramble' => $scramble->generate()
        ]);
    }
}
