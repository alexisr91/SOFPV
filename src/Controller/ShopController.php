<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductQuantityType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    public function showProduct(Product $product, SessionInterface $session, Request $request , ProductRepository $productRepo)
    {
        //données de session concernant le stock
        $tempStock = $session->get("tempStock", []); 
       
        $form = $this->createForm(ProductQuantityType::class);

        $form->handleRequest($request); 
        
        $stock = $product->getStock();

        // pour avoir un rendu direct du maximum autorisé dans l'input
        foreach($tempStock as $id => $stockLeft){
            $stockData = [
                'product' => $id,
                'stockLeft' => $stockLeft
            ];

            //on vérifie pour chaque key => value dans les données des stock si le produit est celui qui correspond à la page
            $findProduct = $productRepo->find($id);
            //si il y a une correlation entre le produit et les données de sessions, on limite l'achat au stock restant
            if($findProduct === $product->getId()){
                $stock = $stockLeft;
            //si il n'y a pas de correlation on se fie au stock de la bdd    
            } 
        }
        
        if($form->isSubmitted() && $form->isValid()){
             //on récupère la quantité souhaitée par l'utilisateur
            $quantity = (int)$form->get('quantity')->getData();  
            
            $productId = $product->getId();

            //récupération du panier actuel
            //On récupère les données de session du panier, la valeur par défaut sera un array vide
            $cart = $session->get("cart", []); 

            if(empty($cart[$productId])){
                 //si le panier est vide on met la quantité récupérée par le form
                $cart[$productId] = $quantity;  
            } else { //si il est déjà présent on l'incrémente à la quantité dejà présente   
                $cart[$productId]+= $quantity ;
          
            }   

            //si il n'y a pas encore de produits ajoutés au panier, on créé la data "produit" => "stock restant" en se basant sur le stock réel du produit en bdd
            if(empty($tempStock[$productId])){      
                $tempStock[$productId] = $product->getStock() - $quantity ;
            } else { 
                //sinon on déduit la quantité ajoutée 
                $tempStock[$productId] -=  $quantity;
            }


            //Sauvegarde dans la session
            $session->set('cart', $cart);
            $session->set('tempStock', $tempStock);

            //on redirige vers le shop 
            return $this->redirectToRoute('cart');

        } 



        return $this->render('shop/showProduct.html.twig', [
            'title' => 'Voir le produit',
            'product'=> $product,
            'form'=>$form->createView(),
            'stock'=> $stock
        ]);
    }
        
    
}
