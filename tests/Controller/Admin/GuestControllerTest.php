<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\GuestController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GuestControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testIndexAlbumPage(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/guest');
        self::assertResponseIsSuccessful();

        $guests = $this->entityManager->getRepository(User::class)->findBy([
            'admin' => false,
        ]);
        foreach ($guests as $guest) {
            $username = $guest->getUsername();
            self::assertNotNull($username);
        }
    }

    public function testAddGuest(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/admin/guest/add');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'guest[username]' => 'Aurélie',
            'guest[email]' => 'aurelie@gmail.com',
            'guest[password][first]' => 'Aurélie',
            'guest[password][second]' => 'Aurélie',
            'guest[description]' => 'description Aurélie',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/guest');

        $guest = $this->entityManager->getRepository(User::class)->findOneBy([
            'username' => 'Aurélie',
        ]);

        self::assertNotNull($guest);
    }

    public function testBlockGuest(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $guest = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
            'restricted' => false,
        ]);
        self::assertNotNull($guest);

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/guest/block/' . $guest->getId());
        self::assertResponseRedirects('/admin/guest');

        $updatedGuest = $this->entityManager->getRepository(User::class)->find($guest->getId());
        self::assertNotNull($updatedGuest);
        self::assertTrue($updatedGuest->isRestricted());
    }

    public function testUnblockGuest(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);
        
        $guest = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
            'restricted' => true,
        ]);
        self::assertNotNull($guest);

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/guest/unblock/' . $guest->getId());
        self::assertResponseRedirects('/admin/guest');

        $updatedGuest = $this->entityManager->getRepository(User::class)->find($guest->getId());
        self::assertNotNull($updatedGuest);
        self::assertFalse($updatedGuest->isRestricted());
    }

    public function testDeleteGuest(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'username' => 'Aurélie',
        ]);

        $userId = $user->getId();
        self::assertNotNull($userId);

        $this->client->request('GET', '/admin/guest/delete/' . $user->getId());
        self::assertResponseRedirects('/admin/guest');

        self::assertNull($this->entityManager->getRepository(User::class)->find($userId));
    }

    public function testConstructGuest(): void
    {
        $passwordHasher = self::getContainer()->get('security.password_hasher');
        $guestController = new GuestController($this->entityManager, $passwordHasher);
        
        self::assertInstanceOf(GuestController::class, $guestController);
        self::assertSame($this->entityManager, $guestController->getEntityManager());
        self::assertSame($passwordHasher, $guestController->getPasswordHasher());    }
}
