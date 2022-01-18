<?php

namespace App\Controller;

use App\Entity\ItemCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(ItemCollection::class);
        $creator = $this->getUser()->getId();
        $itemCollection = $repository->findBy([
            'creator' => $creator,
        ]);


        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'itemCollection' => $itemCollection,
        ]);

    }
}
