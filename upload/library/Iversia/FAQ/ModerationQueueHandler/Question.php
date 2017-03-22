<?php

class Iversia_FAQ_ModerationQueueHandler_Question extends XenForo_ModerationQueueHandler_Abstract
{
    public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
    {
        $questionModel = $this->_getQuestionModel();
        $questions = $questionModel->getQuestionsByIds($contentIds);

        $output = [];

        foreach ($questions as $question) {
            $output[$question['faq_id']] = [
                'message' => $question['answer'],
                'user'    => [
                    'user_id'  => $question['user_id'],
                    'username' => $question['username'],
                ],
                'title'            => $question['question'],
                'link'             => XenForo_Link::buildPublicLink('faq', $question),
                'contentTypeTitle' => new XenForo_Phrase('iversia_faq'),
            ];
        }

        return $output;
    }

    public function approveModerationQueueEntry($contentId, $message, $title)
    {
        $queueModel = XenForo_Model::create('XenForo_Model_ModerationQueue');
        $questionModel = $this->_getQuestionModel();

        $question = $questionModel->getById($contentId);

        if ($question) {
            $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
            $dw->setExistingData($question['faq_id']);
            $dw->set('moderation', 0);
            $dw->set('answer', $message);
            $dw->set('answer_date', XenForo_Application::$time);
            $dw->save();

            // Notify user that their question has been answered
            $visitor = XenForo_Visitor::getInstance();
            $alertModel = XenForo_Model::create('XenForo_Model_Alert');
            $alertModel->alert($question['user_id'], $visitor['user_id'], $visitor['username'], 'xf_faq_question', $contentId, 'answered', $question);
        }

        // Remove from queue
        return $queueModel->deleteFromModerationQueue('xf_faq_question', $contentId);
    }

    public function deleteModerationQueueEntry($contentId)
    {
        $queueModel = XenForo_Model::create('XenForo_Model_ModerationQueue');
        $questionModel = $this->_getQuestionModel();

        $question = $questionModel->getById($contentId, $moderation = 0);

        if ($question) {
            // Delete question
            $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
            $dw->setExistingData($question['faq_id']);
            $dw->delete();
        }

        // Remove from queue
        return $queueModel->deleteFromModerationQueue('xf_faq_question', $contentId);
    }

    protected function _getQuestionModel()
    {
        return XenForo_Model::create('Iversia_FAQ_Model_Question');
    }
}
