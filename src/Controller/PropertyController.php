<?php

namespace App\Controller;


use App\Entity\Appointment;
use App\Entity\Property;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\AppointmentType;
use App\Form\PropertyType;
use App\Repository\AppointmentRepository;
use App\Repository\PropertyRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class PropertyController extends AbstractController
{
    #[Route('/property-list', name: 'app_property_index', methods: ['GET'])]
    public function index(Request $request,PropertyRepository $propertyRepository): Response
    {
        return $this->render('property/index.html.twig', [
            'properties' => $propertyRepository->findAll(),
        ]);
    }

    #[Route('/property-new', name: 'app_property_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,  SluggerInterface $slugger): Response
    {
        $property = new Property();

        // Get the currently logged-in user
        $user = $this->getUser();
        $property->setOwner($user);

        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                echo $error->getMessage();
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $files */
            $files = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($files) {
                $originalFilename = pathinfo($files->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$files->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $files->move($this->getParameter('brochures_directory'), $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $property->setImage($newFilename);
            }
            $property->setStatus('For Sale');

            $entityManager->persist($property);

            $entityManager->flush();

            return $this->redirectToRoute('app_property_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('property/new.html.twig', [
            //'property' => $property,
            'form' => $form->createView(),
        ]);

    }

    #[Route('/property', name: 'app_property_show', methods: ['GET','POST'])]
    public function show(Request $request, EntityManagerInterface $entityManager): Response
    {
        $propertyId = $request->query->get('id');
        $property = $entityManager->getRepository(Property::class)->find($propertyId);

        if (!$property) {
            throw $this->createNotFoundException('The property does not exist');
        }

        $appointment = new Appointment();
        $appointment->setProperty($property);
        $appointment->setUser($this->getUser());
        $appointment->setDateTime(new \DateTime());
        $appointment->setPrice(300.00);
        $appointment->setStatus('For Sale');

        $appointmentForm = $this->createForm(AppointmentType::class, $appointment);

        $appointmentForm->handleRequest($request);

        if ($appointmentForm->isSubmitted() && $appointmentForm->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->addFlash('success', 'Appointment booked successfully!');

            return $this->redirectToRoute('user_appointments', ['userId' => $this->getUser()->getId()]);
        }
        //user_appointment app_property_show
        // if i need to use the property id :  'id' => $property->getId(),
        return $this->render('property/show.html.twig', [
            'property' => $property,
            'appointmentForm' => $appointmentForm->createView(),
        ]);
    }


    #[Route('/property/edit', name: 'app_property_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $propertyId = $request->query->get('id');
        $property = $entityManager->getRepository(Property::class)->find($propertyId);

        if (!$property) {
            throw $this->createNotFoundException('The property does not exist');
        }

        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_property_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('property/edit.html.twig', [
            'property' => $property,
            'form' => $form,
        ]);
    }
 



    #[Route('/property/delete', name: 'app_property_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager): Response
    {
        $propertyId = $request->query->get('id');
        $property = $entityManager->getRepository(Property::class)->find($propertyId);

        if (!$property) {
            throw $this->createNotFoundException('The property does not exist');
        }

        if ($this->isCsrfTokenValid('delete'.$property->getId(), $request->request->get('_token'))) {
            $entityManager->remove($property);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_property_index', [], Response::HTTP_SEE_OTHER);
    }

    // get all properties by user

    #[Route('/properties', name: 'user_properties', methods: ['GET'])]
    public function getUserProperties(Request $request, PropertyRepository $propertyRepository): Response
    {
        $userId = $request->query->get('userId');
        if (!$userId) {
            throw $this->createNotFoundException('User ID not provided');
        }

        $properties = $propertyRepository->findByUserId($userId);

        return $this->render('property/user_properties.html.twig', [
            'properties' => $properties,
        ]);
    }

    // do transaction


    #[Route('/transaction', name: 'property_transaction', methods: ['GET', 'POST'])]
    public function handleTransaction(Request $request, PropertyRepository $propertyRepository, EntityManagerInterface $entityManager): Response
    {
        $propertyId = $request->query->get('id') ?? $request->request->get('propertyId');
        $property = $entityManager->getRepository(Property::class)->find($propertyId);
        $user = $this->getUser();

        if (!$property) {
            throw $this->createNotFoundException('The property does not exist');
        }

        if ($property->getStatus() === 'sold') {
            $this->addFlash('error', 'This property is already sold.');
            return $this->redirectToRoute('user_appointments', ['userId' => $user->getId()]);
        }

        $transaction = new Transaction();
        $transaction->setProperty($property);
        $transaction->setBuyer($user);
        $transaction->setDate(new \DateTime());
        $transaction->setPrice($property->getPrice());

        $property->setStatus('sold');

        $entityManager->persist($transaction);
        $entityManager->flush();

        $this->addFlash('success', 'Transaction successful and property status updated to sold.');

        return $this->redirectToRoute('user_transaction', ['userId' => $user->getId()]);
    }


    #[Route('/appointment', name: 'user_appointments', methods: ['GET'])]
    public function getUserProperty(Request $request, AppointmentRepository $appointmentRepository): Response
    {

        $userId = $request->query->get('userId');

        if (!$userId) {
            throw $this->createNotFoundException('User ID not provided');
        }

        $appointments = $appointmentRepository->findByUserId($userId);

        return $this->render('property/user_appointment.html.twig', [
            'appointment' => $appointments,
        ]);
    }

    #[Route('/trasactionSee', name: 'user_transaction', methods: ['GET'])]
    public function getUserTransaction(Request $request, TransactionRepository $transactionRepository): Response
    {
        $userId = $request->query->get('userId');
        if (!$userId) {
            throw $this->createNotFoundException('User ID not provided');
        }

        $transaction = $transactionRepository->findByUserId($userId);


        return $this->render('property/user_transaction.html.twig', [
            'transaction' => $transaction,
        ]);
    }

}
