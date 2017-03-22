<?php

class Iversia_FAQ_Listener_Extend
{
    public static function fileHealthCheck(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes += Iversia_FAQ_FileSums::getHashes();
    }

    public static function load_class($class, array &$extend)
    {
        static $classes = [
            'XenForo_ControllerPublic_Search',
            'XenForo_Model_Search',
        ];

        if (in_array($class, $classes)) {
            $extend[] = str_replace('XenForo_', 'Iversia_FAQ_', $class);
        }
    }

    /**
     * FAQ Manager Credits.
     *
     * Do not alter or delete these credits unless you have paid for their removal.
     */
    public static function credits(array $matches)
    {
        return $matches[0].
        '<xen:if is="{$controllerName} == \'Iversia_FAQ_ControllerPublic_FAQ\'">
        <div id="iversiaFAQ" class="muted">FAQ Manager &copy;'.date('Y').' <a href="http://www.iversia.com" title="Iversia - Web Developer and Illustrator" class="concealed">Iversia</a> from <a href="http://www.rpgfix.com" title="RPGfix, Role-Playing Game Directory" class="concealed">RPGfix</a>.</div></xen:if>';
    }
}
