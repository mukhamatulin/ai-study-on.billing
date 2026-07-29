<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Transaction;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class PaymentService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function deposit(User $user, string $amount): Transaction
    {
        return $this->entityManager->wrapInTransaction(function () use ($user, $amount): Transaction {
            $transaction = (new Transaction())
                ->setUser($user)
                ->setType(Transaction::TYPE_DEPOSIT)
                ->setAmount($amount);

            $user->setBalance((string) ((float) $user->getBalance() + (float) $amount));

            $this->entityManager->persist($transaction);
            $this->entityManager->persist($user);

            return $transaction;
        });
    }

    public function pay(User $user, Course $course): Transaction
    {
        if (!$course->isPaid()) {
            return (new Transaction())
                ->setUser($user)
                ->setCourse($course)
                ->setType(Transaction::TYPE_PAYMENT)
                ->setAmount('0.00');
        }

        $price = $course->getPrice() ?? '0.00';
        if ((float) $user->getBalance() < (float) $price) {
            throw new RuntimeException('На вашем счету недостаточно средств');
        }

        return $this->entityManager->wrapInTransaction(function () use ($user, $course, $price): Transaction {
            $transaction = (new Transaction())
                ->setUser($user)
                ->setCourse($course)
                ->setType(Transaction::TYPE_PAYMENT)
                ->setAmount($price);

            if ($course->getType() === Course::TYPE_RENT) {
                $transaction->setExpiresAt(new DateTimeImmutable('+1 week'));
            }

            $user->setBalance((string) ((float) $user->getBalance() - (float) $price));

            $this->entityManager->persist($transaction);
            $this->entityManager->persist($user);

            return $transaction;
        });
    }
}
