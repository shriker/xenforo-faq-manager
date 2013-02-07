<?php

class Iversia_FAQ_ControllerPublic_Category extends XenForo_ControllerPublic_Abstract
{
	public function __construct($request, $response, $routeMatch)
	{
		parent::__construct($request, $response, $routeMatch);
	}

	public function actionIndex()
	{
		$category_id 	= $this->_input->filterSingle('category_id', XenForo_Input::UINT);
		$page 			= $this->_input->filterSingle('page', XenForo_Input::UINT);

		$faqPerPage = XenForo_Application::get('options')->faqPerPage;

		$viewParams = array(
			'faq'		=> $this->_getQuestionModel()->getAllCategory($category_id, array(
					'perPage' 	=> $faqPerPage,
					'page' 		=> $page,
					'order' 	=> XenForo_Application::get('options')->faqSortOrder,
					'direction' => XenForo_Application::get('options')->faqSortOrderDir,
					)
				),
			'page' 				=> $page,
			'faqPerPage' 		=> $faqPerPage,
			'faqCatTotal'		=> $this->_getQuestionModel()->getCategoryTotal($category_id),
			'faqcategory'		=> $this->_getCategoryModel()->getById($category_id),
			'categories' 		=> $this->_getCategoryModel()->getAll(),
			'canManageCats'		=> $this->_getCategoryModel()->canManageCategories(),
		);

		return $this->responseView('Iversia_FAQ_ViewPublic_Category', 'iversia_faq_category', $viewParams);
	}

	public function actionCreate()
	{
		$this->_assertCanManageCategories();

		$viewParams = array();

		return $this->responseView('Iversia_FAQ_ViewPublic_Category', 'iversia_faq_create_category', $viewParams);
	}

	public function actionEdit()
	{
		$this->_assertCanManageCategories();

		$category_id 	= $this->_input->filterSingle('category_id', XenForo_Input::UINT);

		$viewParams = array(
			'faqcategory'		=> $this->_getCategoryModel()->getById($category_id),
		);

		return $this->responseView('Iversia_FAQ_ViewPublic_Category', 'iversia_faq_edit_category', $viewParams);
	}

	public function actionDelete()
	{
		$this->_assertCanManageCategories();

		$category_id 	= $this->_input->filterSingle('category_id', XenForo_Input::UINT);

		// Delete category
		$dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Category');
		$dw->setExistingData($category_id);
		$dw->delete();

		// Delete associated questions
		$this->_getQuestionModel()->deleteOrphanQuestions($category_id);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('faq'),
			new XenForo_Phrase('iversia_faq_category_deleted')
		);
	}

	public function actionSave()
	{
		$this->_assertCanManageCategories();

		$category_id 	= $this->_input->filterSingle('category_id', XenForo_Input::UINT);
		$saveAction		= new XenForo_Phrase('iversia_faq_category_added');

		$dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Category');
		if($category_id)
		{
			$dw->setExistingData($category_id);
			$saveAction	= new XenForo_Phrase('iversia_faq_category_edited');
		}
		$dw->bulkSet(array(
			'title'	=> $this->_input->filterSingle('title', XenForo_Input::STRING)
		));
		$dw->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('faq'),
			$saveAction
		);
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
}