<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Album;
use App\Entity\Media;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use App\DataFixtures\AppFixtures;
use App\DataFixtures\AlbumFixtures;
use App\DataFixtures\MediaFixtures;
use App\DataFixtures\UserFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadFixtures([
            AppFixtures::class,
            AlbumFixtures::class,
            MediaFixtures::class,
            UserFixtures::class
        ]);
    }

    public function testHomePage(): void
    {
        $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Photographe');
    }

    public function testAboutPage(): void
    {
        $this->client->request('GET', '/about');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Qui suis-je ?');
    }

    public function testPortfolioPage(): void
    {
        $this->client->request('GET', '/portfolio');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Portfolio');

        $albums = $this->entityManager->getRepository(Album::class)->findAll();

        foreach ($albums as $album) {
            self::assertSelectorExists('a[href="/portfolio/' . $album->getId() . '"]');
        }

        $medias = $this->entityManager->getRepository(Media::class)->findAllMediasNotRestricted();

        foreach ($medias as $media) {
            self::assertSelectorExists('img[src="/' . $media->getPath() . '"]');
        }
    }

    public function testPortfolioPageWithId(): void
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();

        foreach ($albums as $album) {
            $medias = $this->entityManager->getRepository(Media::class)->findAllMediasNotRestrictedByAlbum($album);

            $this->client->request('GET', '/portfolio/' . $album->getId());

            self::assertResponseIsSuccessful();

            foreach ($medias as $media) {
                self::assertSelectorExists('img[src="/' . $media->getPath() . '"]');
            }
        }
    }

    public function testGuestsPage(): void
    {
        $this->client->request('GET', '/guests');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Invités');
    }

    public function testGuestPage(): void
    {
        $guest = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => false,
            'restricted' => false,
        ]);
        self::assertNotNull($guest);
        
        $this->client->request('GET', '/guest/' . $guest->getId());
        self::assertResponseIsSuccessful();

        $username = $guest->getUsername();
        self::assertNotNull($username);
        self::assertSelectorTextContains('h3', $username);
    }
}
