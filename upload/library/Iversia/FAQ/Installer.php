<?php

class Iversia_FAQ_Installer
{
    private static $instance;

    protected $db;

    public static function getInstance()
    {
        if (!self::$instance) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public static function install($existingAddOn, $addOnData)
    {
        if (XenForo_Application::$versionId < 1030032) {
            throw new XenForo_Exception('This add-on requires XenForo 1.3.0 or higher.', true);
        }

        $version = is_array($existingAddOn) ? $existingAddOn['version_id'] : 0;

        $db = XenForo_Application::get('db');

        // This is the very first installation
        if (!$existingAddOn) {
            $db->query(
                "CREATE TABLE IF NOT EXISTS `xf_faq_question` (
                    `faq_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `category_id` int(10) unsigned NOT NULL DEFAULT '0',
                    `moderation` tinyint(1) unsigned NOT NULL DEFAULT '0',
                    `sticky` tinyint(1) unsigned NOT NULL DEFAULT '0',
                    `display_order` int(10) unsigned NOT NULL DEFAULT '1',
                    `user_id` int(10) unsigned NOT NULL DEFAULT '0',
                    `question` varchar(150) NOT NULL,
                    `answer` text NOT NULL,
                    `submit_date` int(10) unsigned NOT NULL DEFAULT '0',
                    `answer_date` int(10) unsigned NOT NULL DEFAULT '0',
                    `attach_count` int(10) NOT NULL,
                    `view_count` int(10) unsigned NOT NULL DEFAULT '0',
                    `likes` int(10) unsigned NOT NULL,
                    `like_users` blob NOT NULL,
                    PRIMARY KEY (`faq_id`),
                    KEY `user_id` (`user_id`),
                    KEY `category_id` (`category_id`),
                    KEY `view_count` (`view_count`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );

            $db->query(
                "CREATE TABLE IF NOT EXISTS `xf_faq_category` (
                    `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `title` varchar(120) NOT NULL,
                    `display_order` int(10) unsigned NOT NULL DEFAULT '1',
                    `short_desc` varchar(255) NOT NULL,
                    `long_desc` text,
                    PRIMARY KEY (`category_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );

            // Insert default data
            $db->query(
                "INSERT INTO `xf_faq_category`
                    (`title`, `display_order`, `short_desc`, `long_desc`)
                VALUES
                    ('General', 0, 'This is a general category.', 'Feel free to edit this category.');"
            );

            // New content type
                $db->query(
                    "INSERT INTO xf_content_type
                        (content_type, addon_id, fields)
                    VALUES
                        ('xf_faq_question', 'iversiaFAQ', '');"
                );

            // Insert content type handlers
            $db->query(
                "INSERT INTO xf_content_type_field
                    (content_type, field_name, field_value)
                VALUES
                    ('xf_faq_question', 'sitemap_handler_class', 'Iversia_FAQ_SitemapHandler_Question'),
                    ('xf_faq_question', 'attachment_handler_class', 'Iversia_FAQ_AttachmentHandler_Question'),
                    ('xf_faq_question', 'moderation_queue_handler_class', 'Iversia_FAQ_ModerationQueueHandler_Question'),
                    ('xf_faq_question', 'search_handler_class', 'Iversia_FAQ_Search_DataHandler_Question'),
                    ('xf_faq_question', 'alert_handler_class', 'Iversia_FAQ_AlertHandler_Question'),
                    ('xf_faq_question', 'like_handler_class', 'Iversia_FAQ_LikeHandler_Question');"
            );

            XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
        } else {
            // Version 1.0.1
            if ($version < 101) {
                // Default serialized for like_users
                $db->query("UPDATE xf_faq_question SET like_users='a:0:{}';");

                // New content type
                $db->query(
                    "INSERT INTO xf_content_type
                        (content_type, addon_id, fields)
                    VALUES
                        ('xf_faq_question', 'iversiaFAQ', '');"
                );

                // New content type handlers
                $db->query(
                    "INSERT INTO xf_content_type_field
                        (content_type, field_name, field_value)
                    VALUES
                        ('xf_faq_question', 'alert_handler_class', 'Iversia_FAQ_AlertHandler_Question'),
                        ('xf_faq_question', 'like_handler_class', 'Iversia_FAQ_LikeHandler_Question');"
                );
            }

            if ($version < 210) {
                $db->query(
                    "INSERT INTO xf_content_type_field
                        (content_type, field_name, field_value)
                    VALUES
                        ('xf_faq_question', 'search_handler_class', 'Iversia_FAQ_Search_DataHandler_Question');"
                );

                $db->query("ALTER TABLE `xf_faq_question` ADD COLUMN `sticky` tinyint NOT NULL DEFAULT '0' AFTER `moderation`;");
            }

            if ($version < 220) {
                $db->query("ALTER TABLE `xf_faq_question` ADD COLUMN `display_order` int(10) unsigned NOT NULL DEFAULT '1' AFTER `moderation`;");
            }

            // Adding attachments
            if ($version < 250) {
                $db->query('ALTER TABLE `xf_faq_question` ADD COLUMN `attach_count` int(10) AFTER `answer_date`;');

                $db->query('ALTER TABLE `xf_faq_category` ADD COLUMN `short_desc` varchar(255) NOT NULL AFTER `display_order`, ADD COLUMN `long_desc` text NOT NULL AFTER `short_desc`;');

                $db->query(
                    "INSERT INTO xf_content_type_field
                        (content_type, field_name, field_value)
                    VALUES
                        ('xf_faq_question', 'attachment_handler_class', 'Iversia_FAQ_AttachmentHandler_Question'),
                        ('xf_faq_question', 'moderation_queue_handler_class', 'Iversia_FAQ_ModerationQueueHandler_Question');"
                );
            }

            if ($version < 310) {
                // Fix for upgrades
                $db->query("ALTER TABLE `xf_faq_question` CHANGE COLUMN `display_order` `display_order` int(10) UNSIGNED NOT NULL DEFAULT '1';");
            }

            // Adding sitemap handler
            if ($version < 320) {
                $db->query(
                    "INSERT INTO xf_content_type_field
                        (content_type, field_name, field_value)
                    VALUES
                        ('xf_faq_question', 'sitemap_handler_class', 'Iversia_FAQ_SitemapHandler_Question');"
                );
            }

            XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
        }

        unset($db);

        return true;
    }

    /**
     * Go home FAQ, you're drunk.
     *
     * @static
     *
     * @return void
     */
    public static function uninstall()
    {
        $db = XenForo_Application::get('db');

        $faqIds = $db->fetchAll('SELECT faq_id FROM xf_faq_question');
        XenForo_Model::create('XenForo_Model_Alert')->deleteAlerts('xf_faq_question', $faqIds);
        XenForo_Model::create('XenForo_Model_Like')->deleteContentLikes('xf_faq_question', $faqIds);

        // Unassociate FAQ Attachments
        // An hourly cron runs which will then prune unassociated and unused attachments
        $db->query('UPDATE xf_attachment set unassociated = 1 WHERE content_type = \'xf_faq_question\'');

        // Delete questions and categories
        $db->query('DROP TABLE IF EXISTS `xf_faq_question`, `xf_faq_category`;');

        // Remove content type
        $db->query("DELETE FROM xf_content_type WHERE content_type IN ('xf_faq_question');");

        // Remove content type fields
        $db->query("DELETE FROM xf_content_type_field WHERE content_type IN ('xf_faq_question');");

        // Delete from cache
        XenForo_Application::setSimpleCacheData('faq_categories', false);

        // Bye caches!
        XenForo_Model::create('XenForo_Model_DataRegistry')->delete('faqCache');
        XenForo_Model::create('XenForo_Model_DataRegistry')->delete('faqStats');
        XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();

        unset($db);
    }
}
