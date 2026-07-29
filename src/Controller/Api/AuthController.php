<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PaymentService;
use App\Service\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
class AuthController extends AbstractController
{
    public function __construct(private readonly string $billingInitialDeposit)
    {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $entityManager,
        PaymentService $paymentService,
        TokenService $tokenService,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?: $request->request->all();

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['code' => 400, 'message' => 'Email и пароль обязательны'], 400);
        }

        $user = (new User())->setEmail($data['email']);
        $user->setPassword($hasher->hashPassword($user, $data['password']));

        $entityManager->persist($user);
        $entityManager->flush();

        $paymentService->deposit($user, $this->billingInitialDeposit);

        return $this->json([
            'token' => $tokenService->create($user),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'balance' => $user->getBalance(),
        ], 201);
    }

    #[Route('/auth', name: 'api_auth', methods: ['POST'])]
    public function auth(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher,
        TokenService $tokenService,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?: $request->request->all();
        $user = $userRepository->findOneBy(['email' => mb_strtolower($data['email'] ?? '')]);

        if (!$user || !$hasher->isPasswordValid($user, (string) ($data['password'] ?? ''))) {
            return $this->json(['code' => 401, 'message' => 'Неверные учетные данные'], 401);
        }

        return $this->json([
            'token' => $tokenService->create($user),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'balance' => $user->getBalance(),
        ]);
    }
}
