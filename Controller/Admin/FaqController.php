<?php

namespace Plugin\FaqManager\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\FaqManager\Entity\Faq;
use Plugin\FaqManager\Form\Type\Admin\FaqType;
use Plugin\FaqManager\Repository\FaqRepository;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    protected FaqRepository $faqRepository;

    /**
     * @param FaqRepository $faqRepository
     */
    public function __construct(
        FaqRepository $faqRepository
    ) {
        $this->faqRepository = $faqRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/faq_manager", name="admin_faq_manager_faq", methods={"GET", "POST"})
     * @Template("@FaqManager/admin/index.twig")
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $Faqs = $this->faqRepository->findBy([], ['sort_no' => 'DESC']);
        return [
            "Faqs" => $Faqs,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/faq_manager/new", name="admin_faq_manager_new", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/faq_manager/{id}/edit", requirements={"id" = "\d+"}, name="admin_faq_manager_edit", methods={"GET", "POST"})
     * @Template("@FaqManager/admin/edit.twig")
     */
    public function edit(Request $request, Faq $Faq = null)
    {
        if (is_null($Faq)) {
            $Faq = $this->faqRepository->findOneBy([], ['sort_no' => 'DESC']);
            $sortNo = 1;
            if ($Faq) {
                $sortNo = $Faq->getSortNo() + 1;
            }

            $Faq = new Faq();
            $Faq
                ->setSortNo($sortNo);
        }

        $builder = $this->formFactory
            ->createBuilder(FaqType::class, $Faq);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Faq' => $Faq,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_SETTING_SHOP_PAYMENT_EDIT_INITIALIZE);

        $form = $builder->getForm();

        $form->setData($Faq);
        $form->handleRequest($request);

        // 登録ボタン押下
        if ($form->isSubmitted() && $form->isValid()) {
            $Faq = $form->getData();

            $this->entityManager->persist($Faq);
            $this->entityManager->flush();

            $event = new EventArgs(
                [
                    'form' => $form,
                    'Faq' => $Faq,
                ],
                $request
            );
            $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_SETTING_SHOP_PAYMENT_EDIT_COMPLETE);

            $this->addSuccess('admin.common.save_complete', 'admin');

            return $this->redirectToRoute('admin_faq_manager_edit', ['id' => $Faq->getId()]);
        }

        return [
            'form' => $form->createView(),
            'faq_id' => $Faq->getId(),
            'Faq' => $Faq,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/faq_manager/{id}/delete", requirements={"id" = "\d+"}, name="admin_faq_manager_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param Faq $targetFaq
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, Faq $targetFaq)
    {
        $this->isTokenValid();

        $sortNo = 1;
        $Faqs = $this->faqRepository->findBy([], ['sort_no' => 'ASC']);
        foreach ($Faqs as $Faq) {
            $Faq->setSortNo($sortNo++);
        }

        try {
            $this->faqRepository->delete($targetFaq);
            $this->entityManager->flush();

            // $event = new EventArgs(
            //     [
            //         'Faq' => $targetFaq,
            //     ],
            //     $request
            // );
            // $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_SETTING_SHOP_PAYMENT_DELETE_COMPLETE);

            $this->addSuccess('admin.common.delete_complete', 'admin');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->entityManager->rollback();

            $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $targetFaq->getId()]);
            $this->addError($message, 'admin');
        }

        return $this->redirectToRoute('admin_faq_manager_faq');
    }

    /**
     * @Route("/%eccube_admin_route%/faq_manager/{id}/visible", requirements={"id" = "\d+"}, name="admin_faq_manager_visible", methods={"PUT"})
     */
    public function visible(Faq $Faq)
    {
        $this->isTokenValid();

        $Faq->setVisible(!$Faq->isVisible());

        $this->entityManager->flush();

        $question = $Faq->getQuestion();
        $id = $Faq->getId();

        if (mb_strlen($question) > 30) {
            $question = mb_substr($question, 0, 30) . '...';
        }

        if ($Faq->isVisible()) {
            $this->addSuccess(
                trans('faq_manager.admin.to_show_message', [
                    '%id%' => $id,
                    '%question%' => $question,
                ]),
                'admin'
            );
        } else {
            $this->addSuccess(
                trans('faq_manager.admin.to_hide_message', [
                    '%id%' => $id,
                    '%question%' => $question,
                ]),
                'admin'
            );
        }

        return $this->redirectToRoute('admin_faq_manager_faq');
    }

    /**
     * @Route("/%eccube_admin_route%/faq_manager/sort_no/move", name="admin_faq_manager_sort_no_move", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function moveSortNo(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        if ($this->isTokenValid()) {
            $sortNos = $request->request->all();
            foreach ($sortNos as $faqId => $sortNo) {
                /** @var Faq $Faq */
                $Faq = $this->faqRepository
                    ->find($faqId);
                $Faq->setSortNo($sortNo);
                $this->entityManager->persist($Faq);
            }
            $this->entityManager->flush();

            return new Response();
        }

        throw new BadRequestHttpException();
    }
}
