<?php

namespace App\Controller;

use App\Entity\Matieres;
use App\Form\MatieresType;
use App\Repository\MatieresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MatieresController extends AbstractController
{
    /**
     * Affiche la liste de toutes les matières.
     */
    #[Route('/matieres', name: 'matieres.index', methods: ['GET'])]
    public function index(MatieresRepository $repository): Response
    {
        return $this->render('pages/matieres/index.html.twig', [
            'matieres' => $repository->findAll(),
        ]);
    }

    /**
     * Affiche le formulaire de création d'une nouvelle matière.
     */
    #[Route('/matieres/nouveau', name: 'matieres.new', methods: ['GET', 'POST'])]
    public function new(EntityManagerInterface $manager, Request $request): Response
    {
        $matiere = new Matieres();
        $form = $this->createForm(MatieresType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($matiere);
            $manager->flush();

            $this->addFlash('success', 'Votre matière a été créée avec succès !');

            return $this->redirectToRoute('matieres.index');
        }

        return $this->render('pages/matieres/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifie une matière existante.
     */
    #[Route('/matieres/edition/{id}', name: 'matieres.edit', methods: ['GET', 'POST'])]
    public function edit(
        MatieresRepository $repository,
        int $id,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $matiere = $repository->find($id);
        if (!$matiere) {
            $this->addFlash('error', 'Matière non trouvée.');
            return $this->redirectToRoute('matieres.index');
        }

        $form = $this->createForm(MatieresType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'Votre matière a été modifiée avec succès !');
            return $this->redirectToRoute('matieres.index');
        }

        return $this->render('pages/matieres/edit.html.twig', [
            'form' => $form->createView(),
            'matiere' => $matiere,
        ]);
    }

    /**
     * Supprime une matière de manière sécurisée via POST.
     */
    #[Route('/matieres/suppression/{id}', name: 'matieres.delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $manager, Matieres $matiere): Response
    {
        if ($this->isCsrfTokenValid('delete' . $matiere->getId(), $request->request->get('_token'))) {
            $manager->remove($matiere);
            $manager->flush();

            $this->addFlash('success', 'Votre matière a été supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('matieres.index');
    }


    /**
     * Affiche les détails d'une matière ainsi que ses cours associés.
     */
    #[Route('/matieres/{id}', name: 'matieres.show', methods: ['GET'])]
    public function show(Matieres $matiere): Response
    {
        return $this->render('pages/matieres/show.html.twig', [
            'matiere' => $matiere,
            'cours' => $matiere->getCours(), // Assurez-vous que cette relation existe dans l'entité Matieres
        ]);
    }
}
