<?php

namespace App\Controller;

use App\Entity\Tag;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search/{name}', name: 'app_search')]
    public function index(string $name, ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Tag::class);
        $tag = $repository->findOneBy([
            'name' => $name
        ]);
        return $this->render('search/index.html.twig', [
            'tag' => $tag,
        ]);
    }
}
