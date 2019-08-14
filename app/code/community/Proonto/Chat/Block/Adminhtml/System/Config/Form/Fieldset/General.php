<?php

class Proonto_Chat_Block_Adminhtml_System_Config_Form_Fieldset_General
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $html .= $this->_getGeneralHtml($element);
        $html .= $this->_getFooterHtml($element);
        return $html;
    }

    /**
     * Get html of general fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getGeneralHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /**
         * @var $fieldsBlock Proonto_Chat_Block_Adminhtml_System_Config_Form_Fieldset_General_Fields
         */
        $fieldsBlock = $this->getLayout()->createBlock('proonto_chat/adminhtml_system_config_form_fieldset_general_fields');
        $fieldsBlock->setElements($element->getSortedElements());
        return $fieldsBlock->toHtml();
    }
}