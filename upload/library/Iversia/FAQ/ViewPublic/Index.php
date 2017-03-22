<?php

class Iversia_FAQ_ViewPublic_Index extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', ['view' => $this]));
        $bbCodeOptions = [
            'states' => [
                'viewAttachments' => true,
            ],
            'contentType'  => 'xf_faq_question',
            'contentIdKey' => 'faq_id',
            'messageKey'   => 'answer',
        ];

        if (isset($this->_params['faq'])) {
            foreach ($this->_params['faq'] as &$question) {
                $question['answer'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper($question, $bbCodeParser, $bbCodeOptions);
            }
        }
    }
}
