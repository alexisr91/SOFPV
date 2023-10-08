<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\AdminOrderType;
use App\Repository\OrderRepository;
use App\Repository\OrderStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminOrdersController extends AbstractController
{
    // list of order to prepare
    // liste des commandes à traiter
    #[Route('/admin/orders', name: 'admin_orders')]
    public function index(OrderRepository $orderRepository, OrderStatusRepository $orderStatusRepository): Response
    {
        // status wich corresponds to an order to prepare
        // status correspondant à une commande en attente de traitement
        $status = $orderStatusRepository->findOneBy(['status' => 0]);

        // give orders to prepare, the older first
        // on récupère les commandes en attente de traitement
        $orders = $orderRepository->findBy(['delivery_status' => $status], ['delivery_status' => 'ASC']);

        return $this->render('admin/orders/index.html.twig', [
            'title' => 'Gestion des commandes à traiter',
            'orders' => $orders,
        ]);
    }

    // all orders
    // toutes les commandes
    #[Route('admin/orders/all', name: 'admin_orders_all')]
    public function allOrders(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/orders/allOrders.html.twig', [
            'title' => 'Toutes les commandes',
            'orders' => $orders,
        ]);
    }

    // modify an order to update his peparation status
    // modifier une commande pour mettre à jour son status de livraison et de traitement
    #[Route('admin/order/modify/{id}', name: 'admin_order_modify')]
    public function modify(Order $order, Request $request, EntityManagerInterface $manager, OrderRepository $orderRepository, int $id): Response
    {
        $checkOrder = $orderRepository->findOneBy(['id' => $id]);

        $form = $this->createForm(AdminOrderType::class, $order);
        $form->handleRequest($request);

        if ($checkOrder) {
            if ($form->isSubmitted() && $form->isValid()) {
                // modify delivery address
                // MODIFICATION DE L'ADRESSE DE LIVRAISON

                $address = $form->get('address')->getData();
                $zip = $form->get('zip')->getData();
                $city = $form->get('city')->getData();
                $addressComplement = $form->get('addressComplement')->getData();

                // check address complement + stock it on a string with full address
                // on vérifie qu'il y a un complément d'adresse
                // et on stocke un string de l'adresse complète
                if ($addressComplement) {
                    $fullAddress = $address.'<br/>'.$addressComplement.'<br/>'.$zip.' '.$city;
                } else {
                    $fullAddress = $address.'<br/>'.$zip.' '.$city;
                }

                // check if inputs are filled
                // On verifie que les champs ont bien été remplis
                if (null == !$address || null == !$zip || null == !$city) {
                    $order->setDeliveryAddress($fullAddress);
                }

                // update delivery status
                // UPDATE DU STATUT DE LIVRAISON
                $status = $form->get('delivery_status')->getData();

                // if status is "remis au transporteur" (handed to transporter), we need to give a tracking number
                // si le statut est = remis au transporteur, on requiert un numéro de suivi

                if ('0' !== $status->getStatus()) {
                    $trackerId = $form->get('trackerID')->getData();
                    if (!$trackerId) {
                        return new JsonResponse(['error' => 'Vous devez indiquer un numéro de suivi.'], 400);
                    } else {
                        $order->setTrackerID($trackerId);
                    }
                }

                $manager->persist($order);
                $manager->flush();

                $this->addFlash('success', 'La commande a bien été modifiée.');

                return $this->redirectToRoute('admin_orders_all');
            }
        } else {
            $this->addFlash('danger', 'La commande n\'a pas été trouvée.');

            return $this->redirectToRoute('admin_orders');
        }

        return $this->render('admin/orders/modify.html.twig', [
            'title' => 'Modification de la commande',
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    // order cancelling
    // annulation de la commande
    #[Route('admin/order/cancelation/{id}', name: 'admin_order_cancel')]
    public function cancelation(Order $order, EntityManagerInterface $manager, OrderRepository $orderRepository, OrderStatusRepository $orderStatusRepository, int $id, Request $request): Response
    {
        // check token to avoid csrf manipulation
        $token = $request->request->get('token');

        if ($this->isCsrfTokenValid('cancel', $token)) {
            // check if Symfony give us le right ID
            $checkOrder = $orderRepository->findBy(['id' => $id]);
            // status "cancelled" for the order => 4
            $status = $orderStatusRepository->findOneBy(['status' => '4']);

            if ($checkOrder) {
                // set the cancelled status before send on database
                $order->setDeliveryStatus($status);
                $manager->persist($order);
                $manager->flush();

                $this->addFlash('success', 'La commande a bien été annulée.');

                return $this->redirectToRoute('admin_orders_all');
            } else {
                $this->addFlash('danger', 'La commande n\'existe pas.');

                return $this->redirectToRoute('admin_orders_all');
            }
            // if token isn't valid
        } else {
            throw new BadRequestHttpException();
        }
    }
}
