<?php

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Book;
use App\Entity\Colection;
use Doctrine\Persistence\ManagerRegistry;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

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
    
    #[Route('/book/nuevo', name: 'nuevo_book')]
    public function nuevo(ManagerRegistry $doctrine, Request $request): Response
    {
        $book = new Book();

        $form = $this->createFormBuilder($book)
            ->add('titulo', TextType::class)
            ->add('autor', EntityType::class, array(
                'class' => Author::class,
                'choice_label' => 'name',
                'placeholder' => '',
                'required' => true,))
            ->add('editorial', TextType::class)
            ->add('paginas', NumberType::class)
            ->add('colection', EntityType::class, array(
                'class' => Colection::class,
                'choice_label' => 'name',
                'placeholder' => '',
                'required' => false,))
            ->add('save', SubmitType::class, array('label' => 'Enviar'))
            ->getForm();

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $book = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($book);

                try {
                    $entityManager->flush();
                    return $this->redirectToRoute('app_book', [
                        "id" => $book->getId()
                    ]);
                } catch (\Exception $e) {
                    return new Response("Error" . $e->getMessage());
                }
            }

            return $this->render('form.html.twig', array(
                'formulario' => $form->createView()
            ));
    }

    #[Route('/book/edit/{id}', name: 'edit_book')]
    public function edit(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $repositorio = $doctrine->getRepository(Book::class);
        $book = $repositorio->find($id);

        $form = $this->createFormBuilder($book)
            ->add('titulo', TextType::class)
            ->add('autor', EntityType::class, array(
                'class' => Author::class,
                'choice_label' => 'name',
                'placeholder' => '',
                'required' => true,))
            ->add('editorial', TextType::class)
            ->add('paginas', NumberType::class)
            ->add('colection', EntityType::class, array(
                'class' => Colection::class,
                'choice_label' => 'name',
                'placeholder' => '',
                'required' => false,))
            ->add('save', SubmitType::class, array('label' => 'Enviar'))
            ->getForm();

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $book = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($book);

                try {
                    $entityManager->flush();
                    return $this->redirectToRoute('app_book', [
                        "id" => $book->getId()
                    ]);
                } catch (\Exception $e) {
                    return new Response("Error" . $e->getMessage());
                }
            }

            return $this->render('form.html.twig', array(
                'formulario' => $form->createView()
            ));
    }

    #[Route('/book/insert', name: 'insert_book')]
    public function insert(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        foreach ($this->books as $b) {
            $book = new Book();
            $book->setTitulo($b["titulo"]);
            $book->setEditorial($b["editorial"]);
            $book->setPaginas($b["paginas"]);
            $entityManager->persist($book);
        }

        try
        {
            $entityManager->flush();
            return new Response("Datos introducidos correctamente");
        } catch (\Exception $e) {
            return new Response("Error insertando los objetos. " . $e->getMessage());
        } 
    }

    #[Route('/book/insertColection', name: 'insert_colection_book')]
    public function insertColection(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $colection = new Colection();
        $colection->setName("Memorias de Idhun");
        $entityManager->persist($colection);


        $repositorio = $doctrine->getRepository(Book::class);
        
        $books = $repositorio->findByTitle("Memorias");
        foreach ($books as $book) {
            $book->setColection($colection);
            $entityManager->persist($book);
        }

        try
        {
            $entityManager->flush();
            return $this->render('books.html.twig', [
                'books' => $books,
            ]);        
        } catch (\Exception $e) {
            return new Response("Error insertando los objetos. " . $e->getMessage());
        } 
    }

    #[Route('/book/update/{id}/{titulo}', name: 'update_book')]
    public function update(ManagerRegistry $doctrine, int $id, $titulo): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Book::class);
        $book = $repositorio->find($id);

        if($book){
            $book->setTitle($titulo);
            try
            {
                $entityManager->flush();
                return $this->render('book.html.twig', [
                    'book' => $book
                ]);
            } catch (\Exception $e) {
                return new Response("Error modificando el objeto. " . $e->getMessage());
            } 
        } else {
            return $this->render('book.html.twig', [
                'book' => null
            ]);
        }
    }

    #[Route('/book/delete/{id}', name: 'delete_book')]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Book::class);
        $book = $repositorio->find($id);

        if($book){
            try
            {
                $entityManager->remove($book);
                $entityManager->flush();
                return new Response("Libro eliminado");
            } catch (\Exception $e) {
                return new Response("Error eliminando el objeto. " . $e->getMessage());
            } 
        } else {
            return $this->render('book.html.twig', [
                'book' => null
            ]);
        }
    }

    #[Route('/book/{id}', name: 'app_book')]
    public function book(ManagerRegistry $doctrine, int $id): Response
    {
        $repositorio = $doctrine->getRepository(Book::class);
        $book = $repositorio->find($id);

        if ($book) {
            return $this->render('book.html.twig', [
                'book' => $book
            ]);
        } else {
            return $this->render('book.html.twig', [
                'book' => null
            ]);
        }
        
    }

    #[Route('/book/search/{text}', name: 'search_book')]
    public function search(ManagerRegistry $doctrine, $text): Response
    {
        $repositorio = $doctrine->getRepository(Book::class);
        
        $books = $repositorio->findByTitle($text);

        return $this->render('books.html.twig', [
            'books' => $books,
            'text' => $text
        ]);
    }

    #[Route('/book/search/pages/{pages}', name: 'searchByPages_book')]
    public function searchByPages(ManagerRegistry $doctrine, $pages): Response
    {
        $repositorio = $doctrine->getRepository(Book::class);
        
        $books = $repositorio->findByPages($pages);

        return $this->render('pages.html.twig', [
            'books' => $books,
            'paginas' => $pages
        ]);
    }
}
