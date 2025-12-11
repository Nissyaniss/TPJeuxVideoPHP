<?php

namespace App\Controller;

use App\Entity\Collect;
use App\Entity\Utilisateur;
use App\Form\CollectType;
use App\Repository\CollectRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Psr\Log\LoggerInterface;

#[Route('/collect')]
class CollectController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_collect_index', methods: ['GET'])]
    public function index(CollectRepository $collectRepository): Response
    {
        return $this->render('collect/index.html.twig', [
            'collects' => $collectRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_collect_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $collect = new Collect();
        $collect->setUtilisateur($utilisateur);
        $collect->setDateModifStatut(new \DateTime());

        $form = $this->createForm(CollectType::class, $collect);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($collect);
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_show', ['id' => $utilisateur->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('collect/new.html.twig', [
            'collect' => $collect,
            'form' => $form,
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_collect_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Collect $collect, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CollectType::class, $collect);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collect->setDateModifStatut(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_show', ['id' => $collect->getUtilisateur()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('collect/edit.html.twig', [
            'collect' => $collect,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_collect_delete', methods: ['POST'])]
    public function delete(Request $request, Collect $collect, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $collect->getId(), $request->request->get('_token'))) {
            $utilisateurId = $collect->getUtilisateur()->getId();
            $entityManager->remove($collect);
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_show', ['id' => $utilisateurId], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_utilisateur_show', ['id' => $collect->getUtilisateur()->getId()], Response::HTTP_SEE_OTHER);
    }
}
