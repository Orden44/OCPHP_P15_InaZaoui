<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testIsAdmin(): void
    {
        // Créer une instance de User
        $user = new User();

        // Vérifier que le statut admin est nul par défaut
        self::assertNull($user->isAdmin());

        // Définir l'administrateur à true
        $user->setAdmin(true);
        self::assertTrue($user->isAdmin());

        // Définir l'administrateur à false
        $user->setAdmin(false);
        self::assertFalse($user->isAdmin());    }
}