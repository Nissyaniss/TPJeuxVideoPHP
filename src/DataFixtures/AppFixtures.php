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


        $adjectives = ['Super', 'Mega', 'Ultimate', 'Dark', 'Cyber', 'Fantasy', 'Space', 'Lost', 'Final', 'Red'];
        $nouns = ['Quest', 'Warrior', 'Kart', 'Souls', 'Punk', 'Fantasy', 'Odyssey', 'Legends', 'Horizon', 'Redemption'];

        $games = [];

        for ($i = 0; $i < 50; $i++) {
            $jeu = new JeuVideo();


            $title = $adjectives[array_rand($adjectives)] . ' ' . $nouns[array_rand($nouns)];
            $jeu->setTitre($title);

            $jeu->setDeveloppeur('Studio ' . rand(1, 20));
            $jeu->setDateSortie(new \DateTime('-' . rand(0, 3650) . ' days'));
            $jeu->setPrix(rand(1000, 8000) / 100);
            $jeu->setDescription('An exciting game full of adventures and challenges.');
            $jeu->setImageUrl('https://picsum.photos/seed/' . rand(1, 99999) . '/300/200');
            $jeu->setCreatedAt(new \DateTimeImmutable());

            $jeu->setGenre($genres[array_rand($genres)]);
            $jeu->setEditeur($editeurs[array_rand($editeurs)]);

            $manager->persist($jeu);
            $games[] = $jeu;
        }

        $firstNames = ['Alice', 'Bob', 'Charlie', 'David', 'Eve', 'Frank', 'Grace', 'Hank', 'Ivy', 'Jack', 'Liam', 'Noah', 'Emma', 'Olivia', 'Ava', 'Sophia'];
        $lastNames = ['Smith', 'Johnson', 'Brown', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Garcia'];

        for ($i = 0; $i < 10; $i++) {
            $user = new Utilisateur();

            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $user->setPrenom($firstName);
            $user->setNom($lastName);
            $user->setPseudo($firstName . $lastName . rand(100, 999));
            $user->setMail(strtolower($firstName . '.' . $lastName . rand(1, 99) . '@example.com'));
            $user->setDateNaissance(new \DateTime('-' . rand(6570, 18250) . ' days'));


            $user->setImageProfil('https://i.pravatar.cc/150?u=' . rand(1, 10000));

            $manager->persist($user);

            $numGames = rand(5, 10);
            $shuffledGames = $games;
            shuffle($shuffledGames);
            $selectedGames = array_slice($shuffledGames, 0, $numGames);

            foreach ($selectedGames as $game) {
                $collect = new Collect();
                $collect->setUtilisateur($user);
                $collect->setJeuvideo($game);

                $statuses = Collection::cases();
                $status = $statuses[array_rand($statuses)];
                $collect->setStatut($status);
                $collect->setDateModifStatut(new \DateTime());

                if ($game->getDateSortie()) {
                    $dateSortie = clone $game->getDateSortie();

                    $dateAchat = (clone $dateSortie)->modify('+' . rand(1, 1000) . ' days');
                    $now = new \DateTime();
                    if ($dateAchat > $now) {
                        $dateAchat = $now;
                    }
                    if ($dateAchat < $dateSortie) {
                        $dateAchat = $dateSortie;
                    }
                    $collect->setDateAchat($dateAchat);
                }

                $collect->setPrixAchat(rand(1000, 7000) / 100);

                $manager->persist($collect);
            }
        }

        $manager->flush();
    }
}