<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titulo = null;

    /* #[ORM\Column(length: 255)]
    private ?string $autor = null;
 */
    #[ORM\Column(length: 255)]
    private ?string $editorial = null;

    #[ORM\Column]
    private ?int $paginas = null;

    #[ORM\ManyToOne(inversedBy: 'books')]
    private ?Colection $colection = null;

    #[ORM\ManyToOne(inversedBy: 'books')]
    private ?Author $autor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    /* public function getAutor(): ?string
    {
        return $this->autor;
    }

    public function setAutor(string $autor): static
    {
        $this->autor = $autor;

        return $this;
    } */

    public function getEditorial(): ?string
    {
        return $this->editorial;
    }

    public function setEditorial(string $editorial): static
    {
        $this->editorial = $editorial;

        return $this;
    }

    public function getPaginas(): ?int
    {
        return $this->paginas;
    }

    public function setPaginas(int $paginas): static
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
}
