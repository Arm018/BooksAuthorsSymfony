<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminBookController extends AbstractController
{
    private $entityManager;
    private $bookRepository;

    public function __construct(EntityManagerInterface $entityManager, BookRepository $bookRepository)
    {
        $this->entityManager = $entityManager;
        $this->bookRepository = $bookRepository;
    }

    /**
     * @Route("/admin/books", name="app_admin_book", methods={"GET"})
     */
    public function index(): Response
    {
        $books = $this->bookRepository->findAll();

        return $this->render('admin/book/index.html.twig', [
            'books' => $books,
        ]);
    }

    /**
     * @Route("/admin/books/create", name="app_admin_book_create", methods={"GET", "POST"})
     */
    public function create(Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($book);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_book');
        }

        return $this->render('admin/book/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/books/{id}/edit", name="app_admin_book_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Book $book): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_book');
        }

        return $this->render('admin/book/edit.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
        ]);
    }

    /**
     * @Route("/admin/books/{id}/delete", name="app_admin_book_delete", methods={"POST"})
     */
    public function delete(Request $request, Book $book): Response
    {
        if ($this->isCsrfTokenValid('delete' . $book->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($book);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_book');
    }
}
