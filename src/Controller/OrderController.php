<?php

namespace App\Controller;

use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @Route("/order/create", name="app_order_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $selectedBooks = $request->request->get('selected_books', []);
        $quantities = $request->request->get('quantity', []);

        if (empty($selectedBooks)) {
            $this->addFlash('danger', 'Խնդրում ենք ընտրել գոնե մեկ գիրք։');
            return $this->redirectToRoute('app_books');
        }

        try {
            $this->orderService->createOrder($selectedBooks, $quantities);
            $this->addFlash('success', 'Դուք հաջողությամբ ձեռք բերեցիք ձեր ընտրած գրքերը։');
        } catch (\RuntimeException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }
}
