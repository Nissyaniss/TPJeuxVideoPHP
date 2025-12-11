<?php

namespace App\Entity;

use App\Enum\Collection;
use App\Repository\CollectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: CollectRepository::class)]
class Collect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        if ($this->dateAchat && $this->jeuvideo && $this->jeuvideo->getDateSortie()) {
            if ($this->dateAchat < $this->jeuvideo->getDateSortie()) {
                $context->buildViolation('La date d\'achat ne peut pas être antérieure à la date de sortie du jeu.')
                    ->atPath('dateAchat')
                    ->addViolation();
            }
        }
    }

    #[ORM\ManyToOne(inversedBy: 'collections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'collections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JeuVideo $jeuvideo = null;

    #[ORM\Column(enumType: Collection::class)]
    private ?Collection $statut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateModifStatut = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixAchat = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAchat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getJeuvideo(): ?JeuVideo
    {
        return $this->jeuvideo;
    }

    public function setJeuvideo(?JeuVideo $jeuvideo): static
    {
        $this->jeuvideo = $jeuvideo;

        return $this;
    }

    public function getStatut(): ?Collection
    {
        return $this->statut;
    }

    public function setStatut(Collection $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateModifStatut(): ?\DateTimeInterface
    {
        return $this->dateModifStatut;
    }

    public function setDateModifStatut(\DateTimeInterface $dateModifStatut): static
    {
        $this->dateModifStatut = $dateModifStatut;

        return $this;
    }

    public function getPrixAchat(): ?float
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?float $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->dateAchat;
    }

    public function setDateAchat(?\DateTimeInterface $dateAchat): static
    {
        $this->dateAchat = $dateAchat;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }
}
