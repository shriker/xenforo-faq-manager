<?php

class Iversia_FAQ_NewsFeedHandler_Question extends XenForo_NewsFeedHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, array $viewingUser)
    {
        return $model->getModelFromCache('Iversia_FAQ_Model_Question')->getPagesByIDs($contentIds);
    }
}
