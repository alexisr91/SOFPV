<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductQuantityType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ShopController extends AbstractController
{
    //page principale de la boutique
    #[Route('/shop', name: 'shop')]
    public function index(ProductRepository $productRepo): Response
    {
        $products = $productRepo->findAll();

        return $this->render('shop/index.html.twig', [
            'title' => 'Boutique',
            'products'=>$products
            
        ]);

      
    }
    //Visualisation du produit + ajout d'un nombre de produit voulu dans le panier
    #[Route('/shop/product/{slug}', name:'shop_show_product')]
    public function showProduct(Product $product, SessionInterface $session, Request $request)
    {

        $id= $product->getId();

        $form = $this->createForm(ProductQuantityType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
             //on récupère la quantité souhaitée par l'utilisateur
            $quantity = (int)$form->get('quantity')->getData();

            //récupération du panier actuel
            //On récupère les données de session du panier, la valeur par défaut sera un array vide
            $cart = $session->get("cart", []); 
        
            //si le panier est vide on met la quantité récupérée par le form
            if(empty($cart[$id])){
                $cart[$id] = $quantity;     
                //si il est déjà présent on l'incrémente à la quantité dejà présente   
            } else {
                $cart[$id]+= $quantity ;
            }

    
            //Sauvegarde dans la session
            $session->set('cart', $cart);
            //on redirige vers le shop 
            return $this->redirectToRoute('cart');
          
        }

        return $this->render('shop/showProduct.html.twig', [
            'title' => 'Voir le produit',
            'product'=>$product,
            'form'=>$form->createView()
        ]);
    }
        
    
}
