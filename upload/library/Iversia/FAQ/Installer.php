<?php

class Iversia_FAQ_Installer
{
	public static function install($existingAddOn, $addOnData)
	{
		if (XenForo_Application::$versionId < 1010170)
		{
			throw new XenForo_Exception('This Add-On requires XenForo version 1.1.1 or higher.');
		}

		// This is the very first installation
		if ( ! $existingAddOn)
		{
			$db = XenForo_Application::get('db');

			$db->query("
				CREATE TABLE IF NOT EXISTS `xf_faq_question` (
					`faq_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`category_id` int(10) unsigned NOT NULL DEFAULT '0',
					`moderation` tinyint(1) unsigned NOT NULL DEFAULT '0',
					`user_id` int(10) unsigned NOT NULL DEFAULT '0',
					`question` varchar(150) NOT NULL,
					`answer` text NOT NULL,
					`submit_date` int(10) unsigned NOT NULL DEFAULT '0',
					`answer_date` int(10) unsigned NOT NULL DEFAULT '0',
					`view_count` int(10) unsigned NOT NULL DEFAULT '0',
					`likes` int(10) unsigned NOT NULL,
					`like_users` blob NOT NULL,
					FULLTEXT(answer),
					PRIMARY KEY (`faq_id`),
					KEY `user_id` (`user_id`),
					KEY `category_id` (`category_id`),
					KEY `view_count` (`view_count`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8
			");

			$db->query("
				CREATE TABLE IF NOT EXISTS `xf_faq_category` (
					`category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`title` varchar(120) NOT NULL,
					`display_order` int(10) unsigned NOT NULL DEFAULT '1',
					PRIMARY KEY (`category_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8
			");

			$db->query("
				REPLACE INTO `xf_faq_category` (`title`, `display_order`) VALUES
			('General', 0);
			");
		}

		return TRUE;
	}

	/**
	 * Go home FAQ, you're drunk.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function uninstall()
	{
		$db = XenForo_Application::get('db');

		$db->query("DROP TABLE IF EXISTS xf_faq_question");
		$db->query("DROP TABLE IF EXISTS xf_faq_category");

		// Bye caches!
		XenForo_Model::create('XenForo_Model_DataRegistry')->delete('faqCache');
		XenForo_Model::create('XenForo_Model_DataRegistry')->delete('faqStats');
	}
}