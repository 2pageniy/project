<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Item;
use App\Entity\ItemCollection;
use App\Entity\User;
use App\Form\CreateCollectionType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CollectionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {
    }

    #[Route('/collection/{id}', name: 'app_collection')]
    public function index(ManagerRegistry $doctrine, int $id): Response
    {

        $itemCollection = $this->em->find(ItemCollection::class, $id);

        if (!$itemCollection) {
            return $this->redirectToRoute('app_main');
        }


        $repository = $doctrine->getRepository(Item::class);
        $items = $repository->findAllTagsOrderedByNewest($id);

        return $this->render('collection/index.html.twig', [
            'itemCollection' => $itemCollection,
            'items' => $items,
        ]);
    }

    #[Route('/create/collection/{id}', name: 'app_create_collection')]
    public function create(Request $request, FileUploader $fileUploader, int $id): Response
    {
        $itemCollection = new ItemCollection();
        $form = $this->createForm(CreateCollectionType::class, $itemCollection);
        $user = $this->em->find(User::class, $id);
        $loggedUser = $this->getUser();

        if ($loggedUser->getId() !== $id && $loggedUser->getRoles()[0] !== 'ROLE_ADMIN') {
            return $this->redirectToRoute('app_profile', ['id' => $loggedUser->getId()]);
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

        return $this->render('collection/create_collection.html.twig', [
            'createCollectionForm' => $form->createView(),
        ]);
    }

    #[Route('/edit/collection/{id}', name: 'app_edit_collection')]
    public function edit(int $id, Request $request, FileUploader $fileUploader): Response
    {
        $user = $this->getUser();
        $itemCollection = $this->em->find(ItemCollection::class, $id);

        if ($itemCollection) {
            if ($user !== $itemCollection->getCreator() && $user->getRoles()[0] !== 'ROLE_ADMIN') {
                return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
            }
        } else return $this->redirectToRoute('app_main');

        $form = $this->createForm(CreateCollectionType::class, $itemCollection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->getClickedButton() && 'save' === $form->getClickedButton()->getName()) {
                $itemCollection->setName($form->get('name')->getData());
                $itemCollection->setTopic($form->get('topic')->getData());
                $itemCollection->setDescription($form->get('description')->getData());
                $pictureFile = $form->get('picture')->getData();
                if ($pictureFile) {
                    $pictureFileName = $fileUploader->upload($pictureFile);
                    $itemCollection->setPicture($pictureFileName);
                }


            }
            if ($form->getClickedButton() && 'delete' === $form->getClickedButton()->getName()) {
                $this->em->remove($itemCollection);
            }

            $this->em->flush();
            return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
        }

        return $this->render('collection/edit_collection.html.twig', [
            'itemCollection' => $itemCollection,
            'createCollectionForm' => $form->createView(),
        ]);
    }


}
