<?php

class Iversia_FAQ_BbCode_Formatter_Base extends XFCP_Iversia_FAQ_BbCode_Formatter_Base {

	protected $_tags;

    public function getTags()
    {
    	if ($this->_tags !== null)
    	{
			return $this->_tags;
		}

        $this->_tags = parent::getTags();

		$this->_tags['faq'] = array(
            'hasOption' => true,
            'optionRegex' => '/^[0-9]+$/i',
            'plainChildren' => true,
            'trimLeadingLinesAfter' => 2,
            'callback' 	=> array($this, 'renderTagFAQ')
        );

        return $this->_tags;
    }

    public function preLoadTemplates(XenForo_View $view)
    {
		$view->preLoadTemplate('iversia_faq_bbcode');

		return parent::preLoadTemplates($view);
	}

    public function renderTagFAQ(array $tag, array $rendererStates)
    {
    	$question_id = $tag['option'];

    	if(isset($question_id))
    	{
	    	// Get questions from the cache
	    	$questions 	= XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqCache');
	    	$question 	= $questions[$question_id];

	    	if($question)
	    	{
	    		$faqData = array(
	    			'faqLink' 	=> XenForo_Link::buildPublicLink('faq', array('question' => $question,'faq_id' => $question_id)),
	    			'faq_id'	=> $question_id,
	    			'question'	=> $question,
	    		);

	    		if ($this->_view)
	    		{
					$template = $this->_view->createTemplateObject('iversia_faq_bbcode', $faqData);
					return $template->render();
				}
				else
				{
					return '<b>'.new XenForo_Phrase('iversia_faq').' #'.$tag['option'].':</b> <a href="'. XenForo_Link::buildPublicLink('faq', array('question' => $question['question'],'faq_id' => $question_id)) .'">'.htmlentities($question).'</a>';
				}
			}
			else
			{
				return '<b>'. new XenForo_Phrase('iversia_faq') .'</b>: Question not found.';
			}
		}
    }
}