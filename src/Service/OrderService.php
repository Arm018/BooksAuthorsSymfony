<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\BookRepository;
use App\Repository\BalanceRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class OrderService
{
    private $bookRepository;
    private $balanceRepository;
    private $entityManager;
    private $security;

    public function __construct(
        BookRepository         $bookRepository,
        BalanceRepository      $balanceRepository,
        EntityManagerInterface $entityManager,
        Security               $security
    )
    {
        $this->bookRepository = $bookRepository;
        $this->balanceRepository = $balanceRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function createOrder(array $selectedBooks, array $quantities): void
    {
        $user = $this->security->getUser();
        $balance = $this->balanceRepository->findOneBy(['user' => $user]);

        $totalPrice = $this->calculateTotalPrice($selectedBooks, $quantities);

        $this->checkBalance($balance, $totalPrice);

        $order = $this->createNewOrder($user, $totalPrice);
        $this->addOrderItems($order, $selectedBooks, $quantities);
        $this->updateUserBalance($balance, $totalPrice);

        $this->entityManager->flush();
    }

    private function calculateTotalPrice(array $selectedBooks, array $quantities): float
    {
        $totalPrice = 0;
        foreach ($selectedBooks as $bookId) {
            $book = $this->bookRepository->find($bookId);
            if ($book) {
                $quantity = $quantities[$bookId] ?? 0;
                $totalPrice += $book->getPrice() * $quantity;
            }
        }
        return $totalPrice;
    }

    private function checkBalance($balance, $totalPrice)
    {
        if ($balance->getAmount() < $totalPrice) {
            throw new \RuntimeException('Խնդրում ենք վերալիցքավորել ձեր հաշվեկշիռը.');
        }
    }

    private function createNewOrder($user, float $totalPrice): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($totalPrice);
        $order->setCreatedAt(Carbon::now());
        $order->setUpdatedAt(Carbon::now());
        $this->entityManager->persist($order);

        return $order;
    }

    private function addOrderItems(Order $order, array $selectedBooks, array $quantities): void
    {
        foreach ($selectedBooks as $bookId) {
            $book = $this->bookRepository->find($bookId);
            $quantity = $quantities[$bookId] ?? 0;

            if ($book && $quantity > 0) {
                $orderItem = new OrderItem();
                $orderItem->setOrder($order);
                $orderItem->setBook($book);
                $orderItem->setQuantity($quantity);
                $orderItem->setPrice($book->getPrice());
                $orderItem->setTotalPrice($book->getPrice() * $quantity);

                $this->entityManager->persist($orderItem);
            }
        }
    }

    private function updateUserBalance($balance, float $totalPrice): void
    {
        $balance->setAmount($balance->getAmount() - $totalPrice);
        $this->entityManager->persist($balance);
    }
}
