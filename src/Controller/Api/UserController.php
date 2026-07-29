<?php

namespace App\Controller\Api;

use App\Service\CurrentUserResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/users')]
class UserController extends AbstractController
{
    #[Route('/current', name: 'api_users_current', methods: ['GET'])]
    public function current(Request $request, CurrentUserResolver $currentUserResolver): JsonResponse
    {
        $user = $currentUserResolver->resolve($request);

        return $this->json([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'balance' => $user->getBalance(),
        ]);
    }
}
