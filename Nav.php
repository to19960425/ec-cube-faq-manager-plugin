<?php

namespace Plugin\FaqManager;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {
        return [
            'FaqManager' => [
                'name' => 'faq_manager.admin.nav.001',
                'icon' => 'fa-question',
                'children' => [
                    'faq_manager_faq' => [
                        'id' => 'admin_faq_manager_faq',
                        'url' => 'admin_faq_manager_faq',
                        'name' => 'faq_manager.admin.nav.002',
                    ],
                    'faq_manager_new' => [
                        'id' => 'admin_faq_manager_new',
                        'url' => 'admin_faq_manager_new',
                        'name' => 'faq_manager.admin.nav.005',
                    ],
                    'faq_manager_category' => [
                        'id' => 'admin_faq_manager_category',
                        'url' => 'admin_faq_manager_category',
                        'name' => 'faq_manager.admin.nav.003',
                    ],
                ],
            ],
        ];
    }
}
