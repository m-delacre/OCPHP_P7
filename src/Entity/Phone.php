<?php

namespace App\Entity;

use App\Repository\PhoneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PhoneRepository::class)]
class Phone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("getPhones")]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups("getPhones")]
    private ?string $model = null;

    #[ORM\Column(length: 50)]
    #[Groups("getPhones")]
    private ?string $marque = null;

    #[ORM\Column(length: 10)]
    private ?string $battery = null;

    #[ORM\Column]
    private array $colors = [];

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    #[Groups("getPhones")]
    private ?string $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getBattery(): ?string
    {
        return $this->battery;
    }

    public function setBattery(string $battery): static
    {
        $this->battery = $battery;

        return $this;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function setColors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }
}
