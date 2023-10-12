<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    // cart details
    // Page de récapitulatif du panier
    #[Route('/cart', name: 'cart')]
    public function index(SessionInterface $session, ProductRepository $productRepo): Response
    {
        // on récupère les données de session concernant le panier
        // get data session about cart
        $cart = $session->get('cart', []);

        // on va stocker les données dans un tableau
        // stock data in an array
        $cartData = [];
        $total = 0;

        // for each session entry which represents a product id => quantity
        // pour chaque entrée de session concernant le cart (panier)
        foreach ($cart as $id => $quantity) {
            // on trouve le produit qui correspond à l'id (pas possible d'ajouter un produit qui n'existe pas)
            // get product id and find it to avoid adding an inexistant product
            $product = $productRepo->find($id);

            // on y associe le int qui correspond à la quantité (nombre de fois où on a ajouté au panier le même produit)
            // association of product id with quantity
            $cartData[] = [
                'product' => $product,
                'quantity' => $quantity,
            ];
            // pour le total on multiplie le prix par la quantité
            // for get total, multiplie price TTC with quantity
            $total += $product->getPriceTTC() * $quantity;
        }
    

        }

        return $this->render('shop/cart/index.html.twig', [
            'title' => 'Votre panier',
            'cardData' => $cartData,
            'total' => $total,
        ]);
    }

    // validation du panier
    // cart validation
    #[Route('/cart/validate', name: 'cart_validate')]
    #[IsGranted('ROLE_USER')]
    public function validate(SessionInterface $session, ProductRepository $productRepo, EntityManagerInterface $manager): RedirectResponse
    {
        $sessionCart = $session->get('cart');

        // si il n'y a pas de données de session de panier, redirection - if no data found for cart, redirection
        if (null == $sessionCart) {
            $this->addFlash('warning', 'Votre panier est vide, vous ne pouvez pas valider de commande.');

            return $this->redirectToRoute('cart');
        }

        // tableau vide pour stocker les données de panier - empty array to stock data
        $cartData = [];
        $total = 0;

         /** @var User $user */
        $user = $this->getUser();

        $carts = [];

        // on assigne une référence qui sera propre à chaque commande (chaque reférence correspond à des entité Cart qui sont des lots de produits)
        // creation of an unique reference for the order, that reference will be the same for all cart lines
        $referenceForOrder = uniqid('', false);

        // pour chaque entrée de session concernant le cart (panier)
        // for each entry on session cart
        foreach ($sessionCart as $id => $quantity) {
            // on trouve le produit qui correspond à l'id (pas possible d'ajouter un produit qui n'existe pas)
            // we find product to check if it exists
            $product = $productRepo->find($id);

            // on y associe le int qui correspond à la quantité (nombre de fois où on a ajouté au panier le même produit)
            // association of id product and his quantity
            $cartData[] = [
                'product' => $product,
                'quantity' => $quantity,
            ];
            // get total by multiply price by quantity
            // pour le total on multiplie le prix par la quantité
            $total += $product->getPriceTTC() * $quantity;

            // nouveau "panier" pour chaque produit dans lequel est stocké le produit, sa quantité, son montant total et une référence commune
            // we create a new instance of Cart for each product : we set the product, his quantity, his total amount, the user and the common reference used to create the order instance
            $cart = new Cart();
            $cart->setProduct($product)
                ->setQuantity($quantity)
                ->setAmount($product, $quantity)
                ->setUser($user)
                ->setReference($referenceForOrder);

            // persistence of each cart
            $carts[] = $cart;
            $manager->persist($cart);
        }

        $manager->flush();

        return $this->redirectToRoute('order_prepare', ['reference' => $referenceForOrder]);
    }

    // Ajout d'un produit dans le panier dans la page récapitulative
    // add a product on cart details page
    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(Product $product, SessionInterface $session, Request $request) : Response
    {
        $token = $request->request->get('token');

        // vérification du token - check token
        if ($this->isCsrfTokenValid('add'.$product->getId(), $token)) {
            // si le produit est activé par l'admin - check if product is activated by admin
            if ($product->isActive()) {
                $productId = $product->getId();

                // récupération du panier actuel
                // On récupère les données de session du panier, la valeur par défaut sera un array vide - get session data for the cart, empty array by default
                $cart = $session->get('cart', []);

                // données de session du stock - get temporary stock data
                $tempStock = $session->get('tempStock', []);

                $stock = $product->getStock();

                // for each temporary stock data (product id => stock left)
                foreach ($tempStock as $id => $stockLeft) {
                    $stockData = [
                        'product' => $id,
                        'stockLeft' => $stockLeft,
                    ];
                }
                // dd($tempStock[$productId]);

                // si le panier est vide on met la quantité à 1 (impossible par l'interface => route accessible sur le récap du panier avec les produits déja présents uniquement.)
                // if cart is empty, quantity set to 1 (in case of : the path isn't planned to be accessible, only in cart page which contains product )
                if (empty($cart[$productId]) && $product->getStock() >= 1 && $tempStock[$productId] >= 1) {
                    $cart[$productId] = 1;

                    // sinon on incrémente la quantité de 1
                    // else we add 1 to quantity
                } elseif (!empty($cart[$productId]) && $product->getStock() >= 1 && $tempStock[$productId] >= 1) {
                    ++$cart[$productId];
                }

                // vérification de la possibilité d'ajouter un produit ou pas selon le stock général et le stock conservé par la session
                // check the possibility to add a product or not, by database stock or temporary stock

                // si le produit n'est pas encore dans la session et que le stock du produit est supérieur à 1
                // if the product is not already in session data and product stock is upper than 1
                if (empty($tempStock[$productId]) && $tempStock[$productId] >= 1) {
                    $tempStock[$productId] = $stock - 1;

                    // si le produit est déja présent dans la session (dans l'utilisation théorique, il l'est) et que le stock est égal ou supérieur à 1
                    // if the product is already in temporary session and the stock is equal to 1 or upper
                } elseif (!empty($tempStock[$productId]) && $tempStock[$productId] >= 1) {
                    // on déduit 1 produit au stock restant
                    // remove 1 to left stock
                    --$tempStock[$productId];
                } elseif (0 == $tempStock[$productId] || 0 == $stock) {
                    // si le stock atteint 0 , on ne permet pas de rajouter de produit
                    // if stock reaches 0, we don't allow to add product
                    $this->addFlash('warning', 'Vous avez atteint la limite de stock pour ce produit.');

                    return $this->redirectToRoute('cart');
                }

                // Sauvegarde dans la session - save on session datas
                $session->set('cart', $cart);
                $session->set('tempStock', $tempStock);
                $this->addFlash('success', 'Produit ajouté à votre panier.');

                return $this->redirectToRoute('cart');
            } else {
                $this->addFlash('danger', 'Ce produit n\'est pas disponible.');

                return $this->redirectToRoute('cart');
            }
        } else {
            throw new BadRequestHttpException();
        }
    }

    // remove on product in cart line on cart details page
    // Suppression d'un produit dans le panier dans la page récapitulative
    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(Product $product, SessionInterface $session, ProductRepository $productRepository, int $id, Request $request) : Response
    {
        $token = $request->request->get('token');

        // vérification du token - token check
        if ($this->isCsrfTokenValid('remove'.$product->getId(), $token)) {
            $checkProduct = $productRepository->findOneBy(['id' => $id]);

            if ($checkProduct) {
                // récupération du panier actuel - get session data for cart
                $cart = $session->get('cart', []);

                $tempStock = $session->get('tempStock', []);

                // si le panier n'est pas vide - if cart is not empty
                if (!empty($cart[$id])) {
                    // il reste + d'un produit du même id dans le panier ?
                    // is there any product left with same id in the cart ?
                    if ($cart[$id] > 1) {
                        // on reduit de 1 la valeur
                        // remove 1 to quantity of product
                        --$cart[$id];
                        ++$tempStock[$id];
                    } else {
                        // si on tombe a 0 on supprime la ligne du panier
                        // if we reach 0 , we delete the cart line
                        unset($cart[$id]);
                        unset($tempStock[$id]);
                    }
                }

                // Sauvegarde dans la session - save on session
                $session->set('cart', $cart);
                $session->set('tempStock', $tempStock);

                return $this->redirectToRoute('cart');
            } else {
                $this->addFlash('danger', 'Une erreur est survenue');

                return $this->redirectToRoute('cart');
            }
        } else {
            throw new BadRequestHttpException();
        }
    }

    // Delete cart line
    // Suppression de la ligne d'un produit
    #[Route('/cart/delete/{id}', name: 'cart_delete')]
    public function delete(Product $product, SessionInterface $session, Request $request) : Response
    {
        $token = $request->request->get('token');

        // vérification du token - token check
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $token)) {
            $id = $product->getId();

            // récupération des données de session - get session datas
            $cart = $session->get('cart', []);
            $tempStock = $session->get('tempStock', []);

            // si le panier n'est pas vide (concernant le produit voulu )
            // if cart have an entry for selected product
            if (!empty($cart[$id])) {
                // on supprime la ligne
                // delete the cart line
                unset($cart[$id]);
                unset($tempStock[$id]);
            }

            // Sauvegarde dans la session - save on session
            $session->set('cart', $cart);
            $session->set('tempStock', $tempStock);

            return $this->redirectToRoute('cart');
        } else {
            throw new BadRequestHttpException();
        }
    }

    // delete all the carts line
    // Suppression du panier
    #[Route('/cart/delete', name: 'cart_delete_all')]
    public function deleteAll(SessionInterface $session, Request $request): Response
    {
        $token = $request->request->get('token');

        // vérification du token - check token
        if ($this->isCsrfTokenValid('deleteAll', $token)) {
            // remove session datas for cart or temporary stock
            $session->remove('cart');
            $session->remove('tempStock');

            return $this->redirectToRoute('cart');
        } else {
            throw new BadRequestHttpException();
        }
    }
}
