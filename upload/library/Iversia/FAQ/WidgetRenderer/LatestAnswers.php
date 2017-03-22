<?php

class Iversia_FAQ_WidgetRenderer_LatestAnswers extends WidgetFramework_WidgetRenderer
{
    protected function _getConfiguration()
    {
        return [
            'name'           => new XenForo_Phrase('wf_faq_latest_answers'),
            'options'        => [
                'limit'         => XenForo_Input::UINT,
            ],
            'useCache'       => true,
            'cacheSeconds'   => 3600,
        ];
    }

    protected function _getOptionsTemplate()
    {
        return 'faq_widget_limit_questions';
    }

    protected function _validateOptionValue($optionKey, &$optionValue)
    {
        if ('limit' == $optionKey) {
            if (empty($optionValue)) {
                $optionValue = 5;
            }
        }

        return true;
    }

    protected function _getRenderTemplate(array $widget, $positionCode, array $params)
    {
        return 'faq_widget_latest_answers';
    }

    protected function _render(array $widget, $positionCode, array $params, XenForo_Template_Abstract $template)
    {
        $questions = [];
        $core = WidgetFramework_Core::getInstance();
        $questionModel = $core->getModelFromCache('Iversia_FAQ_Model_Question');
        $questions = $questionModel->getLatest($widget['options']['limit']);

        $template->setParam('questions', $questions);

        return $template->render();
    }
}
