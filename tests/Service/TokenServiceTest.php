<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\TokenService;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{
    public function testCreateAndParseToken(): void
    {
        $user = (new User())
            ->setEmail('student@example.com')
            ->setRoles(['ROLE_USER']);

        $service = new TokenService('secret');
        $payload = $service->parse($service->create($user));

        self::assertSame('student@example.com', $payload['sub']);
        self::assertContains('ROLE_USER', $payload['roles']);
        self::assertGreaterThan(time(), $payload['exp']);
    }

    public function testTamperedTokenIsRejected(): void
    {
        $user = (new User())->setEmail('student@example.com');
        $service = new TokenService('secret');
        $token = $service->create($user);

        self::assertNull($service->parse($token.'broken'));
    }
}
