<?php

namespace Plugin\FaqManager\Form\Type\Admin;

use Plugin\FaqManager\Entity\Faq;
use Plugin\FaqManager\Repository\FaqCategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FaqType extends AbstractType
{
    /**
     * @var FaqCategoryRepository
     */
    protected $faqCategoryRepository;

    /**
     * FaqType constructor.
     *
     * @param FaqCategoryRepository $faqCategoryRepository
     */
    public function __construct(FaqCategoryRepository $faqCategoryRepository)
    {
        $this->faqCategoryRepository = $faqCategoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', ChoiceType::class, [
                'required' => true,
                'choices' => $this->faqCategoryRepository->findAllSorted(),
                'choice_label' => function ($category) {
                    return $category->getName();
                },
                'placeholder' => 'common.select',
            ])
            ->add('question', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('answer', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('visible', ChoiceType::class, [
                'label' => false,
                'choices' => ['admin.common.show' => true, 'admin.common.hide' => false],
                'required' => true,
                'expanded' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Faq::class,
        ]);
    }
}
