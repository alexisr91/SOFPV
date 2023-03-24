<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Pagination;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    //Visualisation du produit
    #[Route('/shop/product/{slug}', name:'shop_show_product')]
    public function showProduct(Product $product)
    {
        return $this->render('shop/showProduct.html.twig', [
            'title' => 'Voir le produit',
            'product'=>$product
            
        ]);
    }
        
    
}
