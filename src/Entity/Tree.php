<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TreeRepository::class)
 */
class Tree
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Node::class, mappedBy="tree", orphanRemoval=true)
     */
    private $node;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $original;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $flattened;

    /**
     * @ORM\Column(type="integer")
     */
    private $depth;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;


    public function __construct()
    {
        $this->node = new ArrayCollection();
        $this->depth = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Collection|Node[]
     */
    public function getNodes(): Collection
    {
        return $this->node;
    }

    public function addNode(Node $node): self
    {
        if (!$this->node->contains($node)) {
            $this->node[] = $node;
            $node->setTree($this);
        }

        return $this;
    }

    public function removeNode(Node $node): self
    {
        if ($this->node->removeElement($node)) {
            // set the owning side to null (unless already changed)
            if ($node->getTree() === $this) {
                $node->setTree(null);
            }
        }

        return $this;
    }

    public function getDepth(): ?int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * @return Collection|Node[]
     */
    public function getNode(): Collection
    {
        return $this->node;
    }

    public function getFlattened(): ?string
    {
        return $this->flattened;
    }

    public function setFlattened(?string $flattened): self
    {
        $this->flattened = $flattened;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getOriginal(): ?string
    {
        return $this->original;
    }

    public function setOriginal(?string $original): self
    {
        $this->original = $original;

        return $this;
    }
}
