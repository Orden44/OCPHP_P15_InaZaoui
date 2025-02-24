<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Album;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumControllerTest extends WebTestCase
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

        $this->client->request('GET', '/admin/album');
        self::assertResponseIsSuccessful();

        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        foreach ($albums as $album) {
            $albumName = $album->getName();
            self::assertNotEmpty($albumName);
        }
    }

    public function testAddAlbum(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/admin/album/add');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'album[name]' => 'testAddAlbum',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/album');

        $this->client->followRedirect();

        $album = $this->entityManager->getRepository(Album::class)->findOneBy([
            'name' => 'testAddAlbum',
        ]);

        self::assertNotNull($album);
    }

    public function testEditAlbum(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $album = $this->entityManager->getRepository(Album::class)->findOneBy([
            'name' => 'Montagne',
        ]);

        if ($album === null) {
            $album = new Album();
            $album->setName('Montagne');
            $this->entityManager->persist($album);
            $this->entityManager->flush();
        }

        $crawler = $this->client->request('GET', '/admin/album/update/' . $album->getId());
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'album[name]' => 'Montagnes',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/album');

        $updateAlbum = $this->entityManager->getRepository(Album::class)->findOneBy([
            'name' => 'Montagnes',
        ]);

        self::assertNotNull($updateAlbum);
        self::assertSame('Montagnes', $updateAlbum->getName());
    }

    public function testDeleteAlbum(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'admin' => true,
        ]);
        self::assertNotNull($admin);

        $this->client->loginUser($admin);

        $album = new Album();
        $album->setName('created to delete');
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $albumId = $album->getId();
        $this->client->request('GET', '/admin/album/delete/' . $albumId);
        self::assertResponseRedirects('/admin/album');

        self::assertNull($this->entityManager->getRepository(Album::class)->find($albumId));
    }

}