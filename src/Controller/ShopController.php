<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductQuantityType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    // shop main page , list of products
    // page principale de la boutique
    #[Route('/shop', name: 'shop')]
    public function index(ProductRepository $productRepo): Response
    {
        $products = $productRepo->findBy(['active' => true]);

        return $this->render('shop/index.html.twig', [
            'title' => 'Boutique',
            'products' => $products,
        ]);
    }

    // show a product + add a quantity of the product on cart
    // Visualisation du produit + ajout d'un nombre de produit voulu dans le panier
    #[Route('/shop/product/{slug}', name: 'shop_show_product')]
    public function showProduct(Product $product, SessionInterface $session, Request $request) : Response
    {
        // On verifie que le produit est bien actif - is the product is active / online
        if ($product->isActive()) {
            // données de session concernant le stock
            // get session data for temporary stock (database stock less session stock on cart )
            $tempStock = $session->get('tempStock', []);
            $productId = $product->getId();

            $form = $this->createForm(ProductQuantityType::class);

            $form->handleRequest($request);

            $stock = $product->getStock();

            // pour avoir un rendu direct du maximum de quantité autorisé dans l'input - for having direct view of max quantity allowed on input
            // si il y a une donnée dans le stock temporaire correspondant au produit, on se fie au stock temporaire
            // if there is data on temporary stock with product id, the stock will be limited by it
            if (isset($tempStock[$productId])) {
                $stock = $tempStock[$productId];
            }
            // si il y a une donnée de tempStock et qu'elle est a 0 (stock bdd - stock temporaire = 0) on met le stock à 0
            // if there is temporary stock data and it is at 0, we set variable stock at 0. It means database stock less temporary stock equal to 0
            elseif (isset($tempStock[$productId]) && 0 === $tempStock[$productId]) {
                $stock = 0;
            } else {
                // sinon on se base sur le stock de la bdd
                // if there's no temporary data, we get product stock on database
                $stock = $product->getStock();
            }

            if ($form->isSubmitted() && $form->isValid()) {
                // on récupère la quantité souhaitée par l'utilisateur - get quantity wanted by user
                $quantity = (int) $form->get('quantity')->getData();

                // récupération du panier actuel - get current cart
                // On récupère les données de session du panier, la valeur par défaut sera un tableau vide
                // get session data for cart, by default it's an empty array
                $cart = $session->get('cart', []);

                if (empty($cart[$productId])) {
                    // si le panier est vide on met la quantité récupérée par le form
                    // if cart is empty, we get quantity got by the form
                    $cart[$productId] = $quantity;
                } else {
                    // si il est déjà présent on l'ajoute à la quantité dejà présente
                    // if there is already the product, we add that quantity
                    $cart[$productId] += $quantity;
                }

                // si il n'y a pas encore de produits ajoutés au panier, on créé la data "produit" => "stock restant" en se basant sur le stock réel du produit en bdd
                // if there are no products in cart, we create stock left "produit"=>"stock restant" by product stock data from database
                if (empty($tempStock[$productId])) {
                    $tempStock[$productId] = $product->getStock() - $quantity;
                } else {
                    // sinon on déduit la quantité ajoutée
                    // else we remove quantity added
                    $tempStock[$productId] -= $quantity;
                }

                // Sauvegarde dans la session
                // save session data
                $session->set('cart', $cart);
                $session->set('tempStock', $tempStock);

                // on redirige vers le shop
                return $this->redirectToRoute('cart');
            }
        } else {
            throw new BadRequestHttpException();
        }

        return $this->render('shop/showProduct.html.twig', [
            'title' => 'Voir le produit',
            'product' => $product,
            'form' => $form->createView(),
            'stock' => $stock,
        ]);
    }
}
