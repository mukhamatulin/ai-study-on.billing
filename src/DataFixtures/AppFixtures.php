<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\User;
use App\Service\PaymentService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly PaymentService $paymentService,
        private readonly string $billingInitialDeposit,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $courses = [
            ['symfony-start', 'Symfony для старта проекта', Course::TYPE_FREE, null],
            ['doctrine-practice', 'Doctrine ORM на практике', Course::TYPE_RENT, '99.90'],
            ['secure-api', 'Безопасное API', Course::TYPE_BUY, '159.00'],
        ];

        foreach ($courses as [$code, $title, $type, $price]) {
            $manager->persist((new Course())
                ->setCode($code)
                ->setTitle($title)
                ->setType($type)
                ->setPrice($price));
        }

        $student = (new User())->setEmail('student@example.com');
        $student->setPassword($this->hasher->hashPassword($student, 'password'));
        $manager->persist($student);

        $admin = (new User())->setEmail('admin@example.com')->setRoles(['ROLE_SUPER_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        $manager->flush();

        $this->paymentService->deposit($student, $this->billingInitialDeposit);
        $this->paymentService->deposit($admin, $this->billingInitialDeposit);
    }
}
