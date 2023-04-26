<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CartController extends AbstractController
{

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

    //Ajout d'un produit dans le panier dans la page récapitulative
    #[Route('/cart/add/{id}', name: 'cart_add' )]
    public function add(Product $product, SessionInterface $session){

        $id = $product->getId();
        
       
        //récupération du panier actuel
        //On récupère les données de session du panier, la valeur par défaut sera un array vide
        $cart = $session->get("cart", []); 

        //données de session du stock
        $tempStock = $session->get("tempStock", []);

        $stock = $product->getStock();

        foreach($tempStock as $id => $stockLeft){
            $stockData = [
                'product' => $id,
                'stockLeft' => $stockLeft
            ];

        }
        
        //vérification de la possibilité d'ajouter un produit ou pas selon le stock général et le stock conservé par la session
            //si le produit n'est pas encore dans la session et que le stock du produit est supérieur à 1
            if(empty($tempStock[$id]) && $tempStock[$id] >= 1){  

                $tempStock[$id] = $stock - 1;

            //si le produit est déja présent dans la session (dans l'utilisation théorique, il l'est) et que le stock est égal ou supérieur à 1
            } elseif (!empty($tempStock[$id]  && $tempStock[$id] >= 1 )) {
                
                //sinon on déduit 1 produit au stock
                $tempStock[$id] -=  1;

            } else if ($tempStock[$id] == 0 || $stock == 0) {

                //si le stock atteint 0 , on ne permet pas de rajouter de produit
                $this->addFlash('warning','Vous avez atteint la limite de stock pour ce produit.');
                // return $this->redirectToRoute('cart');
            }

        //si le panier est vide on met la quantité à 1 (impossible par l'interface => route accessible sur le récap du panier avec les produits déja présents uniquement. Accès par l'url => ajout de condition)
        if(empty($cart[$id]) && $product->getStock() >= 1 && $tempStock[$id] >= 1){
            $cart[$id] = 1;     
        } else if (!empty($cart[$id]) && $product->getStock() >= 1 && $tempStock[$id] >= 1) {
            $cart[$id] ++;
        } 

    
      //Sauvegarde dans la session
      $session->set('cart', $cart);
      $session->set('tempStock', $tempStock);

    
      return $this->redirectToRoute('cart');
      
    }

    //Suppression d'un produit dans le panier dans la page récapitulative
    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(Product $product, SessionInterface $session){

        $id = $product->getId();

        //récupération du panier actuel
        $cart = $session->get("cart", []);  

        $tempStock = $session->get("tempStock", []);

      
        //si le panier n'est pas vide
        if(!empty($cart[$id])){
            //il reste + d'un produit du même id dans le panier ?
            if($cart[$id] > 1){
                //on reduit de 1 la valeur
                 $cart[$id]--;
                 $tempStock[$id] ++;
            } else {
                //si on tombe a 0 on supprime la ligne du panier 
                unset($cart[$id]);
                unset($tempStock[$id]);
            }
           
        }

      //Sauvegarde dans la session
      $session->set('cart', $cart);
      $session->set('tempStock', $tempStock);

    
      return $this->redirectToRoute('cart');
      
    }
    //Suppression de la ligne d'un produit 
    #[Route('/cart/delete/{id}', name: 'cart_delete')]
    public function delete(Product $product, SessionInterface $session){

        $id = $product->getId();

        $cart = $session->get('cart', []);  
        $tempStock = $session->get('tempStock', []);
      
        //si le panier n'est pas vide (concernant le produit voulu )
        if(!empty($cart[$id])){
            //on supprime la ligne
            unset($cart[$id]);
            unset($tempStock[$id]);
        }
        
      //Sauvegarde dans la session
      $session->set('cart', $cart);
      $session->set('tempStock', $tempStock);

    
      return $this->redirectToRoute('cart');     
    }

    //Suppression du panier
    #[Route('/cart/delete', name: 'cart_delete_all')]
    public function deleteAll(SessionInterface $session){
  
        $session->remove('cart'); 
        $session->remove('tempStock');
     
        return $this->redirectToRoute('cart');     
    }


}
