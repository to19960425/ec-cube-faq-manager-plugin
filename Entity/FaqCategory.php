<?php

namespace Plugin\FaqManager\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FAQカテゴリ
 *
 * @ORM\Entity(repositoryClass="Plugin\FaqManager\Repository\FaqCategoryRepository")
 * @ORM\Table(name="plg_faq_manager_faq_category")
 */
class FaqCategory
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $sort_no = 0;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $enabled = true;

    // getter / setter

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSortNo(): int
    {
        return $this->sort_no;
    }

    public function setSortNo(int $sort_no): self
    {
        $this->sort_no = $sort_no;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }
}
