<?php

class Iversia_FAQ_ControllerPublic_FAQ extends XenForo_ControllerPublic_Abstract
{
    public function __construct($request, $response, $routeMatch)
    {
        parent::__construct($request, $response, $routeMatch);
    }

    /**
     * Display FAQ Index.
     */
    public function actionIndex()
    {
        $viewParams = [];

        $questionModel = $this->_getQuestionModel();
        $categoryModel = $this->_getCategoryModel();

        $faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);
        $page = $this->_input->filterSingle('page', XenForo_Input::UINT);

        if ($faq_id) {
            return $this->responseReroute(__CLASS__, 'permalink');
        }

        // Page Layout
        $faqIndexLayout = XenForo_Application::get('options')->faqIndexLayout;
        $faqPerPage = XenForo_Application::get('options')->faqPerPage;
        $faqTotal = $questionModel->getTotal();

        $indexTemplate = 'iversia_faq_index';

        if ($faqIndexLayout == 'jump_links') {
            $indexTemplate = 'iversia_faq_jump_links';

            $questions = $questionModel->getAll([
                'order'     => XenForo_Application::get('options')->faqSortOrder,
                'direction' => XenForo_Application::get('options')->faqSortOrderDir,
            ]);

            // Get attachments
            $questions = $questionModel->getAndMergeAttachmentsIntoQuestion($questions);
        } elseif ($faqIndexLayout == 'question_columns') {
            $indexTemplate = 'iversia_faq_question_columns';
            $questions = $questionModel->getAll([
                'order'     => XenForo_Application::get('options')->faqSortOrder,
                'direction' => XenForo_Application::get('options')->faqSortOrderDir,
            ]);
        } else {
            $questions = $questionModel->getAll([
                'perPage'   => $faqPerPage,
                'page'      => $page,
                'order'     => XenForo_Application::get('options')->faqSortOrder,
                'direction' => XenForo_Application::get('options')->faqSortOrderDir,
            ]);

            // Get attachments
            $questions = $questionModel->getAndMergeAttachmentsIntoQuestion($questions);
        }

        $viewParams = [
            'faq'           => $questions,
            'page'          => $page,
            'faqPerPage'    => $faqPerPage,
            'faqTotal'      => $faqTotal,
            // Sidebar
            'popular'       => $questionModel->getPopular(5),
            'latest'        => $questionModel->getLatest(5),
            'faqStats'      => XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqStats'),
            'categories'    => $categoryModel->getAll(),
        ];

        // Permissions
        $viewParams['canManageFAQ'] = $questionModel->canManageFAQ();
        $viewParams['canManageCats'] = $categoryModel->canManageCategories();
        $viewParams['canAsk'] = $questionModel->canAskQuestions();

        $this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('faq', '', ['page' => $page]));
        $this->canonicalizePageNumber($page, $faqPerPage, $faqTotal, 'faq');

        return $this->getWrapper(
            'faq',
            'index',
            $this->responseView('Iversia_FAQ_ViewPublic_Index', $indexTemplate, $viewParams)
        );
    }

    /**
     * Ask a question.
     */
    public function actionAsk()
    {
        $questionModel = $this->_getQuestionModel();
        $categoryModel = $this->_getCategoryModel();

        $this->_assertRegistrationRequired();
        $this->_assertCanAskQuestions();

        $viewParams = [
            'categories'    => $categoryModel->getAll(),
        ];

        return $this->responseView('Iversia_FAQ_ViewPublic_Ask', 'iversia_faq_ask', $viewParams);
    }

    /**
     * Send asked question to moderation queue.
     */
    public function actionAskSave()
    {
        $this->_assertRegistrationRequired();
        $this->_assertCanAskQuestions();

        $visitor = XenForo_Visitor::getInstance();

        $input = [];
        $input['question'] = $this->_input->filterSingle('question', XenForo_Input::STRING);
        $input['category_id'] = $this->_input->filterSingle('category_id', XenForo_Input::UINT);

        $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
        $dw->bulkSet(
            [
                'user_id'           => $visitor['user_id'],
                'category_id'       => $input['category_id'],
                'moderation'        => 1, // Put into the moderation queue
                'sticky'            => 0,
                'display_order'     => 0,
                'question'          => $input['question'],
                'answer'            => '',
            ]
        );
        $dw->save();

        $question = $dw->getMergedData();

        // Add to moderation queue
        $this->getModelFromCache('XenForo_Model_ModerationQueue')->insertIntoModerationQueue(
            'xf_faq_question',
            $question['faq_id'],
            XenForo_Application::$time
        );

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('faq'),
            new XenForo_Phrase('iversia_faq_question_added')
        );
    }

    /**
     * Display FAQ Category Index.
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
            [
                'perPage'   => $faqPerPage,
                'page'      => $page,
                'order'     => XenForo_Application::get('options')->faqSortOrder,
                'direction' => XenForo_Application::get('options')->faqSortOrderDir,
            ]
        );

        $faqCatTotal = $questionModel->getCategoryTotal($category_id);

        $this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('faq/category', $category, ['page' => $page]));
        $this->canonicalizePageNumber($page, $faqPerPage, $faqCatTotal, 'faq/category');

        // Get attachments
        $questions = $questionModel->getAndMergeAttachmentsIntoQuestion($questions);

        $viewParams = [
            'faq'                => $questions,
            'page'               => $page,
            'faqPerPage'         => $faqPerPage,
            'faqCatTotal'        => $faqCatTotal,
            'faqcategory'        => $category,
            // Sidebar
            'popular'       => $questionModel->getPopular(5),
            'latest'        => $questionModel->getLatest(5),
            'faqStats'      => XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqStats'),
            // Permissions
            'canManageFAQ'  => $questionModel->canManageFAQ(),
            'canManageCats' => $this->_getCategoryModel()->canManageCategories(),
            'canAsk'        => $questionModel->canAskQuestions(),
        ];

        return $this->getWrapper('category', $category_id, $this->responseView('Iversia_FAQ_ViewPublic_Index', 'iversia_faq_category', $viewParams));
    }

    /**
     * Create a new FAQ Category.
     */
    public function actionCategoryCreate()
    {
        $this->_assertCanManageCategories();

        return $this->responseView('Iversia_FAQ_ViewPublic_Category', 'iversia_faq_create_category', []);
    }

    /**
     * Edit an existing FAQ category.
     */
    public function actionCategoryEdit()
    {
        $this->_assertCanManageCategories();
        $category_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);
        $viewParams = ['faqcategory' => $this->_getCategoryModel()->getById($category_id)];

        return $this->responseView('Iversia_FAQ_ViewPublic_Category', 'iversia_faq_edit_category', $viewParams);
    }

    /**
     * Save a new or existing FAQ category.
     */
    public function actionCategorySave()
    {
        $this->_assertCanManageCategories();

        $category_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);
        $saveAction = new XenForo_Phrase('iversia_faq_category_added');

        $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Category');
        if ($category_id) {
            $dw->setExistingData($category_id);
            $saveAction = new XenForo_Phrase('iversia_faq_category_edited');
        }
        $dw->bulkSet(
            [
                'title'            => $this->_input->filterSingle('title', XenForo_Input::STRING),
                'display_order'    => $this->_input->filterSingle('display_order', XenForo_Input::UINT),
                'short_desc'       => $this->_input->filterSingle('short_desc', XenForo_Input::STRING),
                'long_desc'        => $this->_input->filterSingle('long_desc', XenForo_Input::STRING),
            ]
        );
        $dw->save();

        // Delete from cache
        XenForo_Application::setSimpleCacheData('faq_categories', false);

        $category = $dw->getMergedData();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('faq/category', $category),
            $saveAction
        );
    }

    /**
     * Delete a FAQ category and all of its questions.
     */
    public function actionCategoryDelete()
    {
        $this->_assertCanManageCategories();

        $category_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

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
     * Link directly to a question.
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

        $this->canonicalizeRequestUrl(
            XenForo_Link::buildPublicLink('faq', $question)
        );

        // Likes
        $likeModel = $this->_getLikeModel();
        $question['like_users'] = unserialize($question['like_users']);
        $question['like_date'] = $likeModel->getContentLikeByLikeUser('xf_faq_question', $faq_id, $user_id);

        $viewParams = [
            'question'      => $question,
            'categories'    => $this->_getCategoryModel()->getAll(),
            'canManageFAQ'  => $questionModel->canManageFAQ(),
            'canLikeFAQ'    => $questionModel->canLikeFAQ(),
            'canAsk'        => $questionModel->canAskQuestions(),
        ];

        return $this->getWrapper('faq', 'x', $this->responseView('Iversia_FAQ_ViewPublic_Permalink', 'iversia_faq_question', $viewParams));
    }

    /**
     * Like an answer.
     */
    public function actionLike()
    {
        $faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

        $visitor = XenForo_Visitor::getInstance();

        // Can user like FAQ entry?
        $this->_assertCanLikeFAQ();

        $question = $this->_getQuestionModel()->getById($faq_id);
        $likeModel = $this->_getLikeModel();

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
                $question['likes'] += ($liked ? 1 : -1);
                $question['like_date'] = ($liked ? XenForo_Application::$time : 0);

                $viewParams = [
                    'question'  => $question,
                    'liked'     => $liked,
                ];

                return $this->responseView('Iversia_FAQ_ViewPublic_LikeConfirmed', '', $viewParams);
            } else {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    XenForo_Link::buildPublicLink('faq', $question)
                );
            }
        } else {
            $viewParams = [
                'question'  => $question,
                'like'      => $existingLike,
            ];

            return $this->responseView('Iversia_FAQ_ViewPublic_Like', 'iversia_faq_question_like', $viewParams);
        }
    }

    /**
     * Likes an answer has.
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

        $viewParams = [
            'question'  => $question,
            'likes'     => $likes,
        ];

        return $this->responseView('Iversia_FAQ_ViewPublic_PageLikes', 'iversia_faq_question_all_likes', $viewParams);
    }

    public function actionCreate()
    {
        $this->_assertCanManageFAQ();

        $questionModel = $this->_getQuestionModel();
        $attachmentModel = $this->_getAttachmentModel();

        $attachmentParams = $questionModel->getAttachmentParams();

        $viewParams = [
            'categories'            => $this->_getCategoryModel()->getAll(),
            'attachmentParams'      => $attachmentParams,
            'attachments'           => [],
            'attachmentConstraints' => $attachmentModel->getAttachmentConstraints(),
        ];

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

        $viewParams = [
            'categories'            => $this->_getCategoryModel()->getAll(),
            'question'              => $question,
            'attachmentParams'      => $attachmentParams,
            'attachments'           => $attachments,
            'attachmentConstraints' => $attachmentModel->getAttachmentConstraints(),
        ];

        return $this->responseView('Iversia_FAQ_ViewPublic_Edit', 'iversia_faq_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertCanManageFAQ();

        $faq_id = $this->_input->filterSingle('faq_id', XenForo_Input::UINT);

        $visitor = XenForo_Visitor::getInstance();

        $input = [];
        $input['question'] = $this->_input->filterSingle('question', XenForo_Input::STRING);
        $input['category_id'] = $this->_input->filterSingle('category_id', XenForo_Input::UINT);
        $input['sticky'] = $this->_input->filterSingle('sticky', XenForo_Input::UINT);
        $input['answer'] = $this->getHelper('Editor')->getMessageText('message', $this->_input);
        $input['answer'] = XenForo_Helper_String::autoLinkBbCode($input['answer']);
        $input['attachment_hash'] = $this->_input->filterSingle('attachment_hash', XenForo_Input::STRING);

        // Update existing
        if ($faq_id) {
            $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
            $dw->setExistingData($faq_id);
            $dw->bulkSet(
                [
                    'category_id'       => $input['category_id'],
                    'sticky'            => $input['sticky'],
                    'display_order'     => $this->_input->filterSingle('display_order', XenForo_Input::UINT),
                    'question'          => $input['question'],
                    'answer'            => $input['answer'],
                    'answer_date'       => XenForo_Application::$time, // Last updated
                ]
            );

            $dw->setExtraData(Iversia_FAQ_DataWriter_Question::DATA_ATTACHMENT_HASH, $input['attachment_hash']);
            $dw->save();

            $returnLink = XenForo_Link::buildPublicLink('full:faq', ['faq_id' => $faq_id, 'question' => $input['question']]);
            $saveAction = new XenForo_Phrase('iversia_faq_question_edited');
        } else {
            // New question
            $dw = XenForo_DataWriter::create('Iversia_FAQ_DataWriter_Question');
            $dw->bulkSet(
                [
                    'user_id'           => $visitor['user_id'],
                    'category_id'       => $input['category_id'],
                    'moderation'        => 0,
                    'sticky'            => $input['sticky'],
                    'display_order'     => $this->_input->filterSingle('display_order', XenForo_Input::UINT),
                    'question'          => $input['question'],
                    'answer'            => $input['answer'],
                ]
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
     * Display the last 30 answers.
     */
    public function actionLatestAnswers()
    {
        $viewParams = ['questions' => $this->_getQuestionModel()->getLatest(30)];

        return $this->responseView('Iversia_FAQ_ViewPublic_LatestAnswers', '', $viewParams);
    }

    /**
     * Online list activities.
     */
    public static function getSessionActivityDetailsForList(array $activities)
    {
        foreach ($activities as $key => $activity) {
            // Defaults
            $faqAction = new XenForo_Phrase('viewing_page');
            $faqLinkText = new XenForo_Phrase('iversia_faq');
            $faqLink = XenForo_Link::buildPublicLink('full:faq');

            // Viewing a question
            if (!empty($activity['params']['faq_id'])) {
                $faq_id = (int) $activity['params']['faq_id'];
                $questions = XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqCache');
                if (isset($questions[$faq_id])) {
                    $question = $questions[$faq_id];
                }

                if (isset($question)) {
                    $faqLinkText = new XenForo_Phrase('iversia_faq').' #'.$faq_id.': '.$question;
                    $faqLink = XenForo_Link::buildPublicLink('full:faq', ['faq_id' => $faq_id, 'question' => $question]);
                }
            }

            $output[$key] = [
                $faqAction,
                $faqLinkText,
                $faqLink,
                false,
            ];
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

    protected function _assertCanAskQuestions()
    {
        if (!$this->_getQuestionModel()->canAskQuestions()) {
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
