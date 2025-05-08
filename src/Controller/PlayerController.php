<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\RegistrationForm;
use App\Security\LoginFormAuthenticator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class PlayerController extends AbstractController
{
    #[Route('/api/players', name: 'get_players', methods: ['GET'])]
    public function getPlayers(UserRepository $userRepository): JsonResponse
    {
        $players = $userRepository->findAll();

        $data = array_map(function (User $user) {
            return [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'username' => $user->getUsername(),
                'email' => $user->getEmailAddress(),
                'status' => $user->getStatus(),
            ];
        }, $players);

        return $this->json($data);
    }

    public function __construct(private EmailVerifier $emailVerifier)
    {
    }


    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('teyaranbarine@gmail.com', 'Registration Bot'))
                    ->to((string) $user->getEmailAddress())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/index.html.twig')
            );

            // do anything else you need here, like send an email

            return $security->login($user, LoginFormAuthenticator::class, 'main');
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    #[Route('/api/players/{id}', name: 'get_player', methods: ['GET'])]
    public function getPlayer(User $user): JsonResponse
    {
        $data = [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'email' => $user->getEmailAddress(),
            'status' => $user->getStatus(),
        ];

        return $this->json($data);
    }

    #[Route('/api/players/{id}', name: 'update_player', methods: ['PUT'])]
    public function updatePlayer(User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user->setFirstName($data['firstName'] ?? $user->getFirstName());
        $user->setLastName($data['lastName'] ?? $user->getLastName());
        $user->setUsername($data['username'] ?? $user->getUsername());
        $user->setEmailAddress($data['emailAddress'] ?? $user->getEmailAddress());
        $user->setStatus($data['status'] ?? $user->getStatus());

        $em->flush();

        return $this->json(['message' => 'User updated']);
    }

    #[Route('/api/players/{id}', name: 'delete_player', methods: ['DELETE'])]
    public function deletePlayer(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'User deleted']);
    }
}
