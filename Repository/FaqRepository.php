<?php

namespace Plugin\FaqManager\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Eccube\Repository\AbstractRepository;
use Plugin\FaqManager\Entity\Faq;

class FaqRepository extends AbstractRepository
{
    /**
     * FaqRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    /**
     * @param  Faq $entity
     */
    public function save($entity)
    {
        if (!$entity->getId()) {
            $sortNoTop = $this->findOneBy([], ['sort_no' => 'DESC']);
            $sort_no = 0;
            if (!is_null($sortNoTop)) {
                $sort_no = $sortNoTop->getSortNo();
            }
            $entity->setSortNo($sort_no + 1);
        }

        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * @param  Faq $entity
     */
    public function delete($entity)
    {
        $em = $this->getEntityManager();
        $em->beginTransaction();

        try {
            $this
                ->createQueryBuilder('c')
                ->update()
                ->set('c.sort_no', 'c.sort_no - 1')
                ->where('c.sort_no > :sort_no')
                ->setParameter('sort_no', $entity->getSortNo())
                ->getQuery()
                ->execute();

            // カテゴリを削除
            $em->remove($entity);
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }
    }
}
