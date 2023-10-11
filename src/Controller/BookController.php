<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    private $books = [
        1 => ["titulo" => "El Reino Perdido", "autor" => "Jeniffer H. Lowner", "editorial" => "Planeta", "paginas" => "329"],
        2 => ["titulo" => "Memorias de Idhún I", "autor" => "Laura Gallego", "editorial" => "Santillana", "paginas" => "527"],
        3 => ["titulo" => "Memorias de Idhún II", "autor" => "Laura Gallego", "editorial" => "Santillana", "paginas" => "732"],
        5 => ["titulo" => "Cazadora de Hadas", "autor" => "Chris J. Hans", "editorial" => "Nocturna", "paginas" => "246"],
        7 => ["titulo" => "Chicas como Nosotras", "autor" => "Loren K. Jons", "editorial" => "Planeta", "paginas" => "354"],
        9 => ["titulo" => "Heima es hogar en islandés", "autor" => "Andrea Tomé", "editorial" => "Nocturna", "paginas" => "386"]
    ]; 

    #[Route('/book/{id}', name: 'app_book')]
    public function book(int $id): Response
    {
        $book = ($this->books[$id] ?? null);

        return $this->render('book.html.twig', [
            'book' => $book
        ]);
    }

    #[Route('/book/search/{text}', name: 'search_book')]
    public function search($text): Response
    {
        $books = array_filter($this->books, function ($book) use ($text){
            return strpos($book["titulo"], $text) !== FALSE;
        });

        return $this->render('books.html.twig', [
            'books' => $books,
            'text' => $text
        ]);
    }
}
