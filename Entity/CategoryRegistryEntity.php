<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\CategoriesModule\Traits\StandardFieldsTrait;

/**
 * Category registry entity.
 *
 * @ORM\Entity(repositoryClass="Zikula\CategoriesModule\Entity\Repository\CategoryRegistryRepository")
 * @ORM\Table(name="categories_registry",indexes={@ORM\Index(name="idx_categories_registry",columns={"modname","entityname"})})
 */
class CategoryRegistryEntity extends EntityAccess
{
    use StandardFieldsTrait;

    /**
     * The id of the registry entry
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * The module name owning this entry
     *
     * @ORM\Column(type="string", length=60)
     * @Assert\Length(min="1", max="60")
     * @var string
     */
    private $modname;

    /**
     * The name of the entity
     *
     * @ORM\Column(type="string", length=60)
     * @Assert\Length(min="1", max="60")
     * @var string
     */
    private $entityname;

    /**
     * The property of the entity
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $property;

    /**
     * The category to map this entity to
     *
     * @ORM\ManyToOne(targetEntity="CategoryEntity", inversedBy="attributes")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @var CategoryEntity
     */
    private $category;

    /**
     * The status of the entity
     *
     * @ORM\Column(type="string", length=1, name="obj_status")
     * @Assert\Length(min="1", max="1")
     * @var string
     */
    protected $status = 'A';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getModname(): ?string
    {
        return $this->modname;
    }

    public function setModname(string $modname): self
    {
        $this->modname = $modname;

        return $this;
    }

    public function getEntityname(): ?string
    {
        return $this->entityname;
    }

    public function setEntityname(string $entityname): self
    {
        $this->entityname = $entityname;

        return $this;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function setProperty(string $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(CategoryEntity $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
