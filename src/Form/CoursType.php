<?php

// src/Form/CoursType.php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\Matieres;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On récupère les matières passées en option
        $matieres = $options['matieres'] ?? [];

        $builder
            // Champ pour le nom du cours
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control', 'minlength' => '2', 'maxlength' => '50'],
                'label' => 'Nom du cours',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du cours est requis.'])
                ]
            ])
            // Champ pour le fichier
            ->add('file', VichFileType::class, [
                'label' => 'Pièce jointe (PDF ou Word)',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'constraints' => [
                    new File ([
                        'mimeTypes' => [
                        'application/pdf', 
                        'application/msword', 
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF ou Word valide.',
                ])
                ]
            ])
            // Champ pour la matière
            ->add('matiere', EntityType::class, [
                'class' => Matieres::class,  // La classe de l'entité
                'choice_label' => 'name',     // Affiche le nom de la matière
                'label' => 'Choisir une matière',
                'placeholder' => 'Sélectionnez une matière',
                'required' => true
            ])
            // Champ pour soumettre le formulaire
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'w-full py-3 px-6 border border-transparent shadow-sm text-lg font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200 ease-in-out'],
                'label' => 'Enregistrer le cours'
            ]);
    }

    private function getMatiereChoices(array $matieres): array
    {
        $choices = [];
        foreach ($matieres as $matiere) {
            $choices[$matiere->getName()] = $matiere->getId(); // Nom de la matière => ID de la matière
        }
        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
            'matieres' => [], // On définit une valeur par défaut pour 'matieres'
        ]);
    }
}
