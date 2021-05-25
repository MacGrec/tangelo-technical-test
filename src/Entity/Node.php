<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NodeRepository::class)
 */
class Node
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
     * @ORM\ManyToMany(targetEntity=Node::class, inversedBy="nodes" ,cascade={"persist"})
     */
    private $parent;

    /**
     * @ORM\ManyToMany(targetEntity=Node::class, mappedBy="parent")
     */
    private $nodes;

    /**
     * @ORM\ManyToOne(targetEntity=Tree::class, inversedBy="node")
     * @ORM\JoinColumn(nullable=true)
     */
    private $tree;

    public function __construct()
    {
        $this->parent = new ArrayCollection();
        $this->nodes = new ArrayCollection();
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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Collection|Node[]
     */
    public function getParent(): Collection
    {
        return $this->parent;
    }

    public function addParent(Node $parent): self
    {
        if (!$this->parent->contains($parent)) {
            $this->parent[] = $parent;
        }

        return $this;
    }

    public function removeParent(Node $parent): self
    {
        $this->parent->removeElement($parent);

        return $this;
    }

    /**
     * @return Collection|Node[]
     */
    public function getNodes(): Collection
    {
        return $this->nodes;
    }

    public function addNode(Node $node): self
    {
        if (!$this->nodes->contains($node)) {
            $this->nodes[] = $node;
            $node->addParent($this);
        }

        return $this;
    }

    public function removeNode(Node $node): self
    {
        if ($this->nodes->removeElement($node)) {
            $node->removeParent($this);
        }

        return $this;
    }

    public function getTree(): ?Tree
    {
        return $this->tree;
    }

    public function setTree(?Tree $tree): self
    {
        $this->tree = $tree;

        return $this;
    }
}
