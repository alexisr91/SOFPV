<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
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
            //on trouve le produit qui correspond à l'id 
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
    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(Product $product, SessionInterface $session){

        $id = $product->getId();

        //récupération du panier actuel
        //soit on récupère les données de session du panier, soit la valeur par défaut sera un array vide
        $cart = $session->get('cart', [$id]);

        //si le panier ne contient pas encore le produit on lui donne la valeur 1
        if(empty($cart[$id])){
            $cart[$id] = 1;
        //sinon on incrémente la valeur de 1    
        } else {
            $cart[$id]++;
        }

      //Sauvegarde dans la session
      $session->set('cart', $cart);

      //dd($cart);
      return $this->redirectToRoute('cart');
      

    }
}
