<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[Vich\Uploadable] // 🔴 Ajoute cette annotation pour que l'entité soit reconnue par VichUploader
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[Vich\UploadableField(mapping: 'cours_files', fileNameProperty: 'filePath')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        mimeTypesMessage: 'Veuillez télécharger un fichier PDF ou Word.'
    )]
    private ?File $file = null;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filePath = null; // 🔴 Stocke uniquement le nom du fichier

   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Matieres::class, inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Matieres $matiere = null;

    // Getter et Setter pour matiere
    public function getMatiere(): ?Matieres
    {
        return $this->matiere;
    }

    public function setMatiere(?Matieres $matiere): self
    {
        $this->matiere = $matiere;
        return $this;
    }

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fileName;

      /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $fileContent;

    public function getFileContent()
    {
        return $this->fileContent;
    }

    public function setFileContent($fileContent): self
    {
        $this->fileContent = $fileContent;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
{
    $this->file = $file;

    return $this;
}

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
}
