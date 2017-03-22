<?php
/**
 * Iversia_FAQ_BbCode_Formatter_Base class.
 */
class Iversia_FAQ_BbCode_Formatter_Base
{
    public static function renderTagFAQ(array $tag, array $rendererStates, XenForo_BbCode_Formatter_Base $formatter)
    {
        $question_id = $tag['option'];

        if (isset($question_id)) {

            // Get questions from the cache
            $questions = XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqCache');
            $question = $questions[$question_id];

            if ($question) {
                $faqData = [
                    'faqLink'   => XenForo_Link::buildPublicLink('faq', ['question' => $question, 'faq_id' => $question_id]),
                    'faq_id'    => $question_id,
                    'question'  => $question,
                ];

                $view = $formatter->getView();

                if ($view) {
                    $template = $view->createTemplateObject('iversia_faq_bbcode', $faqData);

                    return $template->render();
                } else {
                    return '<b>'.new XenForo_Phrase('iversia_faq').' #'.$tag['option'].':</b>
                    <a href="'.XenForo_Link::buildPublicLink('faq', ['question' => $question, 'faq_id' => $question_id]).'">'.htmlentities($question).'</a>';
                }
            } else {
                return '<b>'.new XenForo_Phrase('iversia_faq').'</b>: '.new XenForo_Phrase('iversia_faq_not_found').'';
            }
        }
    }
}
