<?php

class Iversia_FAQ_Route_Prefix_FAQ implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $components = explode('/', $routePath);
        $subPrefix  = strtolower(array_shift($components));
        $subSplits  = explode('.', $subPrefix);

        if (is_array($components) && isset($components[0]))
        {
            if ( $components[0] == 'category' || $subPrefix == 'category')
            {
                $action = $router->resolveActionWithIntegerParam($routePath, $request, 'category_id');
                $action = $router->resolveActionAsPageNumber($action, $request);
                return $router->getRouteMatch('Iversia_FAQ_ControllerPublic_Category', $action, 'faq');
            }
        }

        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'faq_id');
        $action = $router->resolveActionAsPageNumber($action, $request);
        return $router->getRouteMatch('Iversia_FAQ_ControllerPublic_FAQ', $action, 'faq', $routePath);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if ($action == 'category' OR $action == 'category/edit' OR $action == 'category/delete')
        {
            $action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

            return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'category_id', 'title');
        }

        // Question Link
        if ( ! is_array($data))
		{
			$action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

			return XenForo_Link::buildBasicLink($outputPrefix, $action);
		}

        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'faq_id', 'question');
    }
}