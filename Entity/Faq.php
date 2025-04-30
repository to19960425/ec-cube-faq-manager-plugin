<?php

namespace Plugin\FaqManager\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FAQ
 *
 * @ORM\Entity(repositoryClass="Plugin\FaqManager\Repository\FaqRepository")
 * @ORM\Table(name="plg_faq_manager_faq")
 */
class Faq
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
     * @ORM\Column(type="text")
     */
    private $question;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $answer;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $sort_no = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", options={"default": true})
     */
    private $visible = true;

    /**
     * @var FaqCategory|null
     *
     * @ORM\ManyToOne(targetEntity="Plugin\FaqManager\Entity\FaqCategory")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $category;

    // getter / setter

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;
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

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }

    public function getCategory(): ?FaqCategory
    {
        return $this->category;
    }

    public function setCategory(?FaqCategory $category): self
    {
        $this->category = $category;
        return $this;
    }
}
