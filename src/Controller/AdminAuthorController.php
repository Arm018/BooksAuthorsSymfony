<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminAuthorController extends AbstractController
{
    private $authorRepository;
    private $entityManager;

    public function __construct(AuthorRepository $authorRepository, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->authorRepository = $authorRepository;
    }

    /**
     * @Route("/admin/authors", name="app_admin_author", methods={"GET"})
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $authors = $this->authorRepository->findAll();
        return $this->render('admin/author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    /**
     * @Route("/admin/authors/create", name="app_admin_author_create", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($author);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_author');
        }

        return $this->render('admin/author/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/author/{id}/edit", name="app_admin_author_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Author $author): Response
    {
        $form = $this->createForm(AuthorType::class, $author);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_author');
        }

        return $this->render('admin/author/edit.html.twig', [
            'form' => $form->createView(),
            'author' => $author,
        ]);
    }

    /**
     * @Route("/admin/author/{id}/delete", name="app_admin_author_delete", methods={"POST"})
     */
    public function delete(Request $request, Author $author): Response
    {
        if ($this->isCsrfTokenValid('delete'.$author->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($author);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_author');
    }


}
