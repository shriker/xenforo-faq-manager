<?php

class Iversia_FAQ_DataWriter_Category extends XenForo_DataWriter
{
    protected function _getFields()
    {
        return [
            'xf_faq_category' => [
                'category_id'      => ['type' => self::TYPE_UINT, 'autoIncrement' => true],
                'title'            => ['type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 120],
                'display_order'    => ['type' => self::TYPE_UINT, 'required' => true, 'default' => 0],
                'short_desc'       => ['type' => self::TYPE_STRING,   'required' => false, 'maxLength' => 255],
                'long_desc'        => ['type' => self::TYPE_STRING,   'required' => false],
            ],
        ];
    }

    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'category_id')) {
            return false;
        }

        return ['xf_faq_category' => $this->getModelFromCache('Iversia_FAQ_Model_Category')->getById($id)];
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'category_id = '.$this->_db->quote($this->getExisting('category_id'));
    }
}
