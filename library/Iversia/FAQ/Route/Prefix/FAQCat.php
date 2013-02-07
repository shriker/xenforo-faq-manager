<?php
class Iversia_FAQ_Route_Prefix_FAQCat implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'category_id');
		$action = $router->resolveActionAsPageNumber($action, $request);

		return $router->getRouteMatch('Iversia_FAQ_ControllerPublic_Category', $action, 'faq');
	}

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'category_id', 'title');
	}
}