<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateCollectionController extends AbstractController
{
    #[Route('/create/collection', name: 'create_collection')]
    public function index(): Response
    {
        return $this->render('create_collection/index.html.twig', [
            'controller_name' => 'CreateCollectionController',
        ]);
    }
}
