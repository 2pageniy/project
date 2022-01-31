<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Entity\ItemCollection;
use App\Entity\Tag;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(ManagerRegistry $doctrine): Response
    {
        //last 10 items
        $repositoryItem = $doctrine->getRepository(Item::class);
        $items = $repositoryItem->findBy(
            [],
            ['id' => 'DESC'],
            10
        );

        //5 biggest collections
        $itemsWithCollections = $repositoryItem->findAllCollectionsOrderedByItem();
        $collectionsWeight = array();
        foreach ($itemsWithCollections as $item) {
            $collection = $item->getCollection();
            $collectionsWeight[$collection->getId()] = (isset($collectionsWeight[$collection->getId()]) ?
                $collectionsWeight[$collection->getId()] + 1 : 1);
        }

        arsort($collectionsWeight);
        $collectionsWeight = array_keys($collectionsWeight);
        array_splice($collectionsWeight, 5);

        $repositoryCollection = $doctrine->getRepository(ItemCollection::class);
        $collections = $repositoryCollection->findBy([
            'id' => $collectionsWeight,
        ]);

        //tag cloud
        $repositoryTags = $doctrine->getRepository(Tag::class);
        $tags = $repositoryTags->findAllItemsOrderedByTag();
        $tagWeights = array();
        if ($tags) {
            foreach ($tags as $tag) {
                $countTag = count($tag->getItems());
                if ($countTag)
                    $tagWeights[$tag->getName()] = $countTag;
            }
            if ($tagWeights) {
                $max = max($tagWeights);

                // Max of 5 weights
                $multiplier = ($max > 5) ? 5 / $max : 1;
                foreach ($tagWeights as &$tag) {
                    $tag = ceil($tag * $multiplier);
                }
            }
        }

        return $this->render('main/index.html.twig', [
            'items' => $items,
            'collections' => $collections,
            'tags' => $tagWeights,
        ]);
    }
}
