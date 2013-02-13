<?php

class Iversia_FAQ_Installer
{
	private static $instance;

	protected $db;

	public static function getInstance()
	{
		if (!self::$instance)
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	public static function install($existingAddOn, $addOnData)
	{
		if (XenForo_Application::$versionId < 1010170)
		{
			throw new XenForo_Exception('This Add-On requires XenForo version 1.1.1 or higher.');
		}

		$version = is_array($existingAddOn) ? $existingAddOn['version_id'] : 0;

		$db = XenForo_Application::get('db');

		// This is the very first installation
		if ( ! $existingAddOn)
		{
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

			// Insert default data
			$db->query("
				REPLACE INTO `xf_faq_category`
					(`title`, `display_order`)
				VALUES
					('General', 0);
			");

			// New content type
				$db->query("
					INSERT INTO xf_content_type
						(content_type, addon_id, fields)
					VALUES
						('xf_faq_question', 'iversiaFAQ', '');
				");

			// Insert content type handlers
			$db->query("
				INSERT INTO xf_content_type_field
					(content_type, field_name, field_value)
				VALUES
					('xf_faq_question', 'alert_handler_class', 'Iversia_FAQ_AlertHandler_Question'),
					('xf_faq_question', 'like_handler_class', 'Iversia_FAQ_LikeHandler_Question');
			");

			XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();

		}
		// We are doing an upgrade! =D
		else
		{
			// Version 1.0.1
			if($version < 101)
			{
				// Default serialized for like_users
				$db->query("UPDATE xf_faq_question SET like_users='a:0:{}';");

				// New content type
				$db->query("
					INSERT INTO xf_content_type
						(content_type, addon_id, fields)
					VALUES
						('xf_faq_question', 'iversiaFAQ', '');
				");

				// New content type handlers
				$db->query("
					INSERT INTO xf_content_type_field
						(content_type, field_name, field_value)
					VALUES
						('xf_faq_question', 'alert_handler_class', 'Iversia_FAQ_AlertHandler_Question'),
						('xf_faq_question', 'like_handler_class', 'Iversia_FAQ_LikeHandler_Question');
				");

				XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
			}

		}

		unset($db);

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

		$faqIds = $db->fetchAll("SELECT faq_id FROM xf_faq_question");
		XenForo_Model::create('XenForo_Model_Alert')->deleteAlerts('xf_faq_question', $faqIds);
		XenForo_Model::create('XenForo_Model_Like')->deleteContentLikes('xf_faq_question', $faqIds);

		// Delete questions and categories
		$db->query('
			DROP TABLE IF EXISTS
			`xf_faq_question`,
			`xf_faq_category`;
		');

		// Remove content type
		$db->query("
			DELETE FROM xf_content_type
			WHERE content_type IN ('xf_faq_question');
		");

		// Remove content type fields
		$db->query("
			DELETE FROM xf_content_type_field
			WHERE content_type IN ('xf_faq_question');
		");

		// Bye caches!
		XenForo_Model::create('XenForo_Model_DataRegistry')->delete('faqCache');
		XenForo_Model::create('XenForo_Model_DataRegistry')->delete('faqStats');
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();

		unset($db);
	}
}