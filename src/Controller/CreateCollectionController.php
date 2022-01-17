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
    public function __construct(
        private EntityManagerInterface $em
    )
    { }

    #[Route('/create', name: 'app_create_collection')]
    public function index(Request $request): Response
    {
        $itemCollection = new ItemCollection();
        $form = $this->createForm(CreateCollectionType::class, $itemCollection);

        //$user = $this->em->find(User::class, 2);
        $user = $this->getUser();
        $form->handleRequest($request);
        $itemCollection->setCreator($user);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($itemCollection);
            $this->em->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('create_collection/index.html.twig', [
            'createCollectionForm' => $form->createView(),
        ]);
    }
}
