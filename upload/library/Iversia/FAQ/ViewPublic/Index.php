<?php

class Iversia_FAQ_ViewPublic_Index extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
        $bbCodeOptions = array(
            'states' => array(
                'viewAttachments' => false
            )
        );

        foreach ($this->_params['faq'] as &$question) {
            $question['answer'] = new XenForo_BbCode_TextWrapper($question['answer'], $bbCodeParser);
        }
    }
}
