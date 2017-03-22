<?php

class Iversia_FAQ_AttachmentHandler_Question extends XenForo_AttachmentHandler_Abstract
{
    protected $_contentIdKey = 'faq_id';
    protected $_contentRoute = 'faq';
    protected $_contentTypePhraseKey = 'xf_faq_question';

    protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
    {
        return $viewingUser['user_id']
            && XenForo_Permission::hasPermission($viewingUser['permissions'], 'FAQ_Manager_Permissions', 'uploadFAQAttach');
    }

    // Everyone can view attachments on FAQ entries
    protected function _canViewAttachment(array $attachment, array $viewingUser)
    {
        return true;
    }

    // What to do after an attachment is deleted
    public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db)
    {
        $db->query('
            UPDATE xf_faq_question
            SET attach_count = IF(attach_count > 0, attach_count - 1, 0)
            WHERE faq_id = ?
        ', $attachment['content_id']);

        return true;
    }

    protected function _getQuestionModel()
    {
        return XenForo_Model::create('Iversia_FAQ_Model_Question');
    }
}
