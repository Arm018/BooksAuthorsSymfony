<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Repository\BookRepository;
use App\Repository\BalanceRepository;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class OrderController extends AbstractController
{
    private $orderRepository;
    private $bookRepository;
    private $balanceRepository;
    private $entityManager;

    public function __construct(OrderRepository $orderRepository, BookRepository $bookRepository, BalanceRepository $balanceRepository, EntityManagerInterface $entityManager)
    {
        $this->orderRepository = $orderRepository;
        $this->bookRepository = $bookRepository;
        $this->balanceRepository = $balanceRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/order", name="app_order")
     */
    public function index(): Response
    {
        $orders = $this->orderRepository->findBy(['user' => $this->getUser()]);
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/order/create", name="app_order_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $selectedBooks = $request->request->get('selected_books', []);
        $quantities = $request->request->get('quantity', []);

        if (empty($selectedBooks)) {
            $this->addFlash('error', 'Please select at least one book.');
            return $this->redirectToRoute('app_books');
        }

        $user = $this->getUser();
        $balance = $this->balanceRepository->findOneBy(['user' => $user]);

        $totalPrice = 0;
        foreach ($selectedBooks as $bookId) {
            $book = $this->bookRepository->find($bookId);
            if ($book) {
                $quantity = $quantities[$bookId] ?? 0;
                $totalPrice += $book->getPrice() * $quantity;
            }
        }

        if ($balance->getAmount() < $totalPrice) {
            $this->addFlash('error', 'Insufficient balance to complete this order.');
            return $this->redirectToRoute('app_books');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($totalPrice);
        $order->setCreatedAt(Carbon::now());
        $order->setUpdatedAt(Carbon::now());
        $this->entityManager->persist($order);

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

        $balance->setAmount($balance->getAmount() - $totalPrice);
        $this->entityManager->persist($balance);

        $this->entityManager->flush();

        $this->addFlash('success', 'Order has been created successfully.');
        return $this->redirectToRoute('app_order');
    }

    /**
     * @Route("/order/{id}", name="app_order_show", methods={"GET"})
     */
    public function show($id): Response
    {
        $order = $this->orderRepository->find($id);
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }
}
