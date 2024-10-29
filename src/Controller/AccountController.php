<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @Route("/account", name="app_account")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $balance = $user->getBalance();
        $amount = $balance->getAmount();

        $orders = $this->orderRepository->findBy(['user' => $user]);

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'balance' => $amount,
            'orders' => $orders,
        ]);
    }
}
