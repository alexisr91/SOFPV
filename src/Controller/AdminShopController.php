<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Services\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminShopController extends AbstractController
{
    // product gestion
    // gestion des produits
    #[Route('/admin/shop', name: 'admin_shop')]
    public function index(Request $request, EntityManagerInterface $manager, Media $mediaService, ProductRepository $productRepository): Response
    {
        // new instance for product class
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        // check form validation
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            // keep beaklines before sending on database
            // on prend en compte les sauts de ligne avec nl2br avant de mettre le contenu en bdd
            $description = nl2br($product->getDescription());
            $product->setDescription($description);

            // image upload
            // upload de la nouvelle image
            if ($image) {
                
                $productPath = $this->getParameter('upload_product');

                $productImgName = $mediaService->saveImageAndGetName($image, $productPath);
                $product->setImage($productImgName);
            }

            $manager->persist($product);
            $manager->flush();

            $this->addFlash('success', 'Le produit a bien été ajouté.');

            return $this->redirectToRoute('admin_shop');
        }

        // product lists, lower stock first
        // liste des produits, ceux qui ont le stock le plus bas en premier
        $products = $productRepository->findBy([], ['stock' => 'ASC']);

        return $this->render('admin/shop/index.html.twig', [
            'title' => 'Gestion des produits',
            'products' => $products,
            'form' => $form->createView(),
        ]);
    }

    // modification d'un produit
    #[Route('admin/shop/modify/{id}', name: 'admin_shop_modify')]
    public function modify(EntityManagerInterface $manager, Media $mediaService , Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            // on prend en compte les sauts de ligne avec nl2br avant de mettre le contenu en bdd
            $description = nl2br($product->getDescription());
            $product->setDescription($description);

            // upload de la nouvelle image
            if ($image) {
               
                //use media service to save and get img name
                $productPath = $this->getParameter('upload_product');
                $imgName = $mediaService->saveImageAndGetName($image, $productPath);

                $product->setImage($imgName);
            }

            $manager->persist($product);
            $manager->flush();

            $this->addFlash('success', 'Le produit a bien été modifié.');

            return $this->redirectToRoute('admin_shop');
        }

        return $this->render('admin/shop/modify.html.twig', [
            'title' => 'Modification du produit',
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    // désactivation d'un produit
    #[Route('admin/shop/delete/{id}', name: 'admin_shop_delete')]
    public function delete(EntityManagerInterface $manager, ProductRepository $productRepository, int $id, Request $request): Response
    {
        $token = $request->request->get('token');

        // vérification du token
        if ($this->isCsrfTokenValid('delete', $token)) {
            // on vérifie que le produit existe et a été trouvé dans la bdd
            $product = $productRepository->findOneBy(['id' => $id]);
            if ($product) {
                $product->setActive(false)
                        ->setStock(0);

                $manager->flush();
                $this->addFlash('success', 'Le produit a bien été désactivé. Il n\'est désormais plus visible dans la boutique.');
            } else {
                $this->addFlash('danger', 'Le produit n\'existe pas.');
            }

            return $this->redirectToRoute('admin_shop');
        } else {
            throw new BadRequestHttpException();
        }
    }

    // réactivation d'un produit
    #[Route('admin/shop/reactivate/{id}', name: 'admin_shop_reactivate')]
    public function reactivate(EntityManagerInterface $manager, ProductRepository $productRepository,int $id, Request $request): Response
    {
        $token = $request->request->get('token');

        // vérification du token
        if ($this->isCsrfTokenValid('reactivate', $token)) {
            // on vérifie que le produit existe et a été trouvé dans la bdd
            $product = $productRepository->findOneBy(['id' => $id]);
            if ($product) {
                $product->setActive(true);
                $manager->flush();
                $this->addFlash('success', 'Le produit a bien été activé. Il est désormais visible dans la boutique.');
            } else {
                $this->addFlash('danger', 'Le produit n\'existe pas.');
            }

            return $this->redirectToRoute('admin_shop');
        } else {
            throw new BadRequestHttpException();
        }
    }

    // réactivation d'un produit
    #[Route('admin/shop/reactivate/{id}', name: 'admin_shop_reactivate')]
    public function reactivate(EntityManagerInterface $manager, ProductRepository $productRepository, $id, Request $request)
    {
        $token = $request->request->get('token');

        // vérification du token
        if ($this->isCsrfTokenValid('reactivate', $token)) {
            // on vérifie que le produit existe et a été trouvé dans la bdd
            $product = $productRepository->findOneBy(['id' => $id]);
            if ($product) {
                $product->setActive(true);
                $manager->flush();
                $this->addFlash('success', 'Le produit a bien été activé. Il est désormais visible dans la boutique.');
            } else {
                $this->addFlash('danger', 'Le produit n\'existe pas.');
            }

            return $this->redirectToRoute('admin_shop');
        } else {
            throw new BadRequestHttpException();
        }
    }
}
