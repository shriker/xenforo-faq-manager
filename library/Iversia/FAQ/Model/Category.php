<?php

class Iversia_FAQ_Model_Category extends XenForo_Model
{
	public function getAll()
	{
		return $this->fetchAllKeyed("SELECT * FROM xf_faq_category ORDER BY display_order DESC, title ASC", 'category_id');
	}

	public function getById($category_id)
	{
		return $this->_getDb()->fetchRow('SELECT * FROM xf_faq_category WHERE category_id = ?', $category_id);
	}

	public function canManageCategories()
	{
		$visitor = XenForo_Visitor::getInstance();

		if ($visitor->hasPermission('FAQ_Manager_Permissions', 'manageFAQCategories'))
		{
		    return TRUE;
		}

		return FALSE;
	}
}