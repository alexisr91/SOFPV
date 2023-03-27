<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    //Page de récapitulatif du panier
    #[Route('/cart', name: 'cart')]
    public function index(ProductRepository $productRepo, SessionInterface $session): Response
    {
        //on récupère les données de session
        $cart = $session->get('cart', []);

        //on récupère les données des produits
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

    //Ajout d'un produit dans le panier
    #[Route('/cart/add/{id}/{quantity}', name: 'cart_add' )]
    public function add(Product $product, SessionInterface $session, Request $request ){

        $id = $product->getId();
        //on récupère la valeur de quantité recupérée dans l'url
        $quantity = (int)$request->attributes->get('quantity');

        //récupération du panier actuel
        //On récupère les données de session du panier, la valeur par défaut sera un array vide
        $cart = $session->get("cart", []); 
        

        //si le panier est vide on met la quantité récupérée
        if(empty($cart[$id])){
            $cart[$id] = $quantity;     
        //si il est déjà présent on l'incrémente à la quantité dejà présente   
        } else {
           $cart[$id]+= $quantity ;
        }

    
      //Sauvegarde dans la session
      $session->set('cart', $cart);

    
      return $this->redirectToRoute('cart');
      
    }
    //Suppression d'un produit dans le panier
    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(Product $product, SessionInterface $session){

        $id = $product->getId();

        //récupération du panier actuel
        $cart = $session->get("cart", []);  
      
        //si le panier n'est pas vide
        if(!empty($cart[$id])){
            //il reste plus d'un produit du même id dans le panier ?
            if($cart[$id] > 1){
                //on reduit de 1 le nombre
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
