<?php

class Iversia_FAQ_ControllerPublic_FAQ extends XenForo_ControllerPublic_Abstract
{
	public function __construct($request, $response, $routeMatch)
	{
		parent::__construct($request, $response, $routeMatch);
	}

	public function actionIndex()
	{
		$faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);
		$page 	= $this->_input->filterSingle('page', XenForo_Input::UINT);

		if ($faq_id)
		{
			return $this->responseReroute(__CLASS__, 'permalink');
		}

		$faqStats 		= XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqStats');
		$faqPerPage 	= XenForo_Application::get('options')->faqPerPage;
		$faqSortOrder 	= XenForo_Application::get('options')->faqSortOrder;

		$viewParams = array(
			'faq'		=> $this->_getQuestionModel()->getAll(array(
				'perPage' 	=> $faqPerPage,
				'page' 		=> $page,
				'order' 	=> XenForo_Application::get('options')->faqSortOrder,
				'direction' => XenForo_Application::get('options')->faqSortOrderDir,
				)
			),
			'categories' 	=> $this->_getCategoryModel()->getAll(),
			'popular' 		=> $this->_getQuestionModel()->getPopular(5),
			'latest' 		=> $this->_getQuestionModel()->getLatest(5),
			'page' 			=> $page,
			'faqPerPage' 	=> $faqPerPage,
			'faqTotal'		=> $this->_getQuestionModel()->getTotal(),
			'faqStats'		=> $faqStats,
			'canManageFAQ'	=> $this->_getQuestionModel()->canManageFAQ(),
			'canManageCats'	=> $this->_getCategoryModel()->canManageCategories(),
		);

		return $this->responseView('Iversia_FAQ_ViewPublic_Index', 'iversia_faq_index', $viewParams);
	}

	public function actionPermalink()
	{
		$faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

		$this->_getQuestionModel()->logQuestionView($faq_id);

		$viewParams = array(
			'question' 		=> $this->_getQuestionModel()->getById($faq_id),
			'categories' 	=> $this->_getCategoryModel()->getAll(),
			'canManageFAQ'	=> $this->_getQuestionModel()->canManageFAQ(),
		);

		return $this->responseView('Iversia_FAQ_ViewPublic_Permalink', 'iversia_faq_question', $viewParams);
	}

	public function actionCreate()
	{
		$this->_assertCanManageFAQ();

		$viewParams = array(
			'categories' => $this->_getCategoryModel()->getAll(),
		);

		return $this->responseView('Iversia_FAQ_ViewPublic_Create', 'iversia_faq_create', $viewParams);
	}

	public function actionEdit()
	{
		$this->_assertCanManageFAQ();

		$faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

		$viewParams = array(
			'categories' 	=> $this->_getCategoryModel()->getAll(),
			'question' 		=> $this->_getQuestionModel()->getById($faq_id)
		);

		return $this->responseView('Iversia_FAQ_ViewPublic_Edit', 'iversia_faq_edit', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertCanManageFAQ();

		$faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

		$visitor = XenForo_Visitor::getInstance();

		$input = array();
		$input['question'] 		= $this->_input->filterSingle('question', XenForo_Input::STRING);
		$input['category_id'] 	= $this->_input->filterSingle('category_id', XenForo_Input::UINT);
		$input['answer'] 		= $this->getHelper('Editor')->getMessageText('message', $this->_input);
		$input['answer'] 		= XenForo_Helper_String::autoLinkBbCode($input['answer']);

		$returnLink = XenForo_Link::buildPublicLink('full:faq');
		$saveAction = new XenForo_Phrase('iversia_faq_question_added');
		$answerDate = '';

		if($faq_id)
		{
			$returnLink = XenForo_Link::buildPublicLink('full:faq', array('faq_id' => $faq_id, 'question' => $input['question']));
			$saveAction = new XenForo_Phrase('iversia_faq_question_edited');

			$dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
			$dw->setExistingData($faq_id);
			$dw->bulkSet(array(
				'category_id'		=> $input['category_id'],
				'moderation'		=> 0,
				'question'			=> $input['question'],
				'answer'			=> $input['answer'],
				'answer_date'		=> XenForo_Application::$time, // Last updated
			));
			$dw->save();
		}
		else
		{
			$dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
			$dw->bulkSet(array(
				'user_id'			=> $visitor['user_id'],
				'category_id'		=> $input['category_id'],
				'moderation'		=> 0,
				'question'			=> $input['question'],
				'answer'			=> $input['answer'],
			));
			$dw->save();
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$returnLink,
			$saveAction
		);
	}

	public function actionDelete()
	{
		$this->_assertCanManageFAQ();

		$faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

		// Delete!
		$dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
		$dw->setExistingData($faq_id);
		$dw->delete();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('faq'),
			new XenForo_Phrase('iversia_faq_question_deleted')
		);
	}

	public function actionLatestAnswers()
	{
		$viewParams = array(
			'questions' => $this->_getQuestionModel()->getLatest(30),
		);

		return $this->responseView('Iversia_FAQ_ViewPublic_LatestAnswers', '', $viewParams);
	}

	protected function _assertCanManageCategories()
	{
		if ( ! $this->_getCategoryModel()->canManageCategories())
		{
			throw $this->getNoPermissionResponseException();
		}
	}

	protected function _assertCanManageFAQ()
	{
		if ( ! $this->_getQuestionModel()->canManageFAQ())
		{
			throw $this->getNoPermissionResponseException();
		}
	}

	protected function _getQuestionModel()
	{
		return $this->getModelFromCache('Iversia_FAQ_Model_Question');
	}

	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('Iversia_FAQ_Model_Category');
	}

	public static function getSessionActivityDetailsForList(array $activities)
	{
		foreach ($activities AS $key => $activity)
		{
			// Defaults
			$faqAction 		= new XenForo_Phrase('viewing_page');
			$faqLinkText 	= new XenForo_Phrase('iversia_faq');
			$faqLink		= XenForo_Link::buildPublicLink('full:faq');

			// Viewing a question
			if( ! empty($activity['params']['faq_id']))
			{
				$faq_id 	= (int) $activity['params']['faq_id'];
				$questions 	= XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqCache');
				$question 	= $questions[$faq_id];

				if($question)
				{
					$faqLinkText 	= new XenForo_Phrase('iversia_faq') . ' #'. $faq_id .': ' . $question;
					$faqLink		= XenForo_Link::buildPublicLink('full:faq', array(
						'faq_id' => $faq_id, 
						'question' => $question
					));
				}
			}

			$output[$key] = array(
				$faqAction,
				$faqLinkText,
				$faqLink,
				false
			);
		}

		return $output;
	}
}