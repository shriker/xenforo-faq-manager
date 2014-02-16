<?php

class Iversia_FAQ_ControllerPublic_FAQ extends XenForo_ControllerPublic_Abstract
{
    public function __construct($request, $response, $routeMatch)
    {
        parent::__construct($request, $response, $routeMatch);
    }

    /**
     * Display FAQ Index
     */
    public function actionIndex()
    {
        $questionModel = $this->_getQuestionModel();

        $faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);
        $page   = $this->_input->filterSingle('page', XenForo_Input::UINT);

        if ($faq_id) {
            return $this->responseReroute(__CLASS__, 'permalink');
        }

        $faqPerPage     = XenForo_Application::get('options')->faqPerPage;

        $questions = $questionModel->getAll(array(
            'perPage'   => $faqPerPage,
            'page'      => $page,
            'order'     => XenForo_Application::get('options')->faqSortOrder,
            'direction' => XenForo_Application::get('options')->faqSortOrderDir,
        ));

        // Get attachments
        $questions = $questionModel->getAndMergeAttachmentsIntoQuestion($questions);

        $viewParams = array(
            'faq'           => $questions,
            'page'          => $page,
            'faqPerPage'    => $faqPerPage,
            'faqTotal'      => $this->_getQuestionModel()->getTotal(),
            // Sidebar
            'popular'       => $this->_getQuestionModel()->getPopular(5),
            'latest'        => $this->_getQuestionModel()->getLatest(5),
            'faqStats'      => XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqStats'),
            // Permissions
            'canManageFAQ'  => $this->_getQuestionModel()->canManageFAQ(),
            'canManageCats' => $this->_getCategoryModel()->canManageCategories(),
        );

        return $this->getWrapper('faq', 'index', $this->responseView('Iversia_FAQ_ViewPublic_Index', 'iversia_faq_index', $viewParams));
    }

    /**
     * Display FAQ Category Index
     */
    public function actionCategory()
    {
        $category_id = $this->_input->filterSingle('category_id', XenForo_Input::UINT);
        $page = $this->_input->filterSingle('page', XenForo_Input::UINT);

        $questionModel = $this->_getQuestionModel();

        $faqPerPage = XenForo_Application::get('options')->faqPerPage;

        $category = $this->_getCategoryModel()->getById($category_id);

        if (!$category) {
            throw $this->responseException($this->responseError(new XenForo_Phrase('requested_page_not_found'), 404));
        }

        $questions = $questionModel->getAllCategory(
            $category_id,
            array(
                'perPage'   => $faqPerPage,
                'page'      => $page,
                'order'     => XenForo_Application::get('options')->faqSortOrder,
                'direction' => XenForo_Application::get('options')->faqSortOrderDir,
            )
        );

        // Get attachments
        $questions = $questionModel->getAndMergeAttachmentsIntoQuestion($questions);

        $viewParams = array(
            'faq' => $questions,
            'page'               => $page,
            'faqPerPage'         => $faqPerPage,
            'faqCatTotal'        => $questionModel->getCategoryTotal($category_id),
            'faqcategory'        => $category,
            // Sidebar
            'popular'       => $questionModel->getPopular(5),
            'latest'        => $questionModel->getLatest(5),
            'faqStats'      => XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqStats'),
            // Permissions
            'canManageFAQ'  => $questionModel->canManageFAQ(),
            'canManageCats' => $this->_getCategoryModel()->canManageCategories(),
        );

        return $this->getWrapper('category', $category_id, $this->responseView('Iversia_FAQ_ViewPublic_Index', 'iversia_faq_category', $viewParams));
    }

    /**
     * Create a new FAQ Category
     */
    public function actionCategoryCreate()
    {
        $this->_assertCanManageCategories();
        return $this->responseView('Iversia_FAQ_ViewPublic_Category', 'iversia_faq_create_category', array());
    }

    /**
     * Edit an existing FAQ category
     */
    public function actionCategoryEdit()
    {
        $this->_assertCanManageCategories();
        $category_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);
        $viewParams = array('faqcategory' => $this->_getCategoryModel()->getById($category_id));
        return $this->responseView('Iversia_FAQ_ViewPublic_Category', 'iversia_faq_edit_category', $viewParams);
    }

    /**
     * Save a new or existing FAQ category
     */
    public function actionCategorySave()
    {
        $this->_assertCanManageCategories();

        $category_id     = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);
        $saveAction        = new XenForo_Phrase('iversia_faq_category_added');

        $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Category');
        if ($category_id) {
            $dw->setExistingData($category_id);
            $saveAction    = new XenForo_Phrase('iversia_faq_category_edited');
        }
        $dw->bulkSet(
            array(
                'title'    => $this->_input->filterSingle('title', XenForo_Input::STRING),
                'display_order'    => $this->_input->filterSingle('display_order', XenForo_Input::UINT),
            )
        );
        $dw->save();

        // Delete from cache
        XenForo_Application::setSimpleCacheData('faq_categories', false);

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('faq'),
            $saveAction
        );
    }

    /**
     * Delete a FAQ category and all of its questions
     */
    public function actionCategoryDelete()
    {
        $this->_assertCanManageCategories();

        $category_id     = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

        // Delete category
        $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Category');
        $dw->setExistingData($category_id);
        $dw->delete();

        // Delete associated questions
        $this->_getQuestionModel()->deleteOrphanQuestions($category_id);

        // Delete from cache
        XenForo_Application::setSimpleCacheData('faq_categories', false);

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('faq'),
            new XenForo_Phrase('iversia_faq_category_deleted')
        );
    }

    /**
     * Link directly to a question
     */
    public function actionPermalink()
    {
        $faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

        $questionModel = $this->_getQuestionModel();

        $user_id = XenForo_Visitor::getUserId();

        $question = $questionModel->getById($faq_id);

        if (!$question) {
            throw $this->responseException($this->responseError(new XenForo_Phrase('requested_page_not_found'), 404));
        }

        $questionModel->logQuestionView($faq_id);

        // Get attachments
        $question = $questionModel->getAndMergeAttachmentsIntoQuestion($question, $question['faq_id']);

        // Likes
        $likeModel                 = $this->_getLikeModel();
        $question['like_users']    = unserialize($question['like_users']);
        $question['like_date']     = $likeModel->getContentLikeByLikeUser('xf_faq_question', $faq_id, $user_id);

        $viewParams = array(
            'question'      => $question,
            'categories'    => $this->_getCategoryModel()->getAll(),
            'canManageFAQ'  => $questionModel->canManageFAQ(),
            'canLikeFAQ'    => $questionModel->canLikeFAQ(),
        );

        return $this->getWrapper('faq', 'x', $this->responseView('Iversia_FAQ_ViewPublic_Permalink', 'iversia_faq_question', $viewParams));
    }

    /**
     * Like an answer
     */
    public function actionLike()
    {
        $faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

        $visitor = XenForo_Visitor::getInstance();

        // Can user like FAQ entry?
        $this->_assertCanLikeFAQ();

        $question   = $this->_getQuestionModel()->getById($faq_id);
        $likeModel  = $this->_getLikeModel();

        if (!$question) {
            throw $this->responseException($this->responseError(new XenForo_Phrase('requested_page_not_found'), 404));
        }

        // Users cannot like their own FAQ entries
        if ($question['user_id'] == $visitor['user_id']) {
            throw $this->getNoPermissionResponseException();
        }

        $existingLike = $likeModel->getContentLikeByLikeUser('xf_faq_question', $faq_id, XenForo_Visitor::getUserId());

        if ($this->_request->isPost()) {
            if ($existingLike) {
                $latestUsers = $likeModel->unlikeContent($existingLike);
            } else {
                $latestUsers = $likeModel->likeContent('xf_faq_question', $faq_id, $question['user_id']);
            }

            $liked = ($existingLike ? false : true);

            if ($this->_noRedirect() && $latestUsers !== false) {
                $question['like_users'] = $latestUsers;
                $question['likes']     += ($liked ? 1 : -1);
                $question['like_date']  = ($liked ? XenForo_Application::$time : 0);

                $viewParams = array(
                    'question'  => $question,
                    'liked'     => $liked
                );

                return $this->responseView('Iversia_FAQ_ViewPublic_LikeConfirmed', '', $viewParams);
            } else {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    XenForo_Link::buildPublicLink('faq', $question)
                );
            }
        } else {
            $viewParams  = array(
                'question'  => $question,
                'like'      => $existingLike
            );

            return $this->responseView('Iversia_FAQ_ViewPublic_Like', 'iversia_faq_question_like', $viewParams);
        }
    }

    /**
     * Likes an answer has
     */
    public function actionLikes()
    {
        $faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::STRING);
        $question = $this->_getQuestionModel()->getById($faq_id);

        if (!$question) {
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('faq'));
        }

        $likes = $this->getModelFromCache('XenForo_Model_Like')->getContentLikes('xf_faq_question', $faq_id);

        if (!$likes) {
            return $this->responseError(new XenForo_Phrase('no_one_has_liked_this_post_yet'));
        }

        $viewParams = array(
            'question'  => $question,
            'likes'     => $likes
        );

        return $this->responseView('Iversia_FAQ_ViewPublic_PageLikes', 'iversia_faq_question_all_likes', $viewParams);
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
        $question = $this->_getQuestionModel()->getById($faq_id);

        $questionModel = $this->_getQuestionModel();
        $attachmentModel = $this->_getAttachmentModel();

        $attachmentParams = $questionModel->getAttachmentParams($question);
        $attachments = $attachmentModel->getAttachmentsByContentId('xf_faq_question', $question['faq_id']);
        $attachments = $attachmentModel->prepareAttachments($attachments);

        $viewParams = array(
            'categories'    => $this->_getCategoryModel()->getAll(),
            'question'      => $question,
            'attachmentParams' => $attachmentParams,
            'attachments' => $attachments,
            'attachmentConstraints' => $attachmentModel->getAttachmentConstraints(),
        );

        return $this->responseView('Iversia_FAQ_ViewPublic_Edit', 'iversia_faq_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertCanManageFAQ();

        $faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

        $visitor = XenForo_Visitor::getInstance();

        $input = array();
        $input['question']          = $this->_input->filterSingle('question', XenForo_Input::STRING);
        $input['category_id']       = $this->_input->filterSingle('category_id', XenForo_Input::UINT);
        $input['sticky']            = $this->_input->filterSingle('sticky', XenForo_Input::UINT);
        $input['answer']            = $this->getHelper('Editor')->getMessageText('message', $this->_input);
        $input['answer']            = XenForo_Helper_String::autoLinkBbCode($input['answer']);
        $input['attachment_hash']   = $this->_input->filterSingle('attachment_hash', XenForo_Input::STRING);

        // New question
        if ($faq_id) {

            $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
            $dw->setExistingData($faq_id);
            $dw->bulkSet(
                array(
                    'category_id'       => $input['category_id'],
                    'moderation'        => 0,
                    'sticky'            => $input['sticky'],
                    'display_order'     => $this->_input->filterSingle('display_order', XenForo_Input::UINT),
                    'question'          => $input['question'],
                    'answer'            => $input['answer'],
                    'answer_date'       => XenForo_Application::$time, // Last updated
                )
            );

            $dw->setExtraData(Iversia_FAQ_DataWriter_Question::DATA_ATTACHMENT_HASH, $input['attachment_hash']);
            $dw->save();

            $returnLink = XenForo_Link::buildPublicLink('full:faq', array('faq_id' => $faq_id, 'question' => $input['question']));
            $saveAction = new XenForo_Phrase('iversia_faq_question_edited');

        } else {
            // Updating existing question
            $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
            $dw->bulkSet(
                array(
                    'user_id'           => $visitor['user_id'],
                    'category_id'       => $input['category_id'],
                    'moderation'        => 0,
                    'sticky'            => $input['sticky'],
                    'display_order'     => $this->_input->filterSingle('display_order', XenForo_Input::UINT),
                    'question'          => $input['question'],
                    'answer'            => $input['answer'],
                )
            );

            $dw->setExtraData(Iversia_FAQ_DataWriter_Question::DATA_ATTACHMENT_HASH, $input['attachment_hash']);
            $dw->save();

            $question = $dw->getMergedData();
            $returnLink = XenForo_Link::buildPublicLink('full:faq', $question);
            $saveAction = new XenForo_Phrase('iversia_faq_question_added');
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

    /**
     * Display the last 30 answers
     */
    public function actionLatestAnswers()
    {
        $viewParams = array('questions' => $this->_getQuestionModel()->getLatest(30));
        return $this->responseView('Iversia_FAQ_ViewPublic_LatestAnswers', '', $viewParams);
    }

    /**
     * Online list activities
     */
    public static function getSessionActivityDetailsForList(array $activities)
    {
        foreach ($activities as $key => $activity) {
            // Defaults
            $faqAction      = new XenForo_Phrase('viewing_page');
            $faqLinkText    = new XenForo_Phrase('iversia_faq');
            $faqLink        = XenForo_Link::buildPublicLink('full:faq');

            // Viewing a question
            if (!empty($activity['params']['faq_id'])) {
                $faq_id     = (int) $activity['params']['faq_id'];
                $questions  = XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqCache');
                if (isset($questions[$faq_id])) {
                    $question   = $questions[$faq_id];
                }

                if (isset($question)) {
                    $faqLinkText = new XenForo_Phrase('iversia_faq') . ' #'. $faq_id .': ' . $question;
                    $faqLink = XenForo_Link::buildPublicLink('full:faq', array('faq_id' => $faq_id, 'question' => $question));
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

    protected function getWrapper($selectedGroup, $selectedLink, XenForo_ControllerResponse_View $subView)
    {
        $faqHelper = new Iversia_FAQ_ControllerHelper_FAQ($this);
        return $faqHelper->getWrapper($selectedGroup, $selectedLink, $subView);
    }

    protected function _assertCanManageCategories()
    {
        if (!$this->_getCategoryModel()->canManageCategories()) {
            throw $this->getNoPermissionResponseException();
        }
    }

    protected function _assertCanManageFAQ()
    {
        if (!$this->_getQuestionModel()->canManageFAQ()) {
            throw $this->getNoPermissionResponseException();
        }
    }

    protected function _assertCanLikeFAQ()
    {
        if (!$this->_getQuestionModel()->canLikeFAQ()) {
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

    protected function _getLikeModel()
    {
        return $this->getModelFromCache('XenForo_Model_Like');
    }

    protected function _getAttachmentModel()
    {
        return $this->getModelFromCache('XenForo_Model_Attachment');
    }
}
