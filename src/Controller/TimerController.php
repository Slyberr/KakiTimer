<?php

namespace App\Controller;

use App\Service\Cube\ThreeByThreeCube;
use App\Service\DrawCube\ThreeByThreeDraw;
use App\Service\Scramble\ScrambleGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

//La classe pour Gérer le timer et sa logique.
final class TimerController extends AbstractController
{
    //Arriver sur la page du timer
    #[Route('/timer', methods:['GET'], name: 'app_timer')]
    public function index(ScrambleGeneratorInterface $scramble,ThreeByThreeDraw $cubeDraw,ThreeByThreeCube $cube): Response
    {
        $date  = time();
        dd($cubeDraw->drawThreeByThree($scramble->generate(),$cube->makeCube()));

        return $this->render('timer/index.html.twig', [
            'controller_name' => 'TimerController',
             'date_actuel' => $date,
             'scramble' => $scramble->generate()
             
        ]);
    }

    //Générer un nouveau scramble lors de le chornomètre est arrêté.
    #[Route('/timer/scramble/generate', methods:['GET'], name: 'app_timer_scramble_generate')]
    public function generateScramble(ScrambleGeneratorInterface $scramble, ): JsonResponse
    {
        $newScramble = $scramble->generate();
        
        
        return new JsonResponse(['newScramble' => $newScramble]);   
    }


}
