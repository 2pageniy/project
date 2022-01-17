<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $name;

    #[ORM\ManyToOne(targetEntity: ItemCollection::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private $collection;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCollection(): ?ItemCollection
    {
        return $this->collection;
    }

    public function setCollection(?ItemCollection $collection): self
    {
        $this->collection = $collection;

        return $this;
    }
}
