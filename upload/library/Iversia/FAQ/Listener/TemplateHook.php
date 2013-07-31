<?php

class Iversia_FAQ_Listener_TemplateHook
{
	/**
	 * navTabs function.
	 *
	 * @access public
	 * @static
	 * @param array &$extraTabs
	 * @param mixed $selected
	 * @return void
	 */
	public static function navTabs(array &$extraTabs, $selected)
	{
		$visitor = XenForo_Visitor::getInstance();

        /*

		$faqCatModel = XenForo_Model::create('Iversia_FAQ_Model_Category');

		$faqLinks['canManageFAQ']     = XenForo_Permission::hasPermission($visitor['permissions'], 'FAQ_Manager_Permissions', 'manageFAQ');
		$faqLinks['canManageCats']    = $faqCatModel->canManageCategories();
		$faqLinks['faqCats']          = $faqCatModel->getAll(XenForo_Application::get('options')->faqCatNavCount);

		$extraTabs['faq'] = array(
			'title'			=> new XenForo_Phrase('iversia_faq'),
			'href'			=> XenForo_Link::buildPublicLink('full:faq'),
			'position'		=> 'end',
			'selected'		=> ($selected == 'faq'),
			//'linksTemplate' => 'iversia_faq_navtabs',
			//'faqPerm' 		=> $faqLinks
		);
        */
	}
}
