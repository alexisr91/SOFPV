<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

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
    //Visualisation du produit
    #[Route('/shop/product/{slug}', name:'shop_show_product')]
    public function showProduct(Product $product, Request $request)
    {

        $id= $product->getId();
        $form = $this->createFormBuilder()
        ->add('quantity', IntegerType::class, [
            'label'=> false, 
            'data'=> 1
        ])
        ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            
            //on récupère la quantité souhaitée par l'utilisateur
            $quantity = $form->get('quantity')->getData();
            //on la passe en paramètre pour mettre à jour le panier
            return $this->redirectToRoute('cart_add', ['id'=>$id,'quantity'=>$quantity]);
        }

        return $this->render('shop/showProduct.html.twig', [
            'title' => 'Voir le produit',
            'product'=>$product,
            'form'=>$form->createView()
        ]);
    }
        
    
}
