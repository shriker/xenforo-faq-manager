<?php

class Iversia_FAQ_CronEntry_FAQ
{
    public static function runQuestionCache()
    {
        $faqCache = XenForo_Model::create('XenForo_Model_DataRegistry');
        $faqModel = XenForo_Model::create('Iversia_FAQ_Model_Question');

        $questions = $faqModel->getAll();

        if (!$questions) {
            return;
        }

        $faqEntry = [];

        foreach ($questions as $question) {
            $faqEntry[$question['faq_id']] = $question['question'];
            $faqCache->set('faqCache', $faqEntry);
        }
    }

    public static function runStatsCache()
    {
        $faqStats = XenForo_Model::create('XenForo_Model_DataRegistry');
        $faqModel = XenForo_Model::create('Iversia_FAQ_Model_Question');

        $viewCount = $faqModel->getViewTotal();
        $questionCount = $faqModel->getTotal();

        $faqStats->set('faqStats', ['views' => $viewCount, 'questions' => $questionCount]);
    }
}
