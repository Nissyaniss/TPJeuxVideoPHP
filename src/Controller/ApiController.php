<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\JeuVideo;
use App\Repository\GenreRepository;
use App\Repository\JeuVideoRepository;

use Psr\Log\LoggerInterface;

#[Route('/api')]
class ApiController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/jeu_video', name: 'api_jeu_video_index', methods: ['GET'])]
    public function getJeuVideos(JeuVideoRepository $jeuVideoRepository): JsonResponse
    {
        $data = [];
        foreach ($jeuVideoRepository->findAll() as $jeu) {
            $data[] = [
                'id' => $jeu->getId(),
                'titre' => $jeu->getTitre(),
                'image' => $jeu->getImageUrl(),
                'genre' => $jeu->getGenre() ? $jeu->getGenre()->getNom() : null,
                'editeur' => $jeu->getEditeur() ? $jeu->getEditeur()->getNom() : null,
            ];
        }
        return $this->json($data);
    }

    #[Route('/jeu_video/{id}', name: 'api_jeu_video_show', methods: ['GET'])]
    public function getJeuVideo(JeuVideo $jeu): JsonResponse
    {
        return $this->json([
            'id' => $jeu->getId(),
            'titre' => $jeu->getTitre(),
            'date_sortie' => $jeu->getDateSortie() ? $jeu->getDateSortie()->format('Y-m-d') : null,
            'description' => $jeu->getDescription(),
            'prix' => $jeu->getPrix(),
            'image' => $jeu->getImageUrl(),
            'genre' => $jeu->getGenre() ? [
                'id' => $jeu->getGenre()->getId(),
                'nom' => $jeu->getGenre()->getNom(),
            ] : null,
            'editeur' => $jeu->getEditeur() ? [
                'id' => $jeu->getEditeur()->getId(),
                'nom' => $jeu->getEditeur()->getNom(),
            ] : null,
        ]);
    }

    #[Route('/genre', name: 'api_genre_index', methods: ['GET'])]
    public function getGenres(GenreRepository $genreRepository): JsonResponse
    {
        $data = [];
        foreach ($genreRepository->findAll() as $genre) {
            $data[] = [
                'id' => $genre->getId(),
                'nom' => $genre->getNom(),
                'actif' => $genre->isActif(),
            ];
        }
        return $this->json($data);
    }

    #[Route('/genre/{id}', name: 'api_genre_show', methods: ['GET'])]
    public function getGenre(Genre $genre): JsonResponse
    {
        return $this->json([
            'id' => $genre->getId(),
            'nom' => $genre->getNom(),
            'description' => $genre->getDescription(),
            'actif' => $genre->isActif(),
            'jeux_lies' => count($genre->getJeuVideos()),
        ]);
    }

    #[Route('/utilisateur/{id}/collection', name: 'api_utilisateur_collection', methods: ['GET'])]
    public function getUserCollection(Utilisateur $utilisateur): JsonResponse
    {
        $data = [];

        foreach ($utilisateur->getCollections() as $collect) {
            $data[] = [
                'id' => $collect->getId(),
                'jeu' => [
                    'id' => $collect->getJeuvideo()->getId(),
                    'titre' => $collect->getJeuvideo()->getTitre(),
                    'image' => $collect->getJeuvideo()->getImageUrl(),
                ],
                'statut' => $collect->getStatut()->getLabel(),
                'prix_achat' => $collect->getPrixAchat(),
                'date_achat' => $collect->getDateAchat() ? $collect->getDateAchat()->format('Y-m-d') : null,
                'commentaire' => $collect->getCommentaire(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/genre/{id}', name: 'api_genre_delete', methods: ['DELETE'])]
    public function deleteGenre(Genre $genre, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $entityManager->remove($genre);
            $entityManager->flush();

            return $this->json(['message' => 'Le genre a été supprimé avec succès.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la suppression : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/ping', name: 'api_ping', methods: ['GET'])]
    public function ping(): Response
    {
        return new Response('pong', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    #[Route('/healthcheck', name: 'api_healthcheck', methods: ['GET'])]
    public function healthcheck(EntityManagerInterface $entityManager): JsonResponse
    {
        $status = [
            'api' => 'ok',
            'database' => 'unknown',
        ];

        try {
            $connection = $entityManager->getConnection();
            $connection->connect();
            if ($connection->isConnected()) {
                $status['database'] = 'ok';
            } else {
                $status['database'] = 'error';
            }
        } catch (\Exception $e) {
            $status['database'] = 'error';
            $status['database_error'] = $e->getMessage();
        }

        return $this->json($status);
    }
}
