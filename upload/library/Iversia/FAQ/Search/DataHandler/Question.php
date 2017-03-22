<?php

class Iversia_FAQ_Search_DataHandler_Question extends XenForo_Search_DataHandler_Abstract
{
    private $questionModel;

    protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList)
    {
        $ids = [];

        foreach ($dataList as $data) {
            $ids[] = $data['faq_id'];
        }

        $indexer->deleteFromIndex('xf_faq_question', $ids);
    }

    protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
    {
        $indexer->insertIntoIndex(
            'xf_faq_question',
            $data['faq_id'],
            $data['question'],
            $data['answer'],
            $data['answer_date'],
            $data['user_id']
        );
    }

    protected function _updateIndex(XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates)
    {
        $indexer->updateIndex('xf_faq_question', $data['faq_id'], $fieldUpdates);
    }

    public function canViewResult(array $result, array $viewingUser)
    {
        return true;
    }

    public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
    {
        $questions = $this->getQuestionModel()->getQuestionsByIds($ids);

        return $questions;
    }

    public function getResultDate(array $result)
    {
        return $result['answer_date'];
    }

    public function getSearchContentTypes()
    {
        return ['xf_faq_question'];
    }

    public function getSearchFormControllerResponse(XenForo_ControllerPublic_Abstract $controller, XenForo_Input $input, array $viewParams)
    {
        return $controller->responseView('Iversia_FAQ_ViewPublic_Search_Form_Question', 'search_form_question', $viewParams);
    }

    public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize)
    {
        $ids = $this->getQuestionModel()->getFaqIdsInRange($lastId, $batchSize);

        if (!$ids) {
            return false;
        }

        $this->quickIndex($indexer, $ids);

        return max($ids);
    }

    public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
    {
        $pages = $this->getQuestionModel()->getQuestionsByIds($contentIds);

        foreach ($pages as $page) {
            $this->insertIntoIndex($indexer, $page);
        }

        return true;
    }

    public function renderResult(XenForo_View $view, array $result, array $search)
    {
        return $view->createTemplateObject('xf_faq_question_search_result', [
            'question'  => $result,
            'search'    => $search,
        ]);
    }

    private function getQuestionModel()
    {
        if (!$this->questionModel) {
            $this->questionModel = XenForo_Model::create('Iversia_FAQ_Model_Question');
        }

        return $this->questionModel;
    }
}
