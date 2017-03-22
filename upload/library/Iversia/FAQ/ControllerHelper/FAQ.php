<?php

class Iversia_FAQ_ControllerHelper_FAQ extends XenForo_ControllerHelper_Abstract
{
    public function getWrapper($selectedGroup, $selectedLink, XenForo_ControllerResponse_View $subView)
    {
        $viewParams = [
            'selectedGroup' => $selectedGroup,
            'selectedLink'  => $selectedLink,
            'selectedKey'   => "$selectedGroup/$selectedLink",

            'categories'    => $this->_controller->getModelFromCache('Iversia_FAQ_Model_Category')->getAll(),

            // Statistics
            'faqTotal'      => $this->_controller->getModelFromCache('Iversia_FAQ_Model_Question')->getTotal(),
            'faqStats'      => XenForo_Model::create('XenForo_Model_DataRegistry')->get('faqStats'),

            // Permissions
            'canManageFAQ'     => $this->_controller->getModelFromCache('Iversia_FAQ_Model_Question')->canManageFAQ(),
            'canAskQuestions'  => $this->_controller->getModelFromCache('Iversia_FAQ_Model_Question')->canAskQuestions(),
        ];

        $wrapper = $this->_controller->responseView('Iversia_FAQ_ViewPublic_Wrapper', 'faq_wrapper', $viewParams);
        $wrapper->subView = $subView;

        return $wrapper;
    }

    public static function wrap(XenForo_Controller $controller, $selectedGroup, $selectedLink, XenForo_ControllerResponse_View $subView)
    {
        $helper = new self($controller);

        return $helper->getWrapper($selectedGroup, $selectedLink, $subView);
    }
}
