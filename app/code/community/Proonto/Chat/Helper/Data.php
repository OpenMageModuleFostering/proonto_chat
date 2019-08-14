<?php

class Proonto_Chat_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_USERNAME     = 'proonto_chat/general/username';
    const XML_PATH_PASSWORD     = 'proonto_chat/general/password';
    const XML_PATH_COMPANY_ID   = 'proonto_chat/general/company_id';
    const XML_PATH_ENABLED      = 'proonto_chat/general/enabled';
    const XML_PATH_VERIFIED     = 'proonto_chat/general/verified';

    /**
     * @return bool
     */
    public function getTestMode()
    {
        return isset($_SERVER['PROONTO_CHAT_IS_DEVELOPER_MODE']);
    }

    /**
     * @param mixed $store
     * @return string
     */
    public function getUsername($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_USERNAME, $store);
    }

    /**
     * @param mixed $store
     * @return string
     */
    public function getPassword($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PASSWORD, $store);
    }

    /**
     * @param mixed $store
     * @return string
     */
    public function getCompanyId($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_COMPANY_ID, $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public function getEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED, $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public function getVerified($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_VERIFIED, $store);
    }

    /**
     * @param mixed $website
     * @param string $path
     * @return mixed|string
     */
    public function getWebsiteConfigValue($website, $path)
    {
        /**
         * @var $configDataCollection Mage_Core_Model_Resource_Config_Data_Collection
         * @var $configData Mage_Core_Model_Config_Data
         */

        $configDataCollection = Mage::getResourceModel('core/config_data_collection');
        $configDataCollection
            ->addFieldToFilter('scope', 'websites')
            ->addFieldToFilter('scope_id', (int) Mage::app()->getWebsite($website)->getId())
            ->addFieldToFilter('path', $path);

        if (count($configDataCollection)) {
            $configData = $configDataCollection->getFirstItem();
            return $configData->getValue();
        }

        return Mage::getStoreConfig($path, Mage_Core_Model_App::ADMIN_STORE_ID);
    }
}