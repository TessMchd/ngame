<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pseudo;

    /**
     * @return mixed
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * @param mixed $pseudo
     */
    public function setPseudo($pseudo): void
    {
        $this->pseudo = $pseudo;
    }

    /**
     * @ORM\Column(type="date")
     */
    private $birthday;

    /**
     * @ORM\OneToMany(targetEntity=Game::class, mappedBy="user1")
     */
    private $games1;

    /**
     * @ORM\OneToMany(targetEntity=Game::class, mappedBy="user2")
     */
    private $games2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $avatar;

    /**
     * @ORM\OneToOne(targetEntity=Stats::class,  cascade={"persist", "remove"})
     * * @ORM\JoinColumn(nullable=true)
     */
    private $stats;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $amis = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $invitations = [];

    /**
     * @param mixed $stats
     */
    public function setStats($stats): void
    {
        $this->stats = $stats;
    }

    public function __construct()
    {
        $this->games1 = new ArrayCollection();
        $this->games2 = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }


    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_JOUEUR';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return Collection|Game[]
     */
    public function getGames1(): Collection
    {
        return $this->games1;
    }

    public function addGames1(Game $games1): self
    {
        if (!$this->games1->contains($games1)) {
            $this->games1[] = $games1;
            $games1->setUser1($this);
        }

        return $this;
    }

    public function removeGames1(Game $games1): self
    {
        if ($this->games1->removeElement($games1)) {
            // set the owning side to null (unless already changed)
            if ($games1->getUser1() === $this) {
                $games1->setUser1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Game[]
     */
    public function getGames2(): Collection
    {
        return $this->games2;
    }

    public function addGames2(Game $games2): self
    {
        if (!$this->games2->contains($games2)) {
            $this->games2[] = $games2;
            $games2->setUser2($this);
        }

        return $this;
    }

    public function removeGames2(Game $games2): self
    {
        if ($this->games2->removeElement($games2)) {
            // set the owning side to null (unless already changed)
            if ($games2->getUser2() === $this) {
                $games2->setUser2(null);
            }
        }

        return $this;
    }

    public function display()
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getStats(): ?Stats
    {
        return $this->stats;
    }

    public function getStatsId(){
        return $this->getStats()->getId();
    }

    public function getAmis(): ?array
    {
        return $this->amis;
    }

    public function setAmis(?array $amis): self
    {
        $this->amis = $amis;

        return $this;
    }

    public function getInvitations(): ?array
    {
        return $this->invitations;
    }

    public function setInvitations(?array $invitations): self
    {
        $this->invitations = $invitations;

        return $this;
    }

}
