<?php 

namespace App\Controller;

use App\Entity\Product;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController {
    
    #[Route('/order/create-session-stripe/{reference}', name:'payment_stripe')]
    public function stripeCheckout($reference, EntityManagerInterface $manager, OrderRepository $orderRepo){

        //on cherche la commande qui correspond à la référence
        $order = $orderRepo->findOneBy(['reference'=> $reference]);
        
        //si elle n'existe pas, on redirige
        if(!$order){
            return $this->redirectToRoute('cart');
        }

        $productStripe = [];

        //On récupère les details de la commande
        $orderValues = $order->getCarts()->getValues();
        
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
                    'name'=> $transporter->getName()
                ]
            ],
            'quantity'=> 1
        ];

        // dd($productStripe);        
       
        $ordeReference = $order->getReference();

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY_TEST']);


        $checkout_session = Session::create([
        'customer_email'=> $this->getUser()->getEmail(),
        'payment_method_types'=>['card'],
        'line_items' => [[
            $productStripe
        ]],
        'mode' => 'payment',
        'success_url' => $this->generateUrl('payment_success', ['reference'=>$ordeReference], UrlGenerator::ABSOLUTE_URL),
        'cancel_url' => $this->generateUrl('payment_error', ['reference'=>$ordeReference], UrlGenerator::ABSOLUTE_URL),
        ]);



        return new RedirectResponse($checkout_session->url);
    }


    #[Route('/order/stripe/success/{reference}', name:'payment_success')]
    public function stripeSuccess($reference){

        return $this->render('order/success.html.twig');
    }

    #[Route('/order/stripe/error/{reference}', name:'payment_error')]
    public function stripeError($reference){

        return $this->render('order/error.html.twig');
    }



    
}