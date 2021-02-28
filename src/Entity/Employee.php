<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\DBAL\Exception;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(
 *     name="employees",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="email_idx", columns={"email"})})
 * @UniqueEntity("email",  message = "The email '{{ value }}' is already used.")
 * @ORM\HasLifecycleCallbacks
 */
class Employee
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Employee")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    public $children;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**   
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $parentEmail;

    /**
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="employees", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
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

    public function getParentEmail(): ?string
    {
        return $this->parentEmail;
    }

    public function setParentEmail(string $parentEmail)
    {
        $this->parentEmail = $parentEmail;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(?Employee $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @throws Exception
     */
    public function validateParent()
    {
        if ( $this->parent == null && $this->parentEmail != '-' ) {           
			throw new Exception('Validation error: parent’s email '.$this->parentEmail.' doesn’t exist in DB');
        }

    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

}
