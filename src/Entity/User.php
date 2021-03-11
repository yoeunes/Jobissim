<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Vich\Uploadable
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"public"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"public"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"public"})
     */
    private $lastname;

    /**
     * @ORM\OneToMany(targetEntity=FormationLike::class, mappedBy="user")
     */
    private $formationLikes;

    /**
     * @ORM\OneToMany(targetEntity=EmploiLike::class, mappedBy="user")
     */
    private $emploiLikes;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="from_id")
     */
    private $messagefrom;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="to_id")
     */
    private $messageto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"public"})
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="product_images", fileNameProperty="image")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cv;

    /**
     * @Vich\UploadableField(mapping="product_images", fileNameProperty="cv")
     * @var File
     */
    private $cvFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lettre;

    /**
     * @Vich\UploadableField(mapping="product_images", fileNameProperty="lettre")
     * @var File
     */
    private $lettreFile;

    /**
     * @ORM\OneToMany(targetEntity=Formation::class, mappedBy="auteur", cascade={"remove"})
     */
    private $formations;

    /**
     * @ORM\OneToMany(targetEntity=Emploi::class, mappedBy="auteur", cascade={"remove"})
     */
    private $emplois;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="cv")
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="user")
     */
    private $orders;

    /**
     * @ORM\OneToOne(targetEntity=Cvtheque::class, mappedBy="reference", cascade={"remove"})
     */
    private $cvtheque;

    /**
     * @ORM\OneToMany(targetEntity=CvLike::class, mappedBy="user", cascade={"remove"})
     */
    private $cvLikes;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cvonline;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $siret;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $compte;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $naissance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $postale;

    /**
     * @ORM\Column(type="string", length=600, nullable=true)
     */
    private $about;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $annonces;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $civilite;

    /**
     * Date/Time of the last activity
     *
     * @var \DateTime
     *
     * @ORM\Column(name="last_activity", type="datetime", nullable=true)
     */
    private $lastActivity;

    public function __construct()
    {
        $this->formationLikes = new ArrayCollection();
        $this->emploiLikes = new ArrayCollection();
        $this->messagefrom = new ArrayCollection();
        $this->messageto = new ArrayCollection();
        $this->formations = new ArrayCollection();
        $this->emplois = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->cvLikes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }


    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFullName(): string
    {
        return $this->getFirstname().' '.$this->getLastname();
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return Collection|FormationLike[]
     */
    public function getFormationLikes(): Collection
    {
        return $this->formationLikes;
    }

    public function addFormationLike(FormationLike $formationLike): self
    {
        if (!$this->formationLikes->contains($formationLike)) {
            $this->formationLikes[] = $formationLike;
            $formationLike->setUser($this);
        }

        return $this;
    }

    public function removeFormationLike(FormationLike $formationLike): self
    {
        if ($this->formationLikes->removeElement($formationLike)) {
            // set the owning side to null (unless already changed)
            if ($formationLike->getUser() === $this) {
                $formationLike->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EmploiLike[]
     */
    public function getEmploiLikes(): Collection
    {
        return $this->emploiLikes;
    }

    public function addEmploiLike(EmploiLike $emploiLike): self
    {
        if (!$this->emploiLikes->contains($emploiLike)) {
            $this->emploiLikes[] = $emploiLike;
            $emploiLike->setUser($this);
        }

        return $this;
    }

    public function removeEmploiLike(EmploiLike $emploiLike): self
    {
        if ($this->emploiLikes->removeElement($emploiLike)) {
            // set the owning side to null (unless already changed)
            if ($emploiLike->getUser() === $this) {
                $emploiLike->setUser(null);
            }
        }

        return $this;
    }



    /**
     * @return Collection|Message[]
     */
    public function getMessagefrom(): Collection
    {
        return $this->messagefrom;
    }

    public function addMessagefrom(Message $messagefrom): self
    {
        if (!$this->messagefrom->contains($messagefrom)) {
            $this->messagefrom[] = $messagefrom;
            $messagefrom->setFromId($this);
        }

        return $this;
    }

    public function removeMessagefrom(Message $messagefrom): self
    {
        if ($this->messagefrom->removeElement($messagefrom)) {
            // set the owning side to null (unless already changed)
            if ($messagefrom->getFromId() === $this) {
                $messagefrom->setFromId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessageto(): Collection
    {
        return $this->messageto;
    }

    public function addMessageto(Message $messageto): self
    {
        if (!$this->messageto->contains($messageto)) {
            $this->messageto[] = $messageto;
            $messageto->setToId($this);
        }

        return $this;
    }

    public function removeMessageto(Message $messageto): self
    {
        if ($this->messageto->removeElement($messageto)) {
            // set the owning side to null (unless already changed)
            if ($messageto->getToId() === $this) {
                $messageto->setToId(null);
            }
        }

        return $this;
    }


    public function __toString()
    {
        return $this->getUsername();
    }

    // Upload image

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // if ($image) {
        //     $this->updatedAt = new \DateTime('now');
        // }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }


    public function setImage($image)
    {
        $this->image = $image;

    }

    public function getImage()
    {
        return $this->image;
    }

    // Upload cv

    public function setCvFile(File $cv = null)
    {
        $this->cvFile = $cv;

        // if ($image) {
        //     $this->updatedAt = new \DateTime('now');
        // }
    }

    public function getCvFile()
    {
        return $this->cvFile;
    }


    public function setCv($cv)
    {
        $this->cv = $cv;

    }


    public function getCv()
    {
        return $this->cv;
    }

    // Upload lettre

    public function setLettreFile(File $lettre = null)
    {
        $this->lettreFile = $lettre;

        // if ($image) {
        //     $this->updatedAt = new \DateTime('now');
        // }
    }

    public function getLettreFile()
    {
        return $this->lettreFile;
    }

    public function setLettre($lettre)
    {
        $this->lettre = $lettre;

    }


    public function getLettre()
    {
        return $this->lettre;
    }


    /**
     * @return Collection|Formation[]
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): self
    {
        if (!$this->formations->contains($formation)) {
            $this->formations[] = $formation;
            $formation->setAuteur($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): self
    {
        if ($this->formations->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getAuteur() === $this) {
                $formation->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Emploi[]
     */
    public function getEmplois(): Collection
    {
        return $this->emplois;
    }

    public function addEmploi(Emploi $emploi): self
    {
        if (!$this->emplois->contains($emploi)) {
            $this->emplois[] = $emploi;
            $emploi->setAuteur($this);
        }

        return $this;
    }

    public function removeEmploi(Emploi $emploi): self
    {
        if ($this->emplois->removeElement($emploi)) {
            // set the owning side to null (unless already changed)
            if ($emploi->getAuteur() === $this) {
                $emploi->setAuteur(null);
            }
        }

        return $this;
    }

    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->password,
            $this->email,
        ]);
    }

    public function unserialize($serialized): void
    {
        list($this->id, $this->password, $this->email) = unserialize($serialized);
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    public function getCvtheque(): ?Cvtheque
    {
        return $this->cvtheque;
    }

    public function setCvtheque(Cvtheque $cvtheque): self
    {
        // set the owning side of the relation if necessary
        if ($cvtheque->getReference() !== $this) {
            $cvtheque->setReference($this);
        }

        $this->cvtheque = $cvtheque;

        return $this;
    }

    /**
     * @return Collection|CvLike[]
     */
    public function getCvLikes(): Collection
    {
        return $this->cvLikes;
    }

    public function addCvLike(CvLike $cvLike): self
    {
        if (!$this->cvLikes->contains($cvLike)) {
            $this->cvLikes[] = $cvLike;
            $cvLike->setUser($this);
        }

        return $this;
    }

    public function removeCvLike(CvLike $cvLike): self
    {
        if ($this->cvLikes->removeElement($cvLike)) {
            // set the owning side to null (unless already changed)
            if ($cvLike->getUser() === $this) {
                $cvLike->setUser(null);
            }
        }

        return $this;
    }

    public function getCvonline(): ?bool
    {
        return $this->cvonline;
    }

    public function setCvonline(?bool $cvonline): self
    {
        $this->cvonline = $cvonline;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getCompte(): ?string
    {
        return $this->compte;
    }

    public function setCompte(?string $compte): self
    {
        $this->compte = $compte;

        return $this;
    }

    public function getNaissance(): ?\DateTimeInterface
    {
        return $this->naissance;
    }

    public function setNaissance(?\DateTimeInterface $naissance): self
    {
        $this->naissance = $naissance;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getPostale(): ?string
    {
        return $this->postale;
    }

    public function setPostale(?string $postale): self
    {
        $this->postale = $postale;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = $about;

        return $this;
    }

    public function getAnnonces(): ?int
    {
        return $this->annonces;
    }

    public function setAnnonces(?int $annonces): self
    {
        $this->annonces = $annonces;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): self
    {
        $this->civilite = $civilite;

        return $this;
    }

    /**
     * @return bool
     *
     * @SerializedName("isOnline")
     * @Groups({"public"})
     */
    public function isOnline(): bool
    {
        return $this->getLastActivity() > new \DateTime('2 minutes ago');
    }

    /**
     * @return \DateTime|null
     */
    public function getLastActivity(): ?\DateTime
    {
        return $this->lastActivity;
    }

    /**
     * @param \DateTime $lastActivity
     *
     * @return self
     */
    public function setLastActivity(\DateTime $lastActivity): self
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    public function refreshLastActivity(): self
    {
        $this->setLastActivity(new \DateTime());

        return $this;
    }
}
