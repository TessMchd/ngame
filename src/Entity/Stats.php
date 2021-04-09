<?php

namespace App\Entity;

use App\Repository\StatsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatsRepository::class)
 */
class Stats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="integer")
     */
    private $victoires;

    /**
     * @ORM\Column(type="integer")
     */
    private $defaites;

    /**
     * @ORM\Column(type="integer")
     */
    private $pieces;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $temps_jeu;

    /**
     * @ORM\Column(type="integer")
     */
    private $rang;

    /**
     * @ORM\Column(type="integer")
     */
    private $parties_terminees;

    /**
     * @ORM\Column(type="integer")
     */
    private $parties_jouees;

    /**
     * @ORM\Column(type="integer")
     */
    private $parties_en_cours;

    /**
     * @ORM\Column(type="integer")
     */
    private $grade;

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getVictoires(): ?int
    {
        return $this->victoires;
    }

    public function setVictoires(int $victoires): self
    {
        $this->victoires = $victoires;

        return $this;
    }

    public function getDefaites(): ?int
    {
        return $this->defaites;
    }

    public function setDefaites(int $defaites): self
    {
        $this->defaites = $defaites;

        return $this;
    }

    public function getPieces(): ?int
    {
        return $this->pieces;
    }

    public function setPieces(int $pieces): self
    {
        $this->pieces = $pieces;

        return $this;
    }

    public function getTempsJeu(): ?\DateTimeInterface
    {
        return $this->temps_jeu;
    }

    public function setTempsJeu(?\DateTimeInterface $temps_jeu): self
    {
        $this->temps_jeu = $temps_jeu;

        return $this;
    }

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(int $rang): self
    {
        $this->rang = $rang;

        return $this;
    }

    public function getPartiesTerminees(): ?int
    {
        return $this->parties_terminees;
    }

    public function setPartiesTerminees(int $parties_terminees): self
    {
        $this->parties_terminees = $parties_terminees;

        return $this;
    }

    public function getPartiesJouees(): ?int
    {
        return $this->parties_jouees;
    }

    public function setPartiesJouees(int $parties_jouees): self
    {
        $this->parties_jouees = $parties_jouees;

        return $this;
    }

    public function getPartiesEnCours(): ?int
    {
        return $this->parties_en_cours;
    }

    public function setPartiesEnCours(int $parties_en_cours): self
    {
        $this->parties_en_cours = $parties_en_cours;

        return $this;
    }

    public function getGrade(): ?int
    {
        return $this->grade;
    }

    public function setGrade(int $grade): self
    {
        $this->grade = $grade;

        return $this;
    }
}
