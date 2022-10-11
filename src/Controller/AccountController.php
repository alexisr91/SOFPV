<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AccountController extends AbstractController
{

    //Connexion 
    #[Route('/login', name: 'account_login')]
    public function login(AuthenticationUtils $authenticationUtils):Response
    {
        //on récupère l'erreur si il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        //on récupère le dernier identifiant de connexion entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('account/login.html.twig', [
            'title' => 'Connexion', 'last_username'=>$lastUsername, 'error'=> $error
        ]);
    }

    //Déconnexion
    #[Route('/logout', name:'account_logout')]
    public function logout()
    {
        
    }

    //Inscription de l'utilisateur
    #[Route('/register', name: 'account_register')]
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        //Si le formulaire est soumis réellement, et si il est valide
        if($form->isSubmitted() && $form->isValid()){

            //Hash du mot de passe avant l'envoi dans la base de données
            $hash = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();
            
            $this->addFlash('success', 'Votre compte a bien été créé !');
            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/register.html.twig', [
            'title' => 'Inscription',
            'form'=>$form->createView()
        ]);
    }

    //Profil personnel de l'utilisateur (modifications paramètres user, vue globale de son profil)
    #[Route('/profile', name:'account_myprofile')]
    public function myProfile()
    {
        $user = $this->getUser();

        return $this->render('account/myprofile.html.twig', [
            'title' => 'Mon compte ',
            'user' => $user
        ]);
    }

    //Edition des données personnelles du profil
    #[Route('/profile/edit', name:'account_edit')]
    public function edit(EntityManagerInterface $manager, Request $request, SluggerInterface $slugger){

        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            //ajout d'avatar par l'user
            $avatar = $form->get('avatar')->getData();
            //on récupère le nom du fichier
            if($avatar) {
                $originalName = pathinfo($avatar->getClientOriginalName(), PATHINFO_FILENAME);
                //on utilise un slug pour éviter les problèmes avec les noms fournis par les users
                $sluggedName = $slugger->slug($originalName);
                //on ajoute un uniqId pour chaque upload pour éviter les problèmes de doublons + on récupère l'extension du fichier
                $newName = $sluggedName.'-'.uniqid().'.'.$avatar->guessExtension();

                //on s'assure que le deplacement dans le dossier uploads est correctement effectué
                try {
                    $avatar->move($this->getParameter('upload_avatar'), $newName);
                } catch(FileException $e) {
                    dd($e->getMessage());
                    
                }

                $user->setAvatar($newName);

            }

                //ajout d'une bannière par l'user
                $banner = $form->get('banner')->getData();
                //on récupère le nom du fichier
                if($banner) {

                    $originalName = pathinfo($banner->getClientOriginalName(), PATHINFO_FILENAME);
                    $sluggedName = $slugger->slug($originalName);
                    $newName = $sluggedName.'-'.uniqid().'.'.$banner->guessExtension();
    
                    try {
                        $banner->move($this->getParameter('upload_banner'), $newName);
                    } catch(FileException $e) {
                        dd($e->getMessage());
                    }
    
                    $user->setBanner($newName);
    
                }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Votre profil a bien été mis à jour !');
            return $this->redirectToRoute('account_myprofile');
        }

        return $this->render('account/edit.html.twig', [
            'title' => 'Modifier le profil ',
            'user' => $user,
            'form'=>$form->createView()
        ]);
    }

    //Profil utilisateur public (vidéos de l'user, badge helper, drone favori et sa configuration)
    #[Route('/profile/{nickname}', name:'account_profile')]
    public function profile()
    {

    }

}
