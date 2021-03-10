<?php

namespace App\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 * @Vich\Uploadable
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messagefrom")
     * @ORM\JoinColumn(nullable=false)
     *
     * @SerializedName("sender")
     * @Groups({"public"})
     */
    private $from_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messageto")
     * @ORM\JoinColumn(nullable=false)
     *
     * @SerializedName("receiver")
     * @Groups({"public"})
     */
    private $to_id;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups({"public"})
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({"public"})
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isRead;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cv;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fichier;

    /**
     * @Vich\UploadableField(mapping="product_images", fileNameProperty="fichier")
     * @var File
     */
    private $fichierFile;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromId(): ?User
    {
        return $this->from_id;
    }

    public function setFromId(?User $from_id): self
    {
        $this->from_id = $from_id;

        return $this;
    }

    public function getToId(): ?User
    {
        return $this->to_id;
    }

    public function setToId(?User $to_id): self
    {
        $this->to_id = $to_id;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(?bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(?string $cv): self
    {
        $this->cv = $cv;

        return $this;
    }

    // Upload fichier

    public function setFichierFile(File $fichier = null)
    {
        $this->fichierFile = $fichier;
    }
 
    public function getFichierFile()
    {
        return $this->fichierFile;
    }

    public function setFichier($fichier)
    {
        $this->fichier = $fichier;

    }

    public function getFichier()
    {
        return $this->fichier;
    }


}
