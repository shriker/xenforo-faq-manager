<?php

class Iversia_FAQ_ViewPublic_Create extends XenForo_ViewPublic_Base
{
    /**
     * renderHtml function.
     * Add WYSIWYG editor to submission form.
     *
     * @return void
     */
    public function renderHtml()
    {
        $this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate($this, 'message');
    }
}
