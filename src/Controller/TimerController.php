<?php

namespace App\Controller;

use App\Model\Cube\Cube;
use App\Service\EventDrawer\CubeDrawerInterface;
use App\Service\Scramble\ScrambleGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

//La classe pour Gérer le timer et sa logique.
final class TimerController extends AbstractController
{
    /**
     * Route GET lorsque la page timer.twig.html est chargée
     * @return Response
     */
    #[Route('/timer', methods:['GET'], name: 'app_timer')]
    public function index(): Response
    { 
        return $this->render('timer/index.html.twig', [
            'controller_name' => 'TimerController',
             'cubeScrambled' => null,

        ]);
    }

    //Générer un nouveau scramble.
    #[Route('/timer/scramble/generate', methods:['GET'], name: 'app_timer_generate_scramble')]
    public function generateScramble(Request $request, ScrambleGeneratorInterface $scramble): JsonResponse
    {

        $event = $request->query->get('event');
        if (str_contains($event, "333") ) {
            $newScramble = $scramble->generate();
        }
        
        return new JsonResponse(['newScramble' => $newScramble]);   
    }

    //Dessiner le scramble.
    #[Route('/timer/scramble/draw', methods:['GET'], name: 'app_timer_draw_scramble')]
    public function drawScramble(Request $request, CubeDrawerInterface $draw): JsonResponse
    {
        
        $n = $request->query->get('event');
        $scramble = $request->query->get('scramble');
        
        
        $cubeScrambled = $draw->drawScramble($scramble,new Cube($n[0]));


        return new JsonResponse(['cubeScrambled' => $cubeScrambled->getCube()]);   
    }

}
