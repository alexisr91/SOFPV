<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    // formulaire de contact public - public contact form
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, EntityManagerInterface $manager, MailerInterface $mailer): Response
    {
        $contact = new Contact();

        // si l'user est connecté, on récupère ses données pour faciliter le remplissage du formulaire
        // if the user is authentified, set full name and mail to easy filling of the form
        if ($this->getUser()) {
            /** @var User $user */
            $user = $this->getUser();
            $contact->setFullName($user->getFullName())
                    ->setEmail($user->getEmail());
        }

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();
            $message = nl2br($contact->getMessage());

            $contact->setMessage($message);

            $manager->persist($contact);
            $manager->flush();

            // envoi de l'email - send mail through mailer
            $email = (new TemplatedEmail())
            ->from($contact->getEmail())
            ->to('sofpv@lauragaupin.fr')
            ->subject($contact->getSubject())

            // template twig pour structure css du mail - template twig for css structure
            ->htmlTemplate('contact/emails.html.twig')

            ->context([
                'contact' => $contact,
            ]);

            $mailer->send($email);

            $this->addFlash('success', 'Votre demande a été envoyée avec succès.');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'title' => 'Formulaire de contact',
            'form' => $form->createView(),
        ]);
    }
}
