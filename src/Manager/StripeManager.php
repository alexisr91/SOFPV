<?php 

namespace App\Manager;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\Product;
use App\Services\StripeService;
use Doctrine\ORM\EntityManagerInterface;

class StripeManager{

    protected $manager;
    protected $stripeService;

    public function __construct(EntityManagerInterface $entityManager, StripeService $stripeService){
        $this->manager = $entityManager;
        $this->stripeService = $stripeService;
    }

    public function getProducts(){
        return $this->manager->getRepository(Product::class)->findAll();
    }

    public function intentSecret(Cart $cart){
        $intent = $this->stripeService->paymentIntent($cart);
        return $intent['client_secret'] ?? null;
    }

    public function stripe(array $stripeParam, Cart $cart)
    {

        $resource = null;
        $data = $this->stripeService->stripe($stripeParam , $cart->getReference());

        if ($data) {
            $resource = [
                'stripeBrand' => $data['charges']['data'][0]['payment_method_details']['card']['brand'],
                'stripeLast4' => $data['charges']['data'][0]['payment_method_details']['card']['last4'],
                'stripeId' => $data['charges']['data'][0]['id'],
                'stripeStatus' => $data['charges']['data'][0]['status'],
                'stripeToken' => $data['client_secret']
            ];
        }
        dd($resource);
        return $resource;
    }

    //crée une commande
    public function create_subscription(array $resource, Cart $cart){
        $order = new Order();
        $customer = $cart->getUser();

        $order->setUser($customer)
              ->setPrice($cart->getAmount())
              ->setReference(uniqid('', false))
              ->setBrandStripe($resource['stripeBrand'])
              ->setLast4Stripe($resource['stripeLast4'])
              ->setIdChargeStripe($resource['stripeId'])
              ->setStripeToken($resource['stripeToken'])
              ->setStatusStripe($resource['stripeStatus'])
              ->setDeliveryAddress($customer->getFulladdress())
              ->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        

        $this->manager->persist($order);
        $this->manager->flush();

        return $order->getReference();

    }

}