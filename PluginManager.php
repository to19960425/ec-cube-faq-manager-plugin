<?php

namespace Plugin\FaqManager;

use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Layout;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Repository\PageRepository;
use Psr\Container\ContainerInterface;

class PluginManager extends AbstractPluginManager
{
    private array $pages = [
        [
            'name'     => 'FAQ',
            'url'      => 'faq_manager_index',                       // ルート名と合わせる
            'filename' => 'FaqManager/Resource/template/default/faq/index', // Twig パス
        ],
    ];

    public function enable(array $meta, ContainerInterface $c)
    {
        $em = $c->get('doctrine')->getManager();
        foreach ($this->pages as $p) {
            $this->createPage($em, $p['name'], $p['url'], $p['filename']);
        }
        $this->copyTwigFiles($c);        // Twig をテーマ側へコピー（任意）
    }

    /**
     * レイアウト編集を残したまま “無効化” したいなら
     * disable() ではページを削除しない、という選択肢もあります。
     * 完全に消したい場合は removePage() を呼び出す。
     */
    public function disable(array $meta, ContainerInterface $c)
    {
        // ここでは何もしない ⇒ ページは残るがコントローラが動かない
    }

    public function uninstall(array $meta, ContainerInterface $c)
    {
        $em = $c->get('doctrine')->getManager();
        foreach ($this->pages as $p) {
            $this->removePage($em, $p['url']);
        }
        $this->removeTwigFiles($c);
    }

    protected function createPage(EntityManagerInterface $em, string $name, string $url, string $filename): void
    {
        // 既存 URL があるか確認（プラグイン再有効化時に重複を防ぐ）
        if ($em->getRepository(Page::class)->findOneBy(['url' => $url])) {
            return;
        }

        $Page = (new Page())
            ->setEditType(Page::EDIT_TYPE_DEFAULT)
            ->setName($name)
            ->setUrl($url)
            ->setFileName($filename);

        $em->persist($Page);
        $em->flush();                        // ← ID が発番される

        // 下層ページ用レイアウト（定数 = 2）に紐付け
        $Layout = $em->find(
            Layout::class,
            Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE   // = 2  :contentReference[oaicite:0]{index=0}
        );

        $PageLayout = (new PageLayout())
            ->setPage($Page)
            ->setPageId($Page->getId())
            ->setLayout($Layout)
            ->setLayoutId($Layout->getId())
            ->setSortNo(0);

        $em->persist($PageLayout);
        $em->flush();
    }

    protected function removePage(EntityManagerInterface $em, string $url): void
    {
        $Page = $em->getRepository(Page::class)->findOneBy(['url' => $url]);
        if (!$Page) {
            return;    // 既に削除済み
        }

        // ページ―レイアウトの紐付けを先に削除
        foreach ($Page->getPageLayouts() as $PageLayout) {
            $em->remove($PageLayout);
        }
        $em->flush();

        $em->remove($Page);
        $em->flush();
    }

    private function copyTwigFiles(ContainerInterface $container)
    {
        $themeDir = $container->get(EccubeConfig::class)
            ->get('eccube_theme_front_dir') . '/FaqManager/Resource/template/default';
        $fs = new Filesystem();
        if (!$fs->exists($themeDir)) {
            $fs->mirror(__DIR__ . '/Resource/template/default', $themeDir);
        }
    }

    private function removeTwigFiles(ContainerInterface $container)
    {
        $themeDir = $container->get(EccubeConfig::class)
            ->get('eccube_theme_front_dir') . '/FaqManager/Resource/template/default';
        (new Filesystem())->remove($themeDir);
    }
}
