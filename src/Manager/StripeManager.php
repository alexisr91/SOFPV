<?php

namespace App\Manager;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Order;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class StripeManager
{
    protected $manager;
    protected $stripeService;

    public function __construct(EntityManagerInterface $entityManager, mixed $stripeService)
    {
        $this->manager = $entityManager;
        $this->stripeService = $stripeService;
    }

    public function getProducts()
    {
        return $this->manager->getRepository(Product::class)->findAll();
    }

    public function intentSecret(Cart $cart)
    {
        $intent = $this->stripeService->paymentIntent($cart);

        return $intent['client_secret'] ?? null;
    }

    public function stripe(array $stripeParam, Cart $cart)
    {
        $resource = null;
        $data = $this->stripeService->stripe($stripeParam, $cart->getReference());

        if ($data) {
            $resource = [
                'stripeId' => $data['charges']['data'][0]['id'],
                'stripeStatus' => $data['charges']['data'][0]['status'],
            ];
        }

        return $resource;
    }

    // crée une commande
    public function createSubscription(array $resource, Cart $cart)
    {
        $order = new Order();

         /** @var User $customer */
        $customer = $cart->getUser();

        $order->setUser($customer)
              ->setPrice($cart->getAmount())
              ->setReference(uniqid('', false))
              ->setStatusStripe($resource['stripeStatus'])
              ->setDeliveryAddress($customer->getFulladdress())
              ->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        $this->manager->persist($order);
        $this->manager->flush();

        return $order->getReference();
    }
}
