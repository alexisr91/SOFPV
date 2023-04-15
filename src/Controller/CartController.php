<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Services\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CartController extends AbstractController
{

    private $reference;

    //Page de récapitulatif du panier
    #[Route('/cart', name: 'cart')]
    public function index(SessionInterface $session, ProductRepository $productRepo): Response
    {
        //on récupère les données de session concernant le panier
        $cart = $session->get('cart', []);

        //on va stocker les données dans un tableau
        $cartData = [];
        $total = 0;

        //pour chaque entrée de session concernant le cart (panier)
        foreach($cart as $id => $quantity){
            //on trouve le produit qui correspond à l'id (pas possible d'ajouter un produit qui n'existe pas)
            $product = $productRepo->find($id);
            //on y associe le int qui correspond à la quantité (nombre de fois où on a ajouté au panier le même produit)
            $cartData[] = [
                "product"=>$product,
                "quantity"=>$quantity
            ];
            //pour le total on multiplie le prix par la quantité
            $total += $product->getPriceTTC()*$quantity;
        }
    

        return $this->render('shop/cart/index.html.twig', [
            'title' => 'Votre panier',
            'cardData'=>$cartData,
            'total'=>$total
        ]);
    }

    //Ajout d'un produit dans le panier dans la page récapitulative
    #[Route('/cart/add/{id}', name: 'cart_add' )]
    public function add(Product $product, SessionInterface $session ){

        $id = $product->getId();
       
        //récupération du panier actuel
        //On récupère les données de session du panier, la valeur par défaut sera un array vide
        $cart = $session->get("cart", []); 

        //si le panier est vide on met la quantité à 1
        if(empty($cart[$id])){
            $cart[$id] = 1;     
        //si il est déjà présent on l'incrémente de 1    
        } else {
           $cart[$id]++ ;
        }

    
      //Sauvegarde dans la session
      $session->set('cart', $cart);

    
      return $this->redirectToRoute('cart');
      
    }

    #[Route('/cart/validate', name:"cart_validate")]
    #[IsGranted("ROLE_USER")]
    public function validate(SessionInterface $session, ProductRepository $productRepo, EntityManagerInterface $manager){

        $sessionCart = $session->get('cart');
        $cartData = [];
        $total = 0;
        $user = $this->getUser();

        $carts = [];
        //on assigne une référence qui sera propre à chaque commande (chaque reférence correspond à des entité Cart qui sont des lots de produits)
        $referenceForOrder = uniqid('', false);
        
        //pour chaque entrée de session concernant le cart (panier)
        foreach($sessionCart as $id => $quantity){
            //on trouve le produit qui correspond à l'id (pas possible d'ajouter un produit qui n'existe pas)
            $product = $productRepo->find($id);
            //on y associe le int qui correspond à la quantité (nombre de fois où on a ajouté au panier le même produit)
            $cartData[] = [
                "product"=>$product,
                "quantity"=>$quantity
            ];
            //pour le total on multiplie le prix par la quantité
            $total += $product->getPriceTTC()*$quantity;

            //nouveau "panier" pour chaque produit dans lequel est stocké le produit, sa quantité, son montant total et une référence commune
            $cart = new Cart();
            $cart->setProduct($product)
                ->setQuantity($quantity)
                ->setAmount($product, $quantity)
                ->setUser($user)
                ->setReference($referenceForOrder);
            
            
            $carts[] = $cart;
            $manager->persist($cart);
            
        }
    
        $manager->flush();

        return $this->redirectToRoute('order_prepare', ['reference'=>$referenceForOrder]);
    }

    //Suppression d'un produit dans le panier dans la page récapitulative
    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(Product $product, SessionInterface $session){

        $id = $product->getId();

        //récupération du panier actuel
        $cart = $session->get("cart", []);  
      
        //si le panier n'est pas vide
        if(!empty($cart[$id])){
            //il reste + d'un produit du même id dans le panier ?
            if($cart[$id] > 1){
                //on reduit de 1 la valeur
                 $cart[$id]--;
            } else {
                //si on tombe a 0 on supprime la ligne du panier 
                unset($cart[$id]);
            }
           
        }

      //Sauvegarde dans la session
      $session->set('cart', $cart);

    
      return $this->redirectToRoute('cart');
      
    }
    //Suppression de la ligne d'un produit 
    #[Route('/cart/delete/{id}', name: 'cart_delete')]
    public function delete(Product $product, SessionInterface $session){

        $id = $product->getId();

        $cart = $session->get('cart', []);  
      
        //si le panier n'est pas vide (concernant le produit voulu )
        if(!empty($cart[$id])){
        
            //on supprime la ligne
            unset($cart[$id]);        
        }

      //Sauvegarde dans la session
      $session->set('cart', $cart);

    
      return $this->redirectToRoute('cart');     
    }

    //Suppression du panier
    #[Route('/cart/delete', name: 'cart_delete_all')]
    public function deleteAll(SessionInterface $session){
  
        $session->remove('cart');  
     
        return $this->redirectToRoute('cart');     
    }


}
