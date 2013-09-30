<?php

class Iversia_FAQ_DataWriter_Question extends XenForo_DataWriter
{
    protected function _getFields()
    {
        return array(
            'xf_faq_question'    => array(
                'faq_id'        => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
                'category_id'   => array('type' => self::TYPE_UINT, 'default' => array('xf_faq_category', 'category_id'), 'required' => true),

                'moderation'    => array('type' => self::TYPE_UINT, 'required' => true),
                'sticky'        => array('type' => self::TYPE_UINT, 'required' => false, 'default' => 0),

                'user_id'       => array('type' => self::TYPE_UINT,     'required' => true),

                'view_count'    => array('type' => self::TYPE_UINT,     'required' => false),
                'likes'         => array('type' => self::TYPE_UINT,     'required' => false, 'default' => 0),
                'like_users'    => array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}'),

                'question'      => array('type' => self::TYPE_STRING,   'required' => true, 'maxLength' => 150),
                'answer'        => array('type' => self::TYPE_STRING,   'required' => false),

                // Times
                'submit_date'   => array('type' => self::TYPE_UINT, 'required' => true, 'default' => XenForo_Application::$time),
                'answer_date'   => array('type' => self::TYPE_UINT, 'required' => false, 'default' => XenForo_Application::$time),
            )
        );
    }

    protected function _getExistingData($data)
    {
        if ( ! $id = $this->_getExistingPrimaryKey($data, 'faq_id')) {
            return false;
        }

        return array('xf_faq_question' => $this->getModelFromCache('Iversia_FAQ_Model_Question')->getById($id));
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'faq_id = ' . $this->_db->quote($this->getExisting('faq_id'));
    }

    protected function _postSave()
    {
        $this->_indexForSearch();
    }

    protected function _postDelete()
    {
        $this->_deleteFromSearchIndex();
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
}
