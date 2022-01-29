<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Entity\ItemCollection;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    )
    { }

    #[Route('/id{id}', name: 'app_profile')]
    public function index(ManagerRegistry $doctrine, int $id): Response
    {
        $user = $this->em->find(User::class, $id);
        if(!$user) {
            return $this->redirectToRoute('app_main');
        }

        $repository = $doctrine->getRepository(ItemCollection::class);
        //$creator = $this->getUser()->getId();
        $itemCollections = $repository->findBy([
            'creator' => $id,
        ]);



        return $this->render('profile/index.html.twig', [
            'itemCollections' => $itemCollections,
            'creatorId' => $id,
        ]);

    }
}
