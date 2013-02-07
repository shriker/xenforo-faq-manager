<?php

class Iversia_FAQ_Route_Prefix_FAQ implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'faq_id');
        $action = $router->resolveActionAsPageNumber($action, $request);

        return $router->getRouteMatch('Iversia_FAQ_ControllerPublic_FAQ', $action, 'faq', $routePath);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if ( ! is_array($data))
		{
			$action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

			return XenForo_Link::buildBasicLink($outputPrefix, $action);
		}

        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'faq_id', 'question');
    }
}