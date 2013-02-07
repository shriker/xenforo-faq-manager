<?php

class Iversia_FAQ_Listener_BbCode
{
    public static function listen($class, array &$extend)
    {
        if ($class == 'XenForo_BbCode_Formatter_Base')
        {
            $extend[] = 'Iversia_FAQ_BbCode_Formatter_Base';
        }
    }
}