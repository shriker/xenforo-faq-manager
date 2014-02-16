<?php

class Iversia_FAQ_ViewPublic_Permalink extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
        $bbCodeOptions = array(
            'states' => array(
                'viewAttachments' => true
            ),
            'contentType' => 'xf_faq_question',
            'contentIdKey' => 'faq_id',
            'messageKey' => 'answer',
        );

        if (isset($this->_params['question']['answer'])) {

            $this->_params['question']['answer'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper($this->_params['question'], $bbCodeParser, $bbCodeOptions);

        }
    }
}
