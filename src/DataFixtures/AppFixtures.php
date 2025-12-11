<?php
namespace App\DataFixtures;
use App\Entity\Genre;
use App\Entity\Utilisateur;
use App\Entity\Collect;
use App\Enum\Collection;
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

        $games = [];

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
            $games[] = $jeu;
        }

        // 4. Create Users
        $firstNames = ['Alice', 'Bob', 'Charlie', 'David', 'Eve', 'Frank', 'Grace', 'Hank', 'Ivy', 'Jack', 'Liam', 'Noah', 'Emma', 'Olivia', 'Ava', 'Sophia'];
        $lastNames = ['Smith', 'Johnson', 'Brown', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Garcia'];

        for ($i = 0; $i < 10; $i++) {
            $user = new Utilisateur();

            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $user->setPrenom($firstName);
            $user->setNom($lastName);
            // Ensure unique pseudo
            $user->setPseudo($firstName . $lastName . rand(100, 999));
            $user->setMail(strtolower($firstName . '.' . $lastName . rand(1, 99) . '@example.com'));
            $user->setDateNaissance(new \DateTime('-' . rand(6570, 18250) . ' days')); // 18 to 50 years old

            // Random face image
            // Adding a random query param to ensure uniqueness if needed, or just relying on unique ID if supported. 
            // Pravatar supports /u/{identifier} to get consistent image for identifier, or just random
            // Let's use a consistent seed so it doesn't change on every page refresh if browser caches, 
            // but here it is a static URL. 
            // 'https://i.pravatar.cc/150?u=fake@pravatar.com'
            $user->setImageProfil('https://i.pravatar.cc/150?u=' . rand(1, 10000));

            $manager->persist($user);

            // Add Collections (5 to 10 games per user)
            $numGames = rand(5, 10);
            $shuffledGames = $games;
            shuffle($shuffledGames);
            $selectedGames = array_slice($shuffledGames, 0, $numGames);

            foreach ($selectedGames as $game) {
                $collect = new Collect();
                $collect->setUtilisateur($user);
                $collect->setJeuvideo($game);

                // Random Status
                $statuses = Collection::cases();
                $status = $statuses[array_rand($statuses)];
                $collect->setStatut($status);
                $collect->setDateModifStatut(new \DateTime());

                // Set Date Achat logic (must be >= dateSortie)
                if ($game->getDateSortie()) {
                    $dateSortie = clone $game->getDateSortie();
                    // Add random days (0 to 1000)
                    $dateAchat = (clone $dateSortie)->modify('+' . rand(1, 1000) . ' days');
                    // Ensure it doesn't exceed now too much or just reasonable
                    $now = new \DateTime();
                    if ($dateAchat > $now) {
                        $dateAchat = $now;
                    }
                    // Final check to satisfy constraint
                    if ($dateAchat < $dateSortie) {
                        $dateAchat = $dateSortie;
                    }
                    $collect->setDateAchat($dateAchat);
                }

                // Random price
                $collect->setPrixAchat(rand(1000, 7000) / 100);

                $manager->persist($collect);
            }
        }

        $manager->flush();
    }
}