<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\User;
use Stripe\Checkout\Session;
use App\Repository\OrderRepository;
use App\Repository\OrderStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use App\Entity\User;
use Stripe\Checkout\Session;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    // checkout stripe
    #[Route('/order/create-session-stripe/{reference}', name: 'payment_stripe')]
    public function stripeCheckout(string $reference, OrderRepository $orderRepo) : RedirectResponse
    {
        // on cherche la commande qui correspond à la référence
        // searching for order which match with reference
        $ordering = $orderRepo->findOneBy(['reference' => $reference]);

        // si elle n'existe pas, on redirige
        // if it doesn't exist, we redirect the user
        if (!$ordering) {
            return $this->redirectToRoute('cart');
        }

        $productStripe = [];

        // On récupère les details de la commande
        // we recover order values
        $orderValues = $ordering->getCarts()->getValues();

        // On boucle pour chaque élément de la commande
        // loop on each element of the order
        foreach ($orderValues as $order) {
            // on récupère le produit
            // we recover the product
            $product = $order->getProduct();

            // valeurs demandées par Stripe
            // values required by Stripe
            $productStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPriceTTC() * 100,
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                ],
                'quantity' => $order->getQuantity(),
            ];
        }

        // ajout du prix du transporteur par stripe
        // add transporter's price by Stripe
        $transporter = $order->getOrdering()->getTransporter();
        $transporterPrice = $transporter->getPrice();

        $productStripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $transporterPrice * 100,
                'product_data' => [
                    'name' => $transporter->getName(),
                    'description' => $transporter->getDescription(),
                ],
            ],
            'quantity' => 1,
        ];

        // on récupère la référence de la commande (pas celle qui est commune aux paniers)
        // recovering the reference of the order (not that wich is the same for each carts)
        $orderReference = $order->getOrdering()->getReference();

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY_TEST']);

        /** @var User $user */
        $user = $this->getUser();

        $checkout_session = Session::create([
        'customer_email' => $user->getEmail(),
        'payment_method_types' => ['card'],
        'line_items' => [[
            $productStripe,
        ]],
        'mode' => 'payment',
        'success_url' => $this->generateUrl('payment_success', ['reference' => $orderReference], UrlGenerator::ABSOLUTE_URL).'?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $this->generateUrl('payment_error', ['reference' => $orderReference], UrlGenerator::ABSOLUTE_URL),
        ]);

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('/order/stripe/success/{reference}', name: 'payment_success')]
    #[IsGranted('ROLE_USER')]
    public function stripeSuccess(string $reference, SessionInterface $sessionCart, OrderRepository $orderRepo, OrderStatusRepository $orderStatusRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY_TEST']);
        $orderStatus = $orderStatusRepository->findOneBy(['status' => 0]);

        try {
            $session = $stripe->checkout->sessions->retrieve($request->query->get('session_id'), ['expand' => ['payment_intent']]);
            $customer = $stripe->customers->retrieve($session->customer);

            http_response_code(200);

            if ('succeeded' == $session->payment_intent->status && 'paid' == $session->payment_status) {
                // si la commande est validée, on supprime les données de session des paniers
                $sessionCart->set('cart', []);
                $sessionCart->set('tempStock', []);

                // on récupère la commande
                $order = $orderRepo->findOneBy(['reference' => $reference]);
                // récupération des données stripe
                $status = $session->payment_intent->status;
                $paymentIntent = $session->payment_intent->id;

                // on met à jour les données de la commande "order" associée, avec de quoi retracer le paiement sur le site STRIPE (customer id et payment intent id)
                $order->setStatusStripe($status)
                      ->setStripePaymentIntent($paymentIntent)
                      ->setStripeCustomerId($customer->id)
                      ->setDeliveryStatus($orderStatus);
                $manager->persist($order);

                // on récupère les données des paniers
                $carts = $order->getCarts()->getValues();

                // maj des stocks
                foreach ($carts as $cart) {
                    $product = $cart->getProduct();
                    $quantity = $cart->getQuantity();
                    $stock = $product->getStock();

                    $newStock = (int) $stock - $quantity;

                    if ($newStock < 0) {
                        http_response_code(500);
                    } else {
                        $product->setStock($stock - $quantity);
                        $manager->persist($product);
                    }
                }

                // envoie de la maj en bdd
                $manager->flush();
            }
        } catch (\Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

        return $this->render('order/success.html.twig', [
            'title' => 'Merci pour votre commande !',
            'order' => $order,
            'carts' => $carts,
            'customer' => $customer,
        ]);
    }

    #[Route('/order/stripe/error/{reference}', name: 'payment_error')]
    #[IsGranted('ROLE_USER')]
    public function stripeError(string $reference): Response
    {
        return $this->render('order/error.html.twig', [
            'title' => 'Erreur lors de la transaction',
            'reference' => $reference,
        ]);
    }
}
