<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\ItemCollection;
use App\Entity\Tag;
use App\Form\CreateItemType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    #[Route('/item/{id}', name: 'app_item')]
    public function index(ManagerRegistry $doctrine, int $id): Response
    {
        $repository = $doctrine->getRepository(Item::class);
        $item = $repository->findOneBy([
            'id' => $id,
        ]);

        return $this->render('item/index.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('/create/item/{id}', name: 'app_create_item')]
    public function create(int $id, Request $request, ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Tag::class);

        $user = $this->getUser();
        $itemCollection = $this->em->find(ItemCollection::class, $id);
        $tags = $repository->findAll();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        } else if ($itemCollection) {
            if ($user !== $itemCollection->getCreator() && $user->getRoles()[0] !== 'ROLE_ADMIN') {
                return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
            }
        } else return $this->redirectToRoute('app_main');

        $item = new Item();

        $form = $this->createForm(CreateItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataTag = $form->get('tags')->getData();
            if ($dataTag) {

                $tag = $repository->findOneBy([
                    'name' => $dataTag
                ]);
                if (!$tag) {
                    $tag = new Tag();
                    $tag->setName($dataTag);
                }
                $item->addTag($tag);
                $this->em->persist($tag);
            }

            $item->setCollection($itemCollection);
            $item->setCreated();
            $this->em->persist($item);
            $this->em->flush();

            return $this->redirectToRoute('app_collection', ['id' => $id]);
        }

        return $this->render('item/create_item.html.twig', [
            'editCollectionForm' => $form->createView(),
            'itemCollection' => $itemCollection,
            'tags' => $tags
        ]);
    }
}
