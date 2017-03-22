<?php

class Iversia_FAQ_SitemapHandler_Question extends XenForo_SitemapHandler_Abstract
{
    protected $_questionModel;

    public function getPhraseKey($key)
    {
        return 'iversia_faq_questions';
    }

    public function getRecords($previousLast, $limit, array $viewingUser)
    {
        $questionModel = $this->_questionModel();

        $ids = $questionModel->getFaqIdsInRange($previousLast, $limit);
        $questions = $questionModel->getQuestionsByIds($ids);

        ksort($questions);

        return $questions;
    }

    public function getData(array $entry)
    {
        $entry['title'] = XenForo_Helper_String::censorString($entry['question']);

        return [
            'loc'     => XenForo_Link::buildPublicLink('canonical:faq', $entry),
            'lastmod' => $entry['answer_date'],
        ];
    }

    public function isIncluded(array $entry, array $viewingUser)
    {
        return true;
    }

    public function isInterruptable()
    {
        return true;
    }

    protected function _questionModel()
    {
        if (!$this->_questionModel) {
            $this->_questionModel = XenForo_Model::create('Iversia_FAQ_Model_Question');
        }

        return $this->_questionModel;
    }
}
