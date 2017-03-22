<?php

class Iversia_FAQ_ViewPublic_LikeConfirmed extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        $question = $this->_params['question'];

        if (!empty($question['like_users'])) {
            $params = [
                'question' => $question,
                'likesUrl' => XenForo_Link::buildPublicLink('faq/likes', $question),
            ];

            $output = $this->_renderer->getDefaultOutputArray(get_class($this), $params, 'iversia_faq_question_likes_summary');
        } else {
            $output = ['templateHtml' => '', 'js' => '', 'css' => ''];
        }

        $output += XenForo_ViewPublic_Helper_Like::getLikeViewParams($this->_params['liked']);

        return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
    }
}
