<?php

class Iversia_FAQ_ViewPublic_Permalink extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
        $bbCodeOptions = array(
            'states' => array(
                'viewAttachments' => false
            )
        );

        $formatter = XenForo_BbCode_Formatter_Base::create();
        $parser = new XenForo_BbCode_Parser($formatter);

        $this->_params['question']['answer'] = $parser->render($this->_params['question']['answer']);
	}
}