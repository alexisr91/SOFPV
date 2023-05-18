<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Form\OrderType;
use App\Manager\StripeManager;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use App\Repository\OrderStatusRepository;
use App\Repository\TransporterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'order')]
    public function index()
    {
        
    }

    //Préparation de la commande, choix de livraison
    #[Route('/order/verify/{reference}', name:'order_prepare')]
    #[IsGranted("ROLE_USER")]
    public function prepareOrder($reference, CartRepository $cartRepository, TransporterRepository $transporterRepository, EntityManagerInterface $manager, Request $request){

        $user = $this->getUser();
        $order = new Order();

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);


        //récupération de l'ensemble des paniers dont la référence est la même
        $carts = $cartRepository->findByCartsReference($reference);

        $transporters = $transporterRepository->findAll();
        
        //si il n'y a pas de correspondance on redirige vers le panier
        if($carts == []){
            return $this->redirectToRoute('cart');
        }
        //calcul du montant total pour la commande (somme des paniers)
        $total = 0;

        //nombre total d'objets
        $totalOfProducts = 0;
      
        $totalAmountForOrder=0;

        //pour chaque panier (groupe de produit => quantité )
        foreach($carts as $cart){
            if($user != $cart->getUser()){
                return $this->redirectToRoute('cart');
            }
            //on récupère les montants préalablement calculés
            $amount = $cart->getAmount();

            //nombre d'objets par Cart
            $quantity = $cart->getQuantity();

            //on les ajoute au total pour calculer le montant de la commande (ORDER)
            $total += $amount;

            $totalOfProducts += $quantity;

            //vérification du stock
            $stockAvailable = $cart->getProduct()->getStock();

            //si le stock n'est pas suffisant, on redirige vers le panier
            if($quantity > $stockAvailable){
                $this->addFlash('error','Attention, un des produits que vous avez sélectionné n\'est plus disponible en quantité suffisante. Merci de vérifier votre panier.');
                return $this->redirectToRoute('cart');
            }
        } 


        $deliveryChoice = $form->get('deliveryAddress')->getData();

        if($deliveryChoice == "user_address" && ($user->getAddress() == null || $user->getZip() == null || $user->getCity() == null ) ){
            $form->get('deliveryAddress')->addError(new FormError('Vous n\'avez pas indiqué d\'adresse : veuillez sélectionner l\'option "Livrer à une autre adresse" et remplir les champs nécessaires.'));
        } 

        if($form->isSubmitted() && $form->isValid()){
       
            foreach($carts as $cart){
                $order->addCart($cart);
            }
            
            $transporter = $form->get('transporter')->getData();

            //prix du transporteur à ajouter au total
            $transporterPrice = $transporter->getPrice();
    
            //prix + prix du transpoteur (en s'assurant que ça soit un float )
            $totalAmountForOrder = floatval($total + $transporterPrice);


            //si c'est l'adresse de l'user
            if($deliveryChoice == 'user_address'){

                //on récupère son adresse (on a déja vérifié que l'user connecté est celui qui à validé le panier)
                $userAddress = $cart->getUser()->getFullAddress();
               
                //on assigne la commande à l'adresse de cet user
                $order->setDeliveryAddress($userAddress);
            
            //si c'est une autre adresse de livraison    
             } elseif ($deliveryChoice == 'other_address'){

                //on récupère les données
                $address = $form->get('address')->getData();
                $zip = $form->get('zip')->getData();
                $city = $form->get('city')->getData();
 
                //On verifie que les champs ont bien été remplis
                if($address == null|| $zip == null|| $city== null){
                    return new JsonResponse(['error'=>'Vous devez indiquer une adresse valide pour finaliser votre commande.'], 400);
                }
                
                $addressComplement = $form->get('addressComplement')->getData();

                //on vérifie qu'il y a un complément d'adresse
                //et on stocke un string de l'adresse complère
                if($addressComplement){
                    $fullAddress = $address.'<br/>'.$addressComplement.'<br/>'.$zip.' '.$city;
                } else {
                    $fullAddress = $address.'<br/>'.$zip.' '.$city;
                }
                //On set l'adresse entière pour la commande
                $order->setDeliveryAddress($fullAddress);
            }
            

            $order->setTransporter($transporter)
                ->setPrice($totalAmountForOrder)
                ->setUser($user);
            
            $manager->persist($order);  
            $manager->flush();

            return $this->redirectToRoute('payment_stripe', ['reference'=> $order->getReference()]);
       
        }

       return $this->render('order/prepare.html.twig',
        ['title'=>'Création de la commande',
            'user'=>$this->getUser(),
            'reference'=>$reference,
            'carts'=>$carts,
            'total'=>$total,
            'totalQuantity'=>$totalOfProducts,
            'transporters'=>$transporters,
            'form'=>$form->createView()
        ]);
    }


}
