<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use App\Entity\Media;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\AlbumFixtures;
use App\Entity\User;
use App\Entity\Album;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MediaFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const MEDIA_DATA = [
        ['user' => UserFixtures::USER_1, 'album' => AlbumFixtures::ALBUM_1, 'path' => 'uploads/Paysage_1.webp', 'title' => 'Paysage miroir'],
        ['user' => UserFixtures::USER_5, 'album' => AlbumFixtures::ALBUM_1, 'path' => 'uploads/Paysage_2.webp', 'title' => 'Quai face montagne'],
        ['user' => UserFixtures::USER_2, 'album' => AlbumFixtures::ALBUM_1, 'path' => 'uploads/Paysage_3.webp', 'title' => 'Colline verte'],
        ['user' => UserFixtures::USER_5, 'album' => AlbumFixtures::ALBUM_3, 'path' => 'uploads/Montagne_1.webp', 'title' => 'Pic vers le ciel'],
        ['user' => UserFixtures::USER_2, 'album' => AlbumFixtures::ALBUM_3, 'path' => 'uploads/Montagne_2.webp', 'title' => 'Chalet montagnard'],
        ['user' => UserFixtures::USER_3, 'album' => AlbumFixtures::ALBUM_3, 'path' => 'uploads/Ville_1.webp', 'title' => 'Purple city'],
        ['user' => UserFixtures::USER_2, 'album' => AlbumFixtures::ALBUM_2, 'path' => 'uploads/Ville_2.webp', 'title' => 'Pise'],
        ['user' => UserFixtures::USER_2, 'album' => AlbumFixtures::ALBUM_2, 'path' => 'uploads/Ville_3.webp', 'title' => 'Paris'],
    ];

    public function __construct(private UserPasswordHasherInterface $encoder) { }

    public function load(ObjectManager $manager): void
    {        
        // Création des médias
        foreach (self::MEDIA_DATA as $index => $data) {
            $media = new Media();
            $media->setUser($this->getReference($data['user'], User::class)) 
                ->setAlbum($this->getReference($data['album'], Album::class)) 
                ->setPath($data['path'])
                ->setTitle($data['title']);
            $manager->persist($media);
            // Utilisation de l'index pour ajouter une référence unique
            $this->addReference('media_' . $index, $media); 
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            AlbumFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['MediaFixtures'];
    }
}