<?php

class Iversia_FAQ_Route_Prefix_FAQ implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'faq_id');
        $actions = explode('/', $action);

        switch ($actions[0]) {
            case 'category':
                $action = $router->resolveActionWithIntegerParam($routePath, $request, 'category_id');
                $action = $router->resolveActionAsPageNumber($action, $request);
                break;
            default:
                $action = $router->resolveActionWithIntegerParam($routePath, $request, 'faq_id');
                $action = $router->resolveActionAsPageNumber($action, $request);
                break;
        }

        return $router->getRouteMatch('Iversia_FAQ_ControllerPublic_FAQ', $action, 'faq');
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $components = explode('/', $action);
        $subPrefix = strtolower(array_shift($components));
        $intParams = '';
        $strParams = '';
        $slice = false;

        switch ($subPrefix) {
            case 'category':
                $intParams = 'category_id';
                $title = 'title';
                break;
            case 'category-edit':
                $intParams = 'category_id';
                $title = 'title';
                break;
            case 'category-delete':
                $intParams = 'category_id';
                $title = 'title';
                break;
            case 'category-save':
                $intParams = 'category_id';
                $title = 'title';
                break;
            default:
                $intParams = 'faq_id';
                $title = 'question';
                break;
        }

        if ($slice) {
            $outputPrefix .= '/'.$subPrefix;
            $action = implode('/', $components);
        }

        $action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

        if (!is_array($data)) {
            $action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

            return XenForo_Link::buildBasicLink($outputPrefix, $action);
        }

        if ($strParams) {
            return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, $strParams);
        } else {
            return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, $intParams, $title);
        }
    }
}
