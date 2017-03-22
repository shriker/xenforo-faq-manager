<?php

class Iversia_FAQ_Model_Question extends XenForo_Model
{
    public function getById($faq_id, $moderation = 0)
    {
        return $this->_getDb()->fetchRow('
            SELECT f.*, c.title FROM xf_faq_question f
            LEFT JOIN xf_faq_category c ON (c.category_id = f.category_id)
            WHERE f.faq_id = ?', $faq_id);
    }

    public function getQuestionsByIds(array $questionIds)
    {
        if (!$questionIds) {
            return [];
        }

        return $this->fetchAllKeyed('
            SELECT question.*, user.*
            FROM xf_faq_question AS question
            LEFT JOIN xf_user AS user ON (user.user_id = question.user_id)
            WHERE question.faq_id IN ('.$this->_getDb()->quote($questionIds).')
        ', 'faq_id');
    }

    public function getAll($fetchOptions = [])
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        $orderClause = $this->prepareUserOrderOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults(
            'SELECT *, c.title
             FROM xf_faq_question
             LEFT JOIN xf_faq_category c ON (c.category_id = xf_faq_question.category_id)
             WHERE moderation = 0
             '.$orderClause.'
            ', $limitOptions['limit'], $limitOptions['offset']
        ), 'faq_id');
    }

    public function getAllCategory($category_id, $fetchOptions = [])
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        $orderClause = $this->prepareUserOrderOptions($fetchOptions, 'submit_date DESC');

        $query = $this->fetchAllKeyed($this->limitQueryResults(
            '
            SELECT *
             FROM xf_faq_question
             WHERE category_id = ? and moderation = 0
             '.$orderClause.'
            ', $limitOptions['limit'], $limitOptions['offset']
        ), 'faq_id', $category_id);

        return $query;
    }

    public function prepareUserOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
    {
        $choices = [
            'question'      => 'sticky desc, xf_faq_question.display_order asc, question',
            'view_count'    => 'sticky desc, xf_faq_question.display_order asc, view_count',
            'submit_date'   => 'sticky desc, xf_faq_question.display_order asc, submit_date',
        ];

        return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
    }

    public function getTotal($moderation = 0)
    {
        return $this->_getDb()->fetchOne('
            SELECT COUNT(*) AS question_count
            FROM xf_faq_question
            WHERE moderation = ?
        ', ['moderation' => $moderation]);
    }

    public function getViewTotal($moderation = 0)
    {
        return $this->_getDb()->fetchOne('
            SELECT SUM(view_count) AS view_count
            FROM xf_faq_question
            WHERE moderation = ?
        ', ['moderation' => $moderation]);
    }

    public function getCategoryTotal($category_id, $moderation = 0)
    {
        return $this->_getDb()->fetchOne('
            SELECT COUNT(*) AS question_count
            FROM xf_faq_question
            WHERE moderation = ? and category_id = ?
        ', ['moderation' => $moderation, 'category_id' => $category_id]);
    }

    public function getLatest($limit, $moderation = 0)
    {
        return $this->fetchAllKeyed("SELECT *
            FROM xf_faq_question
            WHERE moderation = $moderation
            ORDER BY submit_date DESC LIMIT $limit", 'faq_id');
    }

    public function getPopular($limit, $moderation = 0)
    {
        return $this->fetchAllKeyed("SELECT *
            FROM xf_faq_question
            WHERE moderation = $moderation
            ORDER BY view_count DESC LIMIT $limit", 'faq_id');
    }

    public function getSticky($limit, $category_id = null)
    {
        return $this->fetchAllKeyed("SELECT *
            FROM xf_faq_question
            WHERE sticky = 1 and moderation = 0
            ORDER BY display_order ASC, view_count DESC LIMIT $limit", 'faq_id');
    }

    public function getFaqIdsInRange($start, $limit)
    {
        $db = $this->_getDb();

        return $db->fetchCol($db->limit('
            SELECT faq_id
            FROM xf_faq_question
            WHERE faq_id > ?
            ORDER BY faq_id
        ', $limit), $start);
    }

    public function getByCategoryId($category_id)
    {
        return $this->fetchAllKeyed('SELECT * FROM xf_faq_question WHERE category_id = ? ORDER BY submit_date DESC', $category_id);
    }

    public function logQuestionView($faq_id)
    {
        $this->_getDb()->query('UPDATE xf_faq_question SET view_count = view_count+1 WHERE faq_id = ?', $faq_id);
    }

    public function deleteOrphanQuestions($category_id)
    {
        $this->_getDb()->query('DELETE FROM xf_faq_question WHERE category_id = ?', $category_id);
    }

    public function canManageFAQ()
    {
        $visitor = XenForo_Visitor::getInstance();

        if ($visitor->hasPermission('FAQ_Manager_Permissions', 'manageFAQ')) {
            return true;
        }

        return false;
    }

    public function canLikeFAQ()
    {
        $visitor = XenForo_Visitor::getInstance();

        if ($visitor->hasPermission('FAQ_Manager_Permissions', 'canLikeFAQ')) {
            return true;
        }

        return false;
    }

    public function canAskQuestions()
    {
        $visitor = XenForo_Visitor::getInstance();

        if ($visitor->hasPermission('FAQ_Manager_Permissions', 'canAskQuestions')) {
            return true;
        }

        return false;
    }

    public function getAttachmentParams(array $contentData = [], array $viewingUser = null, $tempHash = null)
    {
        if ($this->canUploadAndManageAttachment($null, $viewingUser)) {
            return [
                'hash'         => $tempHash ? $tempHash : md5(uniqid('', true)),
                'content_type' => 'xf_faq_question',
                'content_data' => $contentData,
            ];
        } else {
            return false;
        }
    }

    public function canUploadAndManageAttachment(&$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        return $viewingUser['user_id']
            && XenForo_Permission::hasPermission($viewingUser['permissions'], 'FAQ_Manager_Permissions', 'uploadFAQAttach');
    }

    public function getAndMergeAttachmentsIntoQuestion($questions, $faq_id = null)
    {
        $attachmentModel = $this->_getAttachmentModel();
        $questionIds = [];

        if ($faq_id != null) {
            foreach ($attachmentModel->getAttachmentsByContentId('xf_faq_question', $faq_id) as $attachmentId => $attachment) {
                $questions['attachments'][$attachment['attachment_id']] = $attachmentModel->prepareAttachment($attachment);
            }
        } else {
            foreach ($questions as $questionId => $question) {
                if ($question['attach_count']) {
                    $questionIds[] = $questionId;
                }
            }

            if ($questionIds) {
                foreach ($attachmentModel->getAttachmentsByContentIds('xf_faq_question', $questionIds) as $attachment) {
                    $questions[$attachment['content_id']]['attachments'][$attachment['attachment_id']] = $attachmentModel->prepareAttachment($attachment);
                }
            }
        }

        return $questions;
    }

    protected function _getAttachmentModel()
    {
        return $this->getModelFromCache('XenForo_Model_Attachment');
    }
}
