<?php

class Iversia_FAQ_AlertHandler_Question extends XenForo_AlertHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
    {
        // alert_{contentType}_like
        return $model->getModelFromCache('Iversia_FAQ_Model_Question')->getQuestionsByIds($contentIds);
    }
}
