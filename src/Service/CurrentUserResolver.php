<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CurrentUserResolver
{
    public function __construct(
        private readonly TokenService $tokenService,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function resolve(Request $request): User
    {
        $header = $request->headers->get('Authorization', '');
        if (!str_starts_with($header, 'Bearer ')) {
            throw new AccessDeniedHttpException('Требуется токен аутентификации');
        }

        $payload = $this->tokenService->parse(substr($header, 7));
        if (!$payload || empty($payload['sub'])) {
            throw new AccessDeniedHttpException('Некорректный токен');
        }

        $user = $this->userRepository->findOneBy(['email' => $payload['sub']]);
        if (!$user) {
            throw new AccessDeniedHttpException('Пользователь не найден');
        }

        return $user;
    }

    public function denyUnlessAdmin(Request $request): User
    {
        $user = $this->resolve($request);
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            throw new AccessDeniedHttpException('Недостаточно прав');
        }

        return $user;
    }
}
