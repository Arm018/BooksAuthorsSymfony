<?php

namespace App\Service;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;

class AuthorService
{
    private $authorRepository;
    private $entityManager;

    public function __construct(AuthorRepository $authorRepository, EntityManagerInterface $entityManager)
    {
        $this->authorRepository = $authorRepository;
        $this->entityManager = $entityManager;
    }

    public function getAllAuthors(): array
    {
        return $this->authorRepository->findAll();
    }

    public function createAuthor(Author $author): void
    {
        $this->entityManager->persist($author);
        $this->entityManager->flush();
    }

    public function updateAuthor(): void
    {
        $this->entityManager->flush();
    }

    public function deleteAuthor(Author $author): void
    {
        $this->entityManager->remove($author);
        $this->entityManager->flush();
    }
}
