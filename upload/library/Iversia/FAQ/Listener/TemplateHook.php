<?php

class Iversia_FAQ_Listener_TemplateHook {

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
		}
	}

	public static function navTabs(array &$extraTabs, $selected)
	{
		$visitor = XenForo_Visitor::getInstance();

		$faqLinks['canManageFAQ'] = XenForo_Permission::hasPermission($visitor['permissions'], 'FAQ_Manager_Permissions', 'manageFAQ');
		$faqLinks['canManageCats'] = XenForo_Permission::hasPermission($visitor['permissions'], 'FAQ_Manager_Permissions', 'manageFAQCategories');

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