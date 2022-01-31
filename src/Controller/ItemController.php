<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Item;
use App\Entity\ItemCollection;
use App\Entity\Tag;
use App\Form\CreateCommentType;
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

    #[Route('/{_locale<%app.supported_locales%>}/item/{id}', name: 'app_item')]
    public function index(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $repository = $doctrine->getRepository(Item::class);
        $item = $repository->findOneBy([
            'id' => $id,
        ]);

        $repository = $doctrine->getRepository(Comment::class);
        $comments = $repository->findByAllUsers($id);


        $comment = new Comment();
        $form = $this->createForm(CreateCommentType::class, $comment);
        $form->handleRequest($request);

        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($user);
            $comment->setItem($item);

            $this->em->persist($comment);
            $this->em->flush();

            return $this->redirect($this->generateUrl('app_item', ['id' => $id]));
        }

        return $this->render('item/index.html.twig', [
            'item' => $item,
            'comments' => $comments,
            'createCommentForm' => $form->createView(),
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/create/item/{id}', name: 'app_create_item')]
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
            $this->tags($form, $repository, $item);

            $item->setCollection($itemCollection);
            $this->em->persist($item);
            $this->em->flush();

            return $this->redirectToRoute('app_collection', ['id' => $id]);
        }

        return $this->render('item/create_item.html.twig', [
            'createItemForm' => $form->createView(),
            'itemCollection' => $itemCollection,
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/edit/item/{id}', name: 'app_edit_item')]
    public function edit(int $id, Request $request, ManagerRegistry $doctrine,): Response
    {
        $user = $this->getUser();
        $item = $this->em->find(Item::class, $id);
        $repository = $doctrine->getRepository(Tag::class);
        if ($item) {
            if ($user !== $item->getCollection()->getCreator() && $user->getRoles()[0] !== 'ROLE_ADMIN') {
                return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
            }
        } else return $this->redirectToRoute('app_main');

        $tags = $item->getTags();
        $tagsItem = [];
        foreach ($tags as $tag) {
            $tagsItem[] = $tag->getName();
        }
        $form = $this->createForm(CreateItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->getClickedButton() && 'save' === $form->getClickedButton()->getName()) {
                $getTags = explode(" ", $form->get('tags')->getData() ? $form->get('tags')->getData() : " ");
                $removedTags = array_diff($tagsItem, $getTags);
                $tags = $repository->findBy([
                    'name' => $removedTags
                ]);
                foreach ($tags as $removedTag) {
                    $item->removeTag($removedTag);

                }
                $item->setName($form->get('name')->getData());
                $this->tags($form, $repository, $item);
            }

            if ($form->getClickedButton() && 'delete' === $form->getClickedButton()->getName()) {
                $this->em->remove($item);
            }

            $this->em->flush();
            return $this->redirectToRoute('app_collection', ['id' => $item->getCollection()->getId(),]);
        }

        return $this->render('item/edit_item.html.twig', [
            'editItemForm' => $form->createView(),
            'item' => $item,
            'tagName' => (implode(" ", $tagsItem)),
        ]);
    }

    public function tags($form, $repository, $item)
    {
        $dataTags = $form->get('tags')->getData();
        if ($dataTags) {
            $tags = explode(" ", $dataTags);
            foreach ($tags as $tag) {
                $tagName = $repository->findOneBy([
                    'name' => $tag
                ]);
                if (!$tagName) {
                    $tagName = new Tag();
                    $tagName->setName($tag);
                }
                $item->addTag($tagName);
                $this->em->persist($tagName);
            }
        }
    }
}
