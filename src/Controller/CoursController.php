<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Matieres;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;


class CoursController extends AbstractController
{

    /**
     * This controller display all products
     *
     * @param CoursRepository $repository
     * @return Response
     */
    #[Route('/cours', name: 'cours.index', methods: ['GET'])]
    public function index(CoursRepository $repository): Response
    {
        return $this->render('pages/cours/index.html.twig', [
            'cours' => $repository->findAll()
        ]);
    }

    /**
     * This controller show a form which create a product
     *
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route('/cours/nouveau', name: 'cours.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager, SluggerInterface $slugger): Response
    {
        // Création du nouvel objet Cours
        $cours = new Cours();

        // Récupérer toutes les matières disponibles
        $matieres = $manager->getRepository(Matieres::class)->findAll();

        // Créer le formulaire, en passant la liste des matières
        $form = $this->createForm(CoursType::class, $cours, [
            'matieres' => $matieres
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    // Lire le fichier dans un flux binaire
                    $fileContent = file_get_contents($file->getRealPath());

                    // Enregistre le contenu binaire du fichier dans la base de données
                    $cours->setFileContent($fileContent);

                    // Si tu veux aussi enregistrer le nom du fichier dans la base de données
                    $cours->setFilePath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Impossible d\'uploader le fichier.');
                }
            }

            // Persister le cours dans la base de données
            $manager->persist($cours);
            $manager->flush();

            // Ajouter un message de succès
            $this->addFlash('success', 'Cours ajouté avec succès !');

            // Redirection vers la liste des cours
            return $this->redirectToRoute('cours.index');
        }

        // Rendu du formulaire dans la vue
        return $this->render('pages/cours/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/cours/edition/{id}', 'cours.edit', methods: ['GET', 'POST'])]
    public function edit(
        CoursRepository $repository,
        int $id,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        // Récupérer le cours à modifier
        $cours = $repository->find($id);
        if (!$cours) {
            $this->addFlash('error', 'Cours non trouvé.');
            return $this->redirectToRoute('cours.index');
        }

        // Créer le formulaire
        $form = $this->createForm(CoursType::class, $cours);

        // Traitement du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier (si un nouveau fichier est téléchargé)
            $file = $form->get('file')->getData();
            if ($file) {
                // Lire le contenu binaire du fichier téléchargé
                $fileContent = file_get_contents($file->getPathname());

                // Remplacer le contenu binaire du fichier dans l'entité
                $cours->setFileContent($fileContent);  // Remplacer le contenu binaire
                $cours->setFileName($file->getClientOriginalName());  // Mettre à jour le nom du fichier
            }

            try {
                // Persister les modifications dans la base de données
                $manager->persist($cours);
                $manager->flush();
                $this->addFlash('success', 'Cours modifié avec succès.');
            } catch (\Doctrine\DBAL\Exception $e) {
                dump($e->getMessage());  // Pour déboguer
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour.');
            }

            return $this->redirectToRoute('cours.index');
        }

        // Retourner la vue avec le formulaire et le cours
        return $this->render('pages/cours/edit.html.twig', [
            'form' => $form->createView(),
            'cours' => $cours,
        ]);
    }





    #[Route('/cours/suppression/{id}', name: 'cours.delete', methods: ['GET'])]
    public function delete(EntityManagerInterface $manager, Cours $cours): Response
    {

        if (!$cours) {

            $this->addFlash(
                'success',
                'Votre cours n\'a pas été trouvé'
            );

            return $this->redirectToRoute('cours.index');
        }
        $manager->remove($cours);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre cours à été supprimé avec succés'
        );

        return $this->redirectToRoute('cours.index');
    }

    #[Route('/cours/{id}', name: 'cours.show', methods: ['GET'])]
    public function show(Cours $cours): Response
    {
        return $this->render('pages/cours/show.html.twig', [
            'cours' => $cours,
        ]);
    }


    #[Route("/cours/telecharger/{id}", name: "cours.download", methods: ["GET"])]

    public function download(Cours $cours): Response
    {
        // Vérifier si le fichier existe dans la base de données
        if ($cours->getFilePath()) {
            // Ajouter le répertoire où le fichier est stocké
            $uploadDirectory = $this->getParameter('uploads_directory'); // Par exemple 'public/uploads/cours/'

            // Créer le chemin complet du fichier
            $filePath = $uploadDirectory . '/' . $cours->getFilePath();

            // Vérifier si le fichier existe réellement
            if (file_exists($filePath)) {
                // Récupérer le contenu du fichier
                $fileContent = file_get_contents($filePath);

                // Créer une réponse avec le contenu du fichier
                $response = new Response($fileContent);

                // Définir le type MIME du fichier (par exemple, PDF)
                $response->headers->set('Content-Type', 'application/pdf'); // Change selon le type du fichier
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $cours->getFilePath() . '"');

                return $response;
            } else {
                // Si le fichier n'existe pas
                throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
            }
        }

        // Si le fichier n'est pas trouvé dans la base de données
        throw $this->createNotFoundException('Le fichier n\'a pas été trouvé dans la base de données.');
    }
}
