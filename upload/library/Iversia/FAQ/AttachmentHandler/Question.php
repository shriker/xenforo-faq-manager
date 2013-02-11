<?php

class Iversia_FAQ_AttachmentHandler_Question extends XenForo_AttachmentHandler_Abstract
{
	protected $_contentRoute           = 'faq';
	protected $_contentTypePhraseKey   = 'faq';
	protected $_contentIdKey           = 'faq_id';

	protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
	{
		return true;
	}

	public function _canViewAttachment(array $attachment, array $viewingUser)
	{
		return true;
	}

	public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db)
	{
		return true;
	}

	public function getAttachmentCountLimit()
	{
		return true;
	}
}