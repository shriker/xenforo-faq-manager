<?php

class Iversia_FAQ_Listener_TemplateHook
{
    /**
     * navTabs function.
     *
     * @static
     *
     * @param array &$extraTabs
     * @param mixed $selected
     *
     * @return void
     */
    public static function navTabs(array &$extraTabs, $selected)
    {
        $tabPosition = XenForo_Application::get('options')->faqNavTab;

        if (isset($tabPosition['type']) and $tabPosition['type'] != 'default') {
            $visitor = XenForo_Visitor::getInstance();
            $faqCatModel = XenForo_Model::create('Iversia_FAQ_Model_Category');

            // Look for cached categories
            $faqLinks = [];
            $faqLinks['faqCats'] = XenForo_Application::getSimpleCacheData('faq_categories');

            if (!isset($faqLinks['faqCats']) || !$faqLinks['faqCats']) {
                // Create cache
                $categories = $faqCatModel->getAll(XenForo_Application::get('options')->faqCatNavCount);
                $faqLinks['faqCats'] = XenForo_Application::setSimpleCacheData('faq_categories', $categories);
            }

            $faqLinks['canManageFAQ'] = XenForo_Permission::hasPermission($visitor['permissions'], 'FAQ_Manager_Permissions', 'manageFAQ');
            $faqLinks['canManageCats'] = $faqCatModel->canManageCategories();
            $faqLinks['canAskQuestions'] = XenForo_Permission::hasPermission($visitor['permissions'], 'FAQ_Manager_Permissions', 'canAskQuestions');

            $extraTabs['faq'] = [
                'title'            => new XenForo_Phrase('iversia_faq'),
                'href'             => XenForo_Link::buildPublicLink('full:faq'),
                'position'         => $tabPosition['type'],
                'selected'         => ($selected == 'faq'),
                'linksTemplate'    => 'iversia_faq_navtabs',
                'faqPerm'          => $faqLinks,
            ];
        }
    }
}
