<?php

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Book;
use App\Entity\Colection;
use App\Form\AuthorFormType;
use App\Form\BookFormType;
use App\Form\ColectionFormType;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    
    #[Route('/book/new', name: 'new_book')]
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $book = new Book();

        $form = $this->createForm(BookFormType::class, $book);

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
    public function edit(ManagerRegistry $doctrine, Request $request, $id, SessionInterface $session, SluggerInterface $slugger): Response
    {
        if(!$this->getUser()) {
            $session->set('redirect', '/book/edit/' . $id);
            
            return $this->redirectToRoute('app_login', [
            ]);
        }
        $repositorio = $doctrine->getRepository(Book::class);
        $book = $repositorio->find($id);

        $form = $this->createForm(BookFormType::class, $book);
        $form->add('delete', SubmitType::class, array('label' => 'Borrar'));
            

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                if($form->get('delete')->isClicked()) {
                    return $this->redirectToRoute('delete_book', [
                        "id" => $book->getId()
                    ]);
                }
                $book = $form->getData();

                $file = $form->get('file')->getData();
                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                    try {

                        $file->move(
                            $this->getParameter('images_directory'), $newFilename
                        );
                        $filesystem = new Filesystem();
                        $filesystem->copy(
                            $this->getParameter('images_directory') . '/'. $newFilename, 
                            $this->getParameter('portfolio_directory') . '/'.  $newFilename, true);

                    } catch (FileException $e) {
                        return new Response("Error" . $e->getMessage());
                    }
                    
                    $book->setFile($newFilename);
                }

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
                return $this->redirectToRoute('inicio', [
                ]);
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

    #[Route('/author/new', name: 'new_author')]
    public function newAuthor(ManagerRegistry $doctrine, Request $request): Response
    {
        $author = new author();

        $form = $this->createForm(AuthorFormType::class, $author);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $author = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($author);

                try {
                    $entityManager->flush();
                    return $this->redirectToRoute('app_author', [
                        "id" => $author->getId()
                    ]);
                } catch (\Exception $e) {
                    return new Response("Error" . $e->getMessage());
                }
            }

            return $this->render('form_author.html.twig', array(
                'formulario' => $form->createView()
            ));
    }

    #[Route('/author/edit/{id}', name: 'edit_Author')]
    public function editAuthor(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $repositorio = $doctrine->getRepository(Author::class);
        $author = $repositorio->find($id);

        $form = $this->createForm(AuthorFormType::class, $author);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $author = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($author);

                try {
                    $entityManager->flush();
                    return $this->redirectToRoute('app_author', [
                        "id" => $author->getId()
                    ]);
                } catch (\Exception $e) {
                    return new Response("Error" . $e->getMessage());
                }
            }

            return $this->render('form_author.html.twig', array(
                'formulario' => $form->createView()
            ));
    }

    #[Route('/author/{id}', name: 'app_author')]
    public function author(ManagerRegistry $doctrine, int $id): Response
    {
        $repositorio = $doctrine->getRepository(Author::class);
        $author = $repositorio->find($id);

        if ($author) {
            return $this->render('author.html.twig', [
                'author' => $author
            ]);
        } else {
            return $this->render('author.html.twig', [
                'author' => null
            ]);
        }
        
    }

    #[Route('/colection/new', name: 'new_colection')]
    public function newcolection(ManagerRegistry $doctrine, Request $request): Response
    {
        $colection = new Colection();

        $form = $this->createForm(ColectionFormType::class, $colection);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $colection = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($colection);

                try {
                    $entityManager->flush();
                    return $this->redirectToRoute('app_colections');
                } catch (\Exception $e) {
                    return new Response("Error" . $e->getMessage());
                }
            }

            return $this->render('form_colection.html.twig', array(
                'formulario' => $form->createView()
            ));
    }

    #[Route('/colection/edit/{id}', name: 'edit_colection')]
    public function editcolection(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $repositorio = $doctrine->getRepository(Colection::class);
        $colection = $repositorio->find($id);

        $form = $this->createForm(ColectionFormType::class, $colection);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $colection = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($colection);

                try {
                    $entityManager->flush();
                    return $this->redirectToRoute('app_colections');
                } catch (\Exception $e) {
                    return new Response("Error" . $e->getMessage());
                }
            }

            return $this->render('form_colection.html.twig', array(
                'formulario' => $form->createView()
            ));
    }

    #[Route('/colections', name: 'app_colections')]
    public function colections(ManagerRegistry $doctrine): Response
    {
        $repositorio = $doctrine->getRepository(Colection::class);
        $colections = $repositorio->findAll();

        if ($colections) {
            return $this->render('colections.html.twig', [
                'colections' => $colections
            ]);
        } else {
            return $this->render('colections.html.twig', [
                'colections' => null
            ]);
        }
        
    }

}
