<?php

class Iversia_FAQ_DataWriter_Question extends XenForo_DataWriter
{
    protected $_question = null;

    const DATA_ATTACHMENT_HASH = 'attachmentHash';

    protected function _getFields()
    {
        return [
            'xf_faq_question'    => [
                'faq_id'        => ['type' => self::TYPE_UINT, 'autoIncrement' => true],
                'category_id'   => ['type' => self::TYPE_UINT, 'default' => ['xf_faq_category', 'category_id'], 'required' => true],

                'moderation'       => ['type' => self::TYPE_UINT, 'required' => true],
                'sticky'           => ['type' => self::TYPE_UINT, 'required' => false, 'default' => 0],
                'display_order'    => ['type' => self::TYPE_UINT, 'required' => true, 'default' => 0],

                'user_id'       => ['type' => self::TYPE_UINT,     'required' => true],

                'attach_count'  => ['type' => self::TYPE_UINT,     'required' => false, 'default' => 0],
                'view_count'    => ['type' => self::TYPE_UINT,     'required' => false],
                'likes'         => ['type' => self::TYPE_UINT,     'required' => false, 'default' => 0],
                'like_users'    => ['type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}'],

                'question'      => ['type' => self::TYPE_STRING,   'required' => true, 'maxLength' => 150],
                'answer'        => ['type' => self::TYPE_STRING,   'required' => false],

                // Times
                'submit_date'   => ['type' => self::TYPE_UINT, 'required' => true, 'default' => XenForo_Application::$time],
                'answer_date'   => ['type' => self::TYPE_UINT, 'required' => false, 'default' => XenForo_Application::$time],
            ],
        ];
    }

    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'faq_id')) {
            return false;
        }

        return ['xf_faq_question' => $this->getModelFromCache('Iversia_FAQ_Model_Question')->getById($id)];
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'faq_id = '.$this->_db->quote($this->getExisting('faq_id'));
    }

    protected function _postSave()
    {
        $this->_indexForSearch();

        $attachmentHash = $this->getExtraData(self::DATA_ATTACHMENT_HASH);
        if ($attachmentHash) {
            $this->_associateAttachments($attachmentHash);
        }
    }

    protected function _deleteAttachments()
    {
        $this->getModelFromCache('XenForo_Model_Attachment')->deleteAttachmentsFromContentIds(
            'xf_faq_question',
            [$this->get('faq_id')]
        );
    }

    protected function _postDelete()
    {
        $this->_deleteFromSearchIndex();
        $this->_deleteAttachments();
    }

    protected function _associateAttachments($attachmentHash)
    {
        $rows = $this->_db->update('xf_attachment', [
            'content_type' => 'xf_faq_question',
            'content_id'   => $this->get('faq_id'),
            'temp_hash'    => '',
            'unassociated' => 0,
        ], 'temp_hash = '.$this->_db->quote($attachmentHash));

        if ($rows) {
            $newAttachCount = $this->get('attach_count') + $rows;

            $this->set('attach_count', $newAttachCount, '', ['setAfterPreSave' => true]);
            $this->_db->update('xf_faq_question', [
                'attach_count' => $newAttachCount, ],
                'faq_id = '.$this->get('faq_id')
            );
        }
    }

    protected function _indexForSearch()
    {
        if ($this->isChanged('answer') or $this->isChanged('question')) {
            $this->_insertOrUpdateSearchIndex();
        }
    }

    protected function _insertOrUpdateSearchIndex()
    {
        $dataHandler = $this->_getSearchDataHandler();
        $indexer = new XenForo_Search_Indexer();

        $dataHandler->insertIntoIndex($indexer, $this->getMergedData());
    }

    protected function _deleteFromSearchIndex()
    {
        $dataHandler = $this->_getSearchDataHandler();
        $indexer = new XenForo_Search_Indexer();

        $dataHandler->deleteFromIndex($indexer, $this->getMergedData());
    }

    protected function _getSearchDataHandler()
    {
        // Gets the search-data handler for 'xf_faq_question' content type
        return $this->getModelFromCache('XenForo_Model_Search')->getSearchDataHandler('xf_faq_question');
    }

    public function setQuestion(array $question)
    {
        $this->_question = $question;
    }
}
