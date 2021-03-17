<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="games1")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user1;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="games2")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user2;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $winner;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $ended;

    /**
     * @ORM\OneToMany(targetEntity=Round::class, mappedBy="game")
     */
    private $sets;

    public function __construct()
    {
        $this->sets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1(): ?user
    {
        return $this->user1;
    }

    public function setUser1(?user $user1): self
    {
        $this->user1 = $user1;

        return $this;
    }

    public function getUser2(): ?user
    {
        return $this->user2;
    }

    public function setUser2(?user $user2): self
    {
        $this->user2 = $user2;

        return $this;
    }

    public function getWinner(): ?user
    {
        return $this->winner;
    }

    public function setWinner(?user $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getEnded(): ?\DateTimeInterface
    {
        return $this->ended;
    }

    public function setEnded(\DateTimeInterface $ended): self
    {
        $this->ended = $ended;

        return $this;
    }

    /**
     * @return Collection|Round[]
     */
    public function getSets(): Collection
    {
        return $this->sets;
    }

    public function addSet(Round $set): self
    {
        if (!$this->sets->contains($set)) {
            $this->sets[] = $set;
            $set->setGame($this);
        }

        return $this;
    }

    public function removeSet(Round $set): self
    {
        if ($this->sets->removeElement($set)) {
            // set the owning side to null (unless already changed)
            if ($set->getGame() === $this) {
                $set->setGame(null);
            }
        }

        return $this;
    }
}
