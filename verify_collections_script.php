<?php

use App\Entity\Collect;
use App\Entity\JeuVideo;
use App\Entity\Utilisateur;
use App\Entity\Genre;
use App\Entity\Editeur;
use App\Enum\Collection;
use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    return new class ($kernel) {
        private Kernel $kernel;

        public function __construct(Kernel $kernel)
        {
            $this->kernel = $kernel;
        }

        public function __invoke()
        {
            $this->kernel->boot();
            $container = $this->kernel->getContainer();
            $em = $container->get('doctrine')->getManager();

            echo "Verifying Collections Logic...\n";

            // Create dependencies
            $genre = new Genre();
            $genre->setNom('Test Genre ' . uniqid());
            $em->persist($genre);

            $editeur = new Editeur();
            $editeur->setNom('Test Editeur ' . uniqid());
            $em->persist($editeur);

            // Create Game
            $jeu = new JeuVideo();
            $jeu->setTitre('Test Game ' . uniqid());
            $jeu->setDeveloppeur('Test Dev');
            $jeu->setGenre($genre);
            $jeu->setEditeur($editeur);
            $em->persist($jeu);

            // Create User
            $user = new Utilisateur();
            $user->setPseudo('testuser' . uniqid());
            $user->setMail('test' . uniqid() . '@example.com');
            $user->setPrenom('Test');
            $user->setNom('User');


            $em->persist($user);

            // Create Collection Link
            $collect = new Collect();
            $collect->setUtilisateur($user);
            $collect->setJeuvideo($jeu);
            $collect->setStatut(Collection::POSSEDE);
            $collect->setPrixAchat(59.99);
            $em->persist($collect);

            $em->flush();

            // Refresh Game
            $em->refresh($jeu);

            echo "Checking if Game knows about the Collection...\n";
            $collections = $jeu->getCollections();

            if ($collections->count() > 0) {
                echo "SUCCESS: Game has {$collections->count()} collection entries.\n";
                $first = $collections->first();
                echo "Collection Status: " . $first->getStatut()->name . "\n";
                echo "User: " . $first->getUtilisateur()->getPseudo() . "\n";
            } else {
                echo "FAILURE: Game has 0 collection entries.\n";
                exit(1);
            }
        }
    };
};
