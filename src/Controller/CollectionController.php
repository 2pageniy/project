<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\ItemCollection;
use App\Form\CreateCollectionType;
use App\Form\EditCollectionType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CollectionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    )
    { }

    #[Route('/create', name: 'app_create_collection')]
    public function create(Request $request, FileUploader $fileUploader): Response
    {
        $itemCollection = new ItemCollection();
        $form = $this->createForm(CreateCollectionType::class, $itemCollection);

        //$user = $this->em->find(User::class, 2);
        $user = $this->getUser();

        if(!$user) {
            return $this->redirectToRoute('app_login');
        }

        $itemCollection->setCreator($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                $pictureFileName = $fileUploader->upload($pictureFile);
                $itemCollection->setPicture($pictureFileName);
            }
            
            $this->em->persist($itemCollection);
            $this->em->flush();

            return $this->redirectToRoute('app_main');
        }

        return $this->render('create_collection/index.html.twig', [
            'createCollectionForm' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_edit_collection')]
    public function edit(int $id, Request $request): Response
    {

        $itemCollection = $this->em->find(ItemCollection::class, $id);
        if(!$itemCollection) {
            return $this->redirectToRoute('app_main');
        }

        $item = new Item();
        $form = $this->createForm(EditCollectionType::class, $item);
        $form->handleRequest($request);

        $item->setCollection($itemCollection);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($item);
            $this->em->flush();

            return $this->redirectToRoute('app_main');
        }

        return $this->render('edit_collection/index.html.twig', [
            'editCollectionForm' => $form->createView(),
        ]);
    }

}
