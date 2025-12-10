<?php
namespace App\DataFixtures;
use App\Entity\Genre;
use App\Entity\Editeur;
use App\Entity\JeuVideo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Create Genres
        $genreNames = ['Action', 'RPG', 'Adventure', 'Strategy', 'Sports', 'Simulation', 'Horror', 'Puzzle'];
        $genres = [];

        foreach ($genreNames as $name) {
            $genre = new Genre();
            $genre->setNom($name);
            $genre->setDescription("Description for $name genre");
            $genre->setActif(true);
            $genre->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($genre);
            $genres[] = $genre;
        }

        // 2. Create Editeurs
        $editeurData = [
            ['Sony Interactive Entertainment', 'Japon', 'https://www.sie.com'],
            ['Nintendo', 'Japon', 'https://www.nintendo.com'],
            ['Microsoft Studios', 'USA', 'https://www.xbox.com'],
            ['Ubisoft', 'France', 'https://www.ubisoft.com'],
            ['Electronic Arts', 'USA', 'https://www.ea.com'],
            ['Square Enix', 'Japon', 'https://www.square-enix.com'],
            ['Capcom', 'Japon', 'https://www.capcom.com'],
            ['Bethesda', 'USA', 'https://bethesda.net'],
        ];
        $editeurs = [];

        foreach ($editeurData as $data) {
            $editeur = new Editeur();
            $editeur->setNom($data[0]);
            $editeur->setPays($data[1]);
            $editeur->setSiteWeb($data[2]);
            $editeur->setDescription("Leading game publisher from " . $data[1]);
            $editeur->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($editeur);
            $editeurs[] = $editeur;
        }

        // 3. Create 50 Video Games
        $adjectives = ['Super', 'Mega', 'Ultimate', 'Dark', 'Cyber', 'Fantasy', 'Space', 'Lost', 'Final', 'Red'];
        $nouns = ['Quest', 'Warrior', 'Kart', 'Souls', 'Punk', 'Fantasy', 'Odyssey', 'Legends', 'Horizon', 'Redemption'];

        for ($i = 0; $i < 50; $i++) {
            $jeu = new JeuVideo();

            // Random Title
            $title = $adjectives[array_rand($adjectives)] . ' ' . $nouns[array_rand($nouns)];
            $jeu->setTitre($title);

            $jeu->setDeveloppeur('Studio ' . rand(1, 20));
            $jeu->setDateSortie(new \DateTime('-' . rand(0, 3650) . ' days')); // Random date within last 10 years
            $jeu->setPrix(rand(1000, 8000) / 100); // Random price 10.00 to 80.00
            $jeu->setDescription('An exciting game full of adventures and challenges.');
            // Use Picsum for real random images
            // We use a random seed to get different images for each game
            $jeu->setImageUrl('https://picsum.photos/seed/' . rand(1, 99999) . '/300/200');
            $jeu->setCreatedAt(new \DateTimeImmutable());

            // Link to random Genre and Editeur
            $jeu->setGenre($genres[array_rand($genres)]);
            $jeu->setEditeur($editeurs[array_rand($editeurs)]);

            $manager->persist($jeu);
        }

        $manager->flush();
    }
}