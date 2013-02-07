<?php

class Iversia_FAQ_DataWriter_Category extends XenForo_DataWriter
{
	protected function _getFields()
	{
		return array(
			'xf_faq_category' => array(
				'category_id'	=> array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'title'			=> array('type' => self::TYPE_STRING,'required' => true, 'maxLength' => 120),
				'display_order'	=> array('type' => self::TYPE_UINT, 'required' => true, 'default' => 0)
			)
		);
	}

	protected function _getExistingData($data)
	{
		if ( ! $id = $this->_getExistingPrimaryKey($data, 'category_id'))
		{
			return false;
		}

		return array('xf_faq_category' => $this->getModelFromCache('Iversia_FAQ_Model_Category')->getById($id));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'category_id = ' . $this->_db->quote($this->getExisting('category_id'));
	}
}