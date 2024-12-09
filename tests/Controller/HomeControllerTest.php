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
}
