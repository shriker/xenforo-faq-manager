<?php

class Iversia_FAQ_ViewPublic_Edit extends XenForo_ViewPublic_Base
{
    /**
     * renderHtml function.
     * Add WYSIWYG editor to submission form.
     *
     * @return void
     */
    public function renderHtml()
    {
        $this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate($this, 'message', $this->_params['question']['answer']);
    }
}
