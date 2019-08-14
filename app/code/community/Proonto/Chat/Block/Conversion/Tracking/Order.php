<?php

class Proonto_Chat_Block_Conversion_Tracking_Order extends Proonto_Chat_Block_Abstract
{

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrders()
    {
        if (!$this->hasData('orders')) {
            $orderIds = Mage::registry('proonto_chat_tracking_conversion_order_ids');
            /**
             * @var $collection Mage_Sales_Model_Resource_Order_Collection
             */
            $collection = Mage::getResourceModel('sales/order_collection');
            $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), array('in' => $orderIds));
            $this->setData('orders', $collection->getItems());
        }
        return $this->_getData('orders');
    }

    /**
     * return empty string if orders not found
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOrders()) {
            return '';
        }

        return parent::_toHtml();
    }
}