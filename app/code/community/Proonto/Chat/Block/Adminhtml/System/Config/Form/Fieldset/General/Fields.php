<?php

class Proonto_Chat_Block_Adminhtml_System_Config_Form_Fieldset_General_Fields
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('proonto/chat/general.phtml');
    }

    /**
     * @param array $elements
     * @return $this
     */
    public function setElements(array $elements)
    {
        $this->setData('elements', $elements);
        return $this;
    }

    /**
     * @return $this
     */
    public function getElements()
    {
        return $this->_getData('elements');
    }

    /**
     * @return Proonto_Chat_Helper_Data
     */
    public function getChatHelper()
    {
        return $this->helper('proonto_chat');
    }

    public function getVerified()
    {
        $request = $this->getRequest();

        $website = $request->getParam('website');
        $store = $request->getParam('store');

        if (!$website && !$store) {
            $value = $this->getChatHelper()->getVerified(Mage_Core_Model_App::ADMIN_STORE_ID);
        } elseif (!$store) {
            $value = $this->getChatHelper()->getWebsiteConfigValue($website, Proonto_Chat_Helper_Data::XML_PATH_VERIFIED);
        } else {
            $value = $this->getChatHelper()->getVerified($store);
        }

        return $value;
    }
}