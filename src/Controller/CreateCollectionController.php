<?php

namespace App\Controller;

use App\Entity\ItemCollection;
use App\Form\CreateCollectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateCollectionController extends AbstractController
{
    #[Route('/create', name: 'app_create_collection')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $itemCollection = new ItemCollection();

        $form = $this->createForm(CreateCollectionType::class, $itemCollection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password

            $entityManager->persist($itemCollection);
            $entityManager->flush();

            return $this->redirectToRoute('app_main');
        }

        return $this->render('create_collection/index.html.twig', [
            'createCollectionForm' => $form->createView(),

        ]);
    }
}
