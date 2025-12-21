<?php

namespace App\Controller;

use App\Entity\SolveEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SolveController extends AbstractController
{
    //Sauvegarder un temps dans la base de données.
    #[Route('/timer/save', methods:['POST'], name: 'app_time_save')]
    public function saveTime(Request $request, EntityManagerInterface $entityManager)
    {

        $data = json_decode($request->getContent(),true);

        $solve = new SolveEntity();
        

    }
}
