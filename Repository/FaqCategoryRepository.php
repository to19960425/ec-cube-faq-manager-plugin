<?php

namespace Plugin\FaqManager\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Eccube\Repository\AbstractRepository;
use Plugin\FaqManager\Entity\FaqCategory;

class FaqCategoryRepository extends AbstractRepository
{
    /**
     * FaqCategoryRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqCategory::class);
    }

    /**
     * 有効なカテゴリを取得します（sort_no 昇順）
     *
     * @return Plugin\FaqManager\Entity\FaqCategory[]
     */
    public function findEnabled(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('c.sort_no', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 全カテゴリを sort_no 昇順で取得します
     *
     * @return Plugin\FaqManager\Entity\FaqCategory[]
     */
    public function findAllSorted(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.sort_no', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 名前でカテゴリを検索します（部分一致）
     *
     * @param string $name
     * @return Plugin\FaqManager\Entity\FaqCategory[]
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('c.sort_no', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /**
     * カテゴリを保存する.
     *
     * @param  FaqCategory $entity
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
     * カテゴリを削除する.
     *
     * @param  FaqCategory $entity
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
