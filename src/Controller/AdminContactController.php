<?php

namespace App\Controller;

use App\Entity\AdminResponseContact;
use App\Entity\Contact;
use App\Form\ResponseContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminContactController extends AbstractController
{
    // mails list received from contact form
    // liste de mails reçu via le formulaire de contact
    #[Route('/admin/contact', name: 'admin_contact')]
    public function index(ContactRepository $contactRepository, AdminResponseContactRepository $responsesRepository): Response
    {
        // select messages (closed/last archived on end) by sending date
        // tri des messages (les clôturés / archivés en dernier) et par date d'envoi
        $messages = $contactRepository->findBy([], ['closed' => 'ASC', 'createdAt' => 'DESC']);

        return $this->render('admin/contact/index.html.twig', [
            'title' => 'Messagerie',
            'messages' => $messages,
        ]);
    }

    // mails closing / archiving  (visually notified for admin)
    // clôture / archivage du mail (distinction pour les mails auxquels l'admin a dejà répondu)
    #[Route('/admin/contact/close/{id}', name: 'admin_contact_close')]
    public function close(EntityManagerInterface $manager, Contact $contact, ContactRepository $contactRepository, int $id, Request $request): Response
    {
        $token = $request->request->get('token');

        // vérification du token
        if ($this->isCsrfTokenValid('close', $token)) {
            // check if Symfony get the good ID
            $checkContact = $contactRepository->findOneBy(['id' => $id]);

            if ($checkContact) {
                $contact->setClosed(true);
                $manager->persist($contact);
                $manager->flush();

                // toast + redirection
                $this->addFlash('success', 'Le message a été clôturé avec succès.');

                return $this->redirectToRoute('admin_contact');
            } else {
                $this->addFlash('danger', 'Le message est introuvable.');

                return $this->redirectToRoute('admin_contact');
            }
        } else {
            throw new BadRequestHttpException();
        }
    }

    // send a response to a contact mail
    // Envoi d'une réponse à un mail par l'admin
    #[Route('/admin/contact/response/{id}', name: 'admin_contact_response')]
    public function response(Request $request, EntityManagerInterface $manager, ContactRepository $contactRepository, MailerInterface $mailer, int $id) : Response
    {
        $contact = $contactRepository->findOneBy(['id' => $id]);

        if ($contact) {
            // create a new instance of adminResponseContact
            $contactResponse = new AdminResponseContact();

            $form = $this->createForm(ResponseContactType::class, $contactResponse);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // keep breaklines
                $contactResponse->setMessage(nl2br($form->get('message')->getData()))
                                ->setContact($contact);

                $manager->persist($contactResponse);
                $manager->flush();

                // mailer preparation : we use TemplatedEmail with htmlTemplate to have a personnalised template for responses
                // préparation du mail via le mailer
                $email = (new TemplatedEmail())
                ->from('admin@sofpv.fr')
                ->to($contact->getEmail())
                ->subject($contactResponse->getSubject())

                // template twig pour structure css du mail
                ->htmlTemplate('contact/emailsResponse.html.twig')

                ->context([
                    'contact' => $contactResponse,
                ]);

                $mailer->send($email);

                $this->addFlash('success', 'Votre réponse a été envoyée avec succès.');

                return $this->redirectToRoute('admin_contact');
            }
        } else {
            $this->addFlash('danger', 'Le message est introuvable');

            return $this->redirectToRoute('admin_contact');
        }

        return $this->render('admin/contact/response.html.twig', [
            'title' => 'Répondre à un mail',
            'form' => $form->createView(),
            'contact' => $contact,
        ]);
    }

    //Envoi d'une réponse à un mail par l'admin
    #[Route('/admin/contact/response/{id}', name:'admin_contact_response')]
    public function response(Request $request, EntityManagerInterface $manager, ContactRepository $contactRepository, MailerInterface $mailer, $id){

        $contact = $contactRepository->findOneBy(['id'=>$id]);
       
        if($contact){

            $contactResponse = new AdminResponseContact();

            $form = $this->createForm(ResponseContactType::class, $contactResponse);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                $contactResponse->setMessage(nl2br($form->get('message')->getData()))
                                ->setContact($contact);
                
                $manager->persist($contactResponse);
                $manager->flush();

                //préparation du mail via le mailer
                $email = (new TemplatedEmail())
                ->from('admin@sofpv.fr')
                ->to($contact->getEmail())
                ->subject($contactResponse->getSubject())
                
                //template twig pour structure css du mail
                ->htmlTemplate('contact/emailsResponse.html.twig')

                ->context([
                    'contact' => $contactResponse
                ]);

            $mailer->send($email);

                $this->addFlash('success', 'Votre réponse a été envoyée avec succès.');
                return $this->redirectToRoute('admin_contact');
            }

        } else {
            $this->addFlash('danger', 'Le message est introuvable');
            return $this->redirectToRoute('admin_contact');
        }

        return $this->render('admin/contact/response.html.twig', [
            'title' => 'Répondre à un mail',
            'form'=>$form->createView(),
            'contact'=>$contact
        ]);
    }
}
