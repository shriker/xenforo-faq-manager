<?php

class Iversia_FAQ_Model_Category extends XenForo_Model
{
    public function getAll($limit = 0)
    {
        if ($limit != 0) {
            return $this->fetchAllKeyed('SELECT * FROM xf_faq_category ORDER BY display_order ASC, title ASC LIMIT ?', 'category_id', $limit);
        } else {
            return $this->fetchAllKeyed('SELECT * FROM xf_faq_category ORDER BY display_order ASC, title ASC', 'category_id');
        }
    }

    public function getById($category_id)
    {
        return $this->_getDb()->fetchRow('SELECT * FROM xf_faq_category WHERE category_id = ?', $category_id);
    }

    public function canManageCategories()
    {
        $visitor = XenForo_Visitor::getInstance();

        if ($visitor->hasPermission('FAQ_Manager_Permissions', 'manageFAQCategories')) {
            return true;
        }

        return false;
    }
}
