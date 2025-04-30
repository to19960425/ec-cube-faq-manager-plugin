<?php

namespace Plugin\FaqManager\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\FaqManager\Entity\FaqCategory;
use Plugin\FaqManager\Form\Type\Admin\FaqCategoryType;
use Plugin\FaqManager\Repository\FaqCategoryRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FaqCategoryController extends AbstractController
{
    /**
     * @var FaqCategoryRepository
     */
    protected $faqCategoryRepository;

    public function __construct(FaqCategoryRepository $faqCategoryRepository)
    {
        $this->faqCategoryRepository = $faqCategoryRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/faq_manager/category", name="admin_faq_manager_category", methods={"GET", "POST"})
     * @Template("@FaqManager/admin/faq_category.twig")
     */
    public function index(Request $request)
    {
        $FaqCategory = new FaqCategory();
        $FaqCategories = $this->faqCategoryRepository->findBy([], ['sort_no' => 'DESC']);

        // 新規登録用フォーム
        $builder = $this->formFactory
            ->createBuilder(FaqCategoryType::class, $FaqCategory);

        $form = $builder->getForm();

        // 編集用フォーム
        $forms = [];
        foreach ($FaqCategories as $EditFaqCategory) {
            $id = $EditFaqCategory->getId();
            $forms[$id] = $this->formFactory
                ->createNamed('faq_category_' . $id, FaqCategoryType::class, $EditFaqCategory);
        }

        if ('POST' === $request->getMethod()) {
            // 新規登録処理
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->faqCategoryRepository->save($form->getData());

                $this->addSuccess('admin.common.save_complete', 'admin');

                return $this->redirectToRoute('admin_faq_manager_category');
            }

            // 編集処理
            foreach ($forms as $editForm) {
                $editForm->handleRequest($request);
                if ($editForm->isSubmitted() && $editForm->isValid()) {
                    $this->faqCategoryRepository->save($editForm->getData());

                    $this->addSuccess('admin.common.save_complete', 'admin');

                    return $this->redirectToRoute('admin_faq_manager_category');
                }
            }
        }

        $formViews = [];
        foreach ($forms as $key => $value) {
            $formViews[$key] = $value->createView();
        }

        return [
            'form' => $form->createView(),
            'FaqCategory' => $FaqCategory,
            'FaqCategories' => $FaqCategories,
            'forms' => $formViews,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/faq_manager/category/{id}/delete", requirements={"id" = "\d+"}, name="admin_faq_manager_category_delete", methods={"DELETE"})
     */
    public function delete(Request $request, FaqCategory $FaqCategory)
    {
        $this->isTokenValid();

        log_info('FAQカテゴリ削除開始', [$FaqCategory->getId()]);

        try {
            $this->faqCategoryRepository->delete($FaqCategory);

            $this->addSuccess('admin.common.delete_complete', 'admin');

            log_info('FAQカテゴリ削除完了', [$FaqCategory->getId()]);
        } catch (\Exception $e) {
            log_error('FAQカテゴリ削除エラー', [$FaqCategory->getId(), $e]);

            $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $FaqCategory->getName()]);
            $this->addError($message, 'admin');
        }

        return $this->redirectToRoute('admin_faq_manager_category');
    }

    /**
     * @Route("/%eccube_admin_route%/faq_manager/category/sort_no/move", name="admin_faq_manager_category_sort_no_move", methods={"POST"})
     */
    public function moveSortNo(Request $request)
    {
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
            $sortNos = $request->request->all();
            foreach ($sortNos as $categoryId => $sortNo) {
                /** @var FaqCategory $FaqCategory */
                $FaqCategory = $this->faqCategoryRepository->find($categoryId);
                if ($FaqCategory) {
                    $FaqCategory->setSortNo((int)$sortNo);
                    $this->entityManager->persist($FaqCategory);
                }
            }
            $this->entityManager->flush();
        }

        return new Response();
    }
}
