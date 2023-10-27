<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    private ?string $titulo = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La editorial es obligatoria')]
    private ?string $editorial = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Las páginas son obligatorias')]
    private ?int $paginas = null;

    #[ORM\ManyToOne(inversedBy: 'books')]
    private ?Colection $colection = null;

    #[ORM\ManyToOne(inversedBy: 'books')]
    #[Assert\NotBlank(message: 'El autor es obligatorio')]
    private ?Author $autor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }


    public function getEditorial(): ?string
    {
        return $this->editorial;
    }

    public function setEditorial(string $editorial): self
    {
        $this->editorial = $editorial;

        return $this;
    }

    public function getPaginas(): ?int
    {
        return $this->paginas;
    }

    public function setPaginas(int $paginas): self
    {
        $this->paginas = $paginas;

        return $this;
    }

    public function getColection(): ?Colection
    {
        return $this->colection;
    }

    public function setColection(?Colection $colection): static
    {
        $this->colection = $colection;

        return $this;
    }

    public function getAutor(): ?Author
    {
        return $this->autor;
    }

    public function setAutor(?Author $autor): static
    {
        $this->autor = $autor;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): static
    {
        $this->file = $file;

        return $this;
    }
}
