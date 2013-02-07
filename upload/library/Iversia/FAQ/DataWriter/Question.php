<?php

class Iversia_FAQ_DataWriter_Question extends XenForo_DataWriter
{
	protected function _getFields()
	{
		return array(
			'xf_faq_question' => array(
				'faq_id'			=> array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'category_id'		=> array('type' => self::TYPE_UINT, 	'required' => false, 'default' => 0),

				'moderation'		=> array('type' => self::TYPE_UINT, 'required' => true),
				'user_id'			=> array('type' => self::TYPE_UINT, 	'required' => true),

				'view_count'		=> array('type' => self::TYPE_UINT, 	'required' => false),
				'likes'				=> array('type' => self::TYPE_UINT, 	'required' => false, 'default' => 0),
				'like_users'		=> array('required' => false, 'default' => ''),

				'question'			=> array('type' => self::TYPE_STRING, 	'required' => true, 'maxLength' => 150),
				'answer'      		=> array('type' => self::TYPE_STRING, 	'required' => false),

				// Times
				'submit_date' 		=> array('type' => self::TYPE_UINT, 'required' => true, 'default' => XenForo_Application::$time),
				'answer_date' 		=> array('type' => self::TYPE_UINT, 'required' => false, 'default' => XenForo_Application::$time),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if ( ! $id = $this->_getExistingPrimaryKey($data, 'faq_id'))
		{
			return false;
		}

		return array('xf_faq_question' => $this->getModelFromCache('Iversia_FAQ_Model_Question')->getById($id));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'faq_id = ' . $this->_db->quote($this->getExisting('faq_id'));
	}
}