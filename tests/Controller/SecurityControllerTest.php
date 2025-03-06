<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testLoginPage(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion');
    }

    public function testRestrictedUserCannotLogin(): void
    {
        $restrictedUser = $this->entityManager->getRepository(User::class)->findOneBy([
            'restricted' => true
        ]);
        self::assertNotNull($restrictedUser);

        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();
    }

    public function testLogoutRedirectsUser(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'restricted' => false
        ]);
        self::assertNotNull($user);

        // Simuler la connexion de l'utilisateur
        $this->client->loginUser($user);

        // Simuler une déconnexion
        $this->client->request('GET', '/logout');

        // Vérifier que l'utilisateur est redirigé après la déconnexion
        self::assertResponseRedirects('/');

        // Suivre la redirection et vérifier la présence du lien de connexion
        $this->client->followRedirect();
        self::assertSelectorExists('a[href="/login"]');
    }
}