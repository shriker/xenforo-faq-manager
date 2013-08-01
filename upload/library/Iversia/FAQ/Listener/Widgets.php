<?php

class Iversia_FAQ_Listener_Widgets
{
    public static function widget_framework_ready(array &$renderers)
    {
        $renderers[] = 'Iversia_FAQ_WidgetRenderer_MostPopular';
        $renderers[] = 'Iversia_FAQ_WidgetRenderer_LatestAnswers';
    }
}
