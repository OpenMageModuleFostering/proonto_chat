<?php

abstract class Proonto_Chat_Block_Abstract extends Mage_Core_Block_Template
{
    /**
     * @return Proonto_Chat_Helper_Data
     */
    public function getChatHelper()
    {
        return $this->helper('proonto_chat');
    }

    /**
     * @return string
     */
    public function getTestMode()
    {
        return $this->getChatHelper()->getTestMode();
    }

    /**
     * @return string
     */
    public function getCompanyId()
    {
        return $this->getChatHelper()->getCompanyId();
    }

    /**
     * @return string
     */
    public function getScriptDomain()
    {
        return $this->getTestMode() ?
            'chatstg.proonto.com' : 'chat.proonto.com';
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $helper = $this->getChatHelper();
        if (!$helper->getCompanyId() || !$helper->getEnabled() || !$helper->getVerified()) {
            return '';
        }
        return parent::_toHtml();
    }
}