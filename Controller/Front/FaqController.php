<?php

namespace Plugin\FaqManager\Controller\Front;

use Eccube\Controller\AbstractController;
use Plugin\FaqManager\Entity\Faq;
use Plugin\FaqManager\Form\Type\Admin\FaqType;
use Plugin\FaqManager\Repository\FaqRepository;
use Plugin\FaqManager\Repository\FaqCategoryRepository;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    protected FaqRepository $faqRepository;
    protected FaqCategoryRepository $faqCategoryRepository;

    /**
     * @param FaqRepository $faqRepository
     */
    public function __construct(
        FaqRepository $faqRepository,
        FaqCategoryRepository $faqCategoryRepository
    ) {
        $this->faqRepository = $faqRepository;
        $this->faqCategoryRepository = $faqCategoryRepository;
    }

    /**
     * @Route("/faq", name="faq_manager_index", methods={"GET", "POST"})
     * @Template("FaqManager/Resource/template/default/faq/index.twig")
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $FaqCategories = $this->faqCategoryRepository->findEnabled();
        $Faqs = $this->faqRepository->findBy([
            'visible' => true
        ], [
            'sort_no' => 'ASC'
        ]);

        $groupedFaqs = [];
        foreach ($Faqs as $faq) {
            $groupedFaqs[$faq->getCategory()->getId()][] = $faq;
        }

        return [
            "faqCategories" => $FaqCategories,
            "groupedFaqs" => $groupedFaqs,
        ];
    }
}
