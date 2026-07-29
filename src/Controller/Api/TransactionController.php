<?php

namespace App\Controller\Api;

use App\Repository\TransactionRepository;
use App\Service\CurrentUserResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/transactions')]
class TransactionController extends AbstractController
{
    #[Route('', name: 'api_transactions_index', methods: ['GET'])]
    public function index(
        Request $request,
        CurrentUserResolver $currentUserResolver,
        TransactionRepository $transactionRepository,
    ): JsonResponse {
        $user = $currentUserResolver->resolve($request);
        $filters = $request->query->all('filter');

        $items = array_map(static function ($transaction): array {
            return [
                'id' => $transaction->getId(),
                'created_at' => $transaction->getCreatedAt()->format(DATE_ATOM),
                'type' => $transaction->getType(),
                'course_code' => $transaction->getCourse()?->getCode(),
                'amount' => $transaction->getAmount(),
                'expires_at' => $transaction->getExpiresAt()?->format(DATE_ATOM),
            ];
        }, $transactionRepository->findForUser($user, $filters));

        return $this->json($items);
    }
}
