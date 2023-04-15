<?php 

namespace App\Controller;

use Error;
use Stripe\Stripe;
use App\Entity\Product;
use Stripe\Checkout\Session;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController {
    
    #[Route('/order/create-session-stripe/{reference}', name:'payment_stripe')]
    public function stripeCheckout($reference, OrderRepository $orderRepo){

        //on cherche la commande qui correspond à la référence
        $ordering = $orderRepo->findOneBy(['reference'=> $reference]);
        
        //si elle n'existe pas, on redirige
        if(!$ordering){
            return $this->redirectToRoute('cart');
        }

        $productStripe = [];

        //On récupère les details de la commande
        $orderValues = $ordering->getCarts()->getValues();
        
        //On boucle pour chaque élément de la commande
        foreach($orderValues as $order){

            //on récupère le produit
            $product = $order->getProduct();

            //valeurs demandées par Stripe
            $productStripe[] = [
                'price_data'=> [
                    'currency'=> 'eur',
                    'unit_amount'=> $product->getPriceTTC()*100,
                    'product_data'=> [
                        'name'=>$product->getName()
                    ]
                ],
                'quantity'=> $order->getQuantity()
                
            ];
        }

        //ajout du prix du transporteur par stripe
        $transporter = $order->getOrdering()->getTransporter();
        $transporterPrice = $transporter->getPrice();

        $productStripe[] = [
            'price_data'=> [
                'currency'=> 'eur',
                'unit_amount'=> $transporterPrice*100,
                'product_data'=> [
                    'name'=> $transporter->getName(),
                    'description'=>$transporter->getDescription()
                ]
            ],
            'quantity'=> 1
        ];

        // dd($productStripe);    

       // on récupère la référence de la commande (pas celle qui est commune aux paniers)
        $orderReference = $order->getOrdering()->getReference();

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY_TEST']);


        $checkout_session = Session::create([
        'customer_email'=> $this->getUser()->getEmail(),
        'payment_method_types'=>['card'],
        'line_items' => [[
            $productStripe
        ]],
        'mode' => 'payment',
        'success_url' => $this->generateUrl('payment_success',['reference'=>$orderReference], UrlGenerator::ABSOLUTE_URL).'?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $this->generateUrl('payment_error',[], UrlGenerator::ABSOLUTE_URL),
        ]);
        // dd($checkout_session->id);
      
        return new RedirectResponse($checkout_session->url);
  
    }


    
    #[Route('/order/stripe/success/{reference}', name:'payment_success')]
    #[IsGranted("ROLE_USER")]
    public function stripeSuccess($reference, SessionInterface $sessionCart, OrderRepository $orderRepo,Request $request, EntityManagerInterface $manager ){

        $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY_TEST']);

        try {
            $session = $stripe->checkout->sessions->retrieve($request->query->get('session_id'), ['expand'=>['payment_intent']]);
            $customer = $stripe->customers->retrieve($session->customer);
            
            http_response_code(200);


            if($session->payment_intent->status == 'succeeded' && $session->payment_status == 'paid'){
                //si la commande est validée, on supprime les données de session des paniers
                $sessionCart->set('cart', []);

                //on récupère la commande
                $order = $orderRepo->findOneBy(['reference'=>$reference]);
                //récupération des données stripe
                $status = $session->payment_intent->status;
                $paymentIntent = $session->payment_intent->id;

                //on met à jour les données de la commande "order" associée avec de quoi retracer le paiment sur le site STRIPE (customer id et payment intent id)
                $order->setStatusStripe($status)
                      ->setStripePaymentIntent($paymentIntent)
                      ->setStripeCustomerId($customer->id);
                $manager->persist($order);
                

                //on récupère les données des paniers
                $carts = $order->getCarts()->getValues();
                
                //maj des stocks
                foreach ($carts as $cart){
                    $product = $cart->getProduct();
                    $quantity = $cart->getQuantity();
                    $stock = $product->getStock();

                    $product->setStock($stock - $quantity);
                    $manager->persist($product);
                }
                //envoie de la maj en bdd
                $manager->flush();
            }
           
        

        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        

        return $this->render('order/success.html.twig', [
            'title'=>'Merci pour votre commande !',
            'order'=> $order,
            'carts'=>$carts,
            'customer'=>$customer
        ]);
    }

    #[Route('/order/stripe/error', name:'payment_error')]
    #[IsGranted("ROLE_USER")]
    public function stripeError($reference){

        return $this->render('order/error.html.twig', [
            'title'=>'Erreur lors de la transaction',
            'reference'=>$reference
        ]);
    }



    
}