<?php

class Proonto_Chat_Model_Observer
{
    const TEST_SERVICE_URL          = 'http://platformstg.proonto.com/platform/rest/magento/company';
    const PRODUCTION_SERVICE_URL    = 'http://platform.proonto.com/platform/rest/magento/company';

    /**
     * @return Proonto_Chat_Helper_Data
     */
    public function getChatHelper()
    {
        return Mage::helper('proonto_chat');
    }

    /**
     * @param $username
     * @param $password
     * @return mixed
     * @throws Mage_Core_Exception
     */
    protected function _signUp($username, $password)
    {
        $serviceUrl = $this->getChatHelper()->getTestMode() ?
            self::TEST_SERVICE_URL : self::PRODUCTION_SERVICE_URL;

        $params = array(
            'username' => $username,
            'password' => $password,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serviceUrl . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Mage_Core_Exception(
                $this->getChatHelper()->__('Sign up request failed.')
            );
        }

        /**
         * @var $coreHelper Mage_Core_Helper_Data
         */
        $coreHelper = Mage::helper('core');
        $result = $coreHelper->jsonDecode($result);
        if (empty($result)) {
            throw new Mage_Core_Exception(
                $this->getChatHelper()->__('Sign up request failed.')
            );
        }

        if (array_key_exists('code', $result)) {
            throw new Mage_Core_Exception(
                $this->getChatHelper()->__(!empty($result['code']) ? $result['code'] : 'Sign up request failed.')
            );
        }

        if (empty($result['id'])) {
            throw new Mage_Core_Exception(
                $this->getChatHelper()->__('Sign up request failed.')
            );
        }

        curl_close($ch);

        return $result['id'];
    }

    /**
     * @param $username
     * @param $password
     * @param $companyId
     * @return bool
     * @throws Mage_Core_Exception
     */
    protected function _verifyAccount($username, $password, $companyId)
    {
        $serviceUrl = $this->getChatHelper()->getTestMode() ?
            self::TEST_SERVICE_URL : self::PRODUCTION_SERVICE_URL;

        $params = array(
            'username' => $username,
            'password' => $password,
            'companyId' => $companyId,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serviceUrl . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Mage_Core_Exception(
                $this->getChatHelper()->__('Verify account request failed.')
            );
        }

        /**
         * @var $coreHelper Mage_Core_Helper_Data
         */
        $coreHelper = Mage::helper('core');
        $result = $coreHelper->jsonDecode($result);
        if (empty($result)) {
            throw new Mage_Core_Exception(
                $this->getChatHelper()->__('Verify account request failed.')
            );
        }

        if (array_key_exists('code', $result)) {
            throw new Mage_Core_Exception(
                $this->getChatHelper()->__(!empty($result['code']) ? $result['code'] : 'Verify account request failed.')
            );
        }

        curl_close($ch);

        return true;
    }

    /**
     * event: admin_system_config_changed_section_proonto_chat
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function configChanged(Varien_Event_Observer $observer)
    {
        /**
         * @var $configDataCollection Mage_Core_Model_Resource_Config_Data_Collection
         * @var $configData Mage_Core_Model_Config_Data
         */

        $section = 'proonto_chat';

        $website = $observer->getEvent()->getData('website');
        $store = $observer->getEvent()->getData('store');

        if (!$website && !$store) {
            $scope = 'default';
            $scopeId = 0;
        } elseif (!$store) {
            $scope = 'websites';
            $scopeId = (int) Mage::app()->getWebsite($website)->getId();
        } else {
            $scope = 'stores';
            $scopeId = (int) Mage::app()->getStore($store)->getId();
        }

        $configDataCollection = Mage::getResourceModel('core/config_data_collection');
        $configDataCollection->addScopeFilter($scope, $scopeId, $section);

        $usedParentValues = false;

        if ($scope != 'default') {

            $paths = array(
                Proonto_Chat_Helper_Data::XML_PATH_USERNAME,
                Proonto_Chat_Helper_Data::XML_PATH_PASSWORD,
                Proonto_Chat_Helper_Data::XML_PATH_COMPANY_ID,
            );

            $usedParentValues = true;
            foreach ($paths as $path) {
                $configData = $configDataCollection->getItemByColumnValue('path', $path);
                if ($configData instanceof Mage_Core_Model_Config_Data) {
                    $usedParentValues = false;
                    break;
                }
            }

            if ($usedParentValues) {
                $configData = $configDataCollection->getItemByColumnValue('path', Proonto_Chat_Helper_Data::XML_PATH_VERIFIED);
                if ($configData instanceof Mage_Core_Model_Config_Data) {
                    $configData->delete();
                }
            }
        }

        if (!$usedParentValues) {

            $configData = $configDataCollection->getItemByColumnValue('path', Proonto_Chat_Helper_Data::XML_PATH_USERNAME);
            $username   = $configData instanceof Mage_Core_Model_Config_Data ? $configData->getValue() : '';
            $configData = $configDataCollection->getItemByColumnValue('path', Proonto_Chat_Helper_Data::XML_PATH_PASSWORD);
            $password   = $configData instanceof Mage_Core_Model_Config_Data ? $configData->getValue() : '';
            $configData = $configDataCollection->getItemByColumnValue('path', Proonto_Chat_Helper_Data::XML_PATH_COMPANY_ID);
            $companyId  = $configData instanceof Mage_Core_Model_Config_Data ? $configData->getValue() : '';

            $verified = 0;

            /**
             * @var $session Mage_Adminhtml_Model_Session
             */
            $session = Mage::getSingleton('adminhtml/session');
            try {
                if ($companyId) {
                    $verified = $this->_verifyAccount($username, $password, $companyId);
                    $session->addSuccess('Your account was successfully verified.');
                } else {
                    $companyId = $this->_signUp($username, $password);
                    $configData = $configDataCollection->getItemByColumnValue('path', Proonto_Chat_Helper_Data::XML_PATH_COMPANY_ID);
                    if (!($configData instanceof Mage_Core_Model_Config_Data)) {
                        $configData = Mage::getModel('core/config_data');
                    }
                    $configData
                        ->setScope($scope)
                        ->setScopeId($scopeId)
                        ->setPath(Proonto_Chat_Helper_Data::XML_PATH_COMPANY_ID)
                        ->setValue($companyId)
                        ->save();
                    $verified = 1;
                    $session->addSuccess($this->getChatHelper()->__('You was successfully signed up.'));
                }
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
                if ($companyId) {
                    $configData = $configDataCollection->getItemByColumnValue('path', Proonto_Chat_Helper_Data::XML_PATH_COMPANY_ID);
                    $configData->setValue('')->save();
                }
            } catch (Exception $e) {
                $session->addError($this->getChatHelper()->__($companyId ? 'Verify account request failed.' : 'Sign up request failed.'));
                if ($companyId) {
                    $configData = $configDataCollection->getItemByColumnValue('path', Proonto_Chat_Helper_Data::XML_PATH_COMPANY_ID);
                    $configData->setValue('')->save();
                }
            }

            $configData = $configDataCollection->getItemByColumnValue('path', Proonto_Chat_Helper_Data::XML_PATH_VERIFIED);
            if (!($configData instanceof Mage_Core_Model_Config_Data)) {
                $configData = Mage::getModel('core/config_data');
            }

            $configData
                ->setScope($scope)
                ->setScopeId($scopeId)
                ->setPath(Proonto_Chat_Helper_Data::XML_PATH_VERIFIED)
                ->setValue($verified)
                ->save();
            ;
        }

        return $this;
    }


    /**
     * event: model_config_data_save_before
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function configDataSaveBefore(Varien_Event_Observer $observer)
    {
        /**
         * @var $configData Mage_Adminhtml_Model_Config_Data
         */
        $configData = $observer->getEvent()->getData('object');

        if ($configData->getSection() == 'proonto_chat') {
            $groups  = $configData->getGroups();

            $inherit = true;

            $fields = array(
                'username',
                'password',
                'company_id',
            );

            foreach ($fields as $field) {
                if (empty($groups['general']['fields'][$field]['inherit'])) {
                    $inherit = false;
                    break;
                }
            }

            if (!$inherit) {
                foreach ($fields as $field) {
                    if (array_key_exists('inherit', $groups['general']['fields'][$field])) {
                        unset($groups['general']['fields'][$field]['inherit']);
                    }
                    $groups['general']['fields'][$field]['value'] = isset($groups['general']['fields'][$field]['value']) ? $groups['general']['fields'][$field]['value'] : '';
                }
            }

            $configData->setGroups($groups);
        }

        return $this;
    }

    /**
     * event: checkout_onepage_controller_success_action
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function checkoutOnepageSuccess(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getData('order_ids');
        Mage::register('proonto_chat_tracking_conversion_order_ids', $orderIds);
        return $this;
    }

    /**
     * event: checkout_multishipping_controller_success_action
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function checkoutMultishippingSuccess(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getData('order_ids');
        Mage::register('proonto_chat_tracking_conversion_order_ids', $orderIds);
        return $this;
    }
}