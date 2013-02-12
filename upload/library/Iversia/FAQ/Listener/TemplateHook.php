<?php

class Iversia_FAQ_Listener_TemplateHook {

	/**
	 * templateHook function.
	 *
	 * @access public
	 * @static
	 * @param mixed $hookName
	 * @param mixed &$contents
	 * @param array $hookParams
	 * @param XenForo_Template_Abstract $template
	 * @return void
	 */
	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		switch($hookName)
		{
			// Add to Help navTab
			case 'navigation_tabs_help':
			{
				$contents = '<li><a href="'. XenForo_Link::buildPublicLink('faq') .'">'. new XenForo_Phrase('iversia_faq') .'</a></li>' . $contents;
				break;
			}

			// Add to Help sidebar links
			case 'help_sidebar_links':
			{
				$contents = '<li><a href="'. XenForo_Link::buildPublicLink('faq') .'" class="primaryContent">'. new XenForo_Phrase('iversia_faq') .'</a></li>' . $contents;
				break;
			}
			case 'account_alerts_extra':
			{
				$alertOptOuts = array('alertOptOuts' => $template->getParam('alertOptOuts'));
				$contents .= $template->create('iversia_faq_alert_preferences', $alertOptOuts);
				break;
			}
		}
	}

	/**
	 * templateCreate function.
	 *
	 * @access public
	 * @static
	 * @param mixed &$templateName
	 * @param mixed &$params
	 * @param mixed $template
	 * @return void
	 */
	public static function templateCreate(&$templateName, &$params, $template)
	{
		switch($templateName)
		{
			case 'account_alert_preferences':
				$template->preloadTemplate('iversia_faq_alert_preferences');
			break;
		}

	}

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

		$faqCatModel = XenForo_Model::create('Iversia_FAQ_Model_Category');

		$faqLinks['canManageFAQ']     = XenForo_Permission::hasPermission($visitor['permissions'], 'FAQ_Manager_Permissions', 'manageFAQ');
		$faqLinks['canManageCats']    = $faqCatModel->canManageCategories();
		$faqLinks['faqCats']          = $faqCatModel->getAll(XenForo_Application::get('options')->faqCatNavCount);

		$extraTabs['faq'] = array(
			'title'			=> new XenForo_Phrase('iversia_faq'),
			'href'			=> XenForo_Link::buildPublicLink('full:faq'),
			'position'		=> 'end',
			'selected'		=> ($selected == 'faq'),
			'linksTemplate' => 'iversia_faq_navtabs',
			'faqPerm' 		=> $faqLinks
		);
	}
}