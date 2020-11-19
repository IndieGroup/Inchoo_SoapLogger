<?php
/**
 * Inchoo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file if you wish to upgrade
 * Magento or this extension to newer versions in the future.
 ** Inchoo *give their best to conform to
 * "non-obtrusive, best Magento practices" style of coding.
 * However,* Inchoo *guarantee functional accuracy of
 * specific extension behavior. Additionally we take no responsibility
 * for any possible issue(s) resulting from extension usage.
 * We reserve the full right not to provide any kind of support for our free extensions.
 * Thank you for your understanding.
 *
 * @category Inchoo
 * @package SoapLogger
 * @author Marko Martinović <marko.martinovic@inchoo.net>
 * @copyright Copyright (c) Inchoo (http://inchoo.net/)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

class Inchoo_SoapLogger_Model_Api_Server_Handler
    extends Mage_Api_Model_Server_Handler
{
    /**
     * Logs V1 API call
     *
     * @param string $sessionId
     * @param string $apiPath
     * @param array $args
     * @return mixed Null or whatever API call method returns
     */
    public function call($sessionId, $apiPath, $args = array())
    {
        Mage::helper('inchoo_soaplogger/v1')
            ->logPostXml();

        if ($apiPath == 'sales_order.list') {
            if (isset($args[0]['main_table.updated_at']['from'])) {
                // There's an order before this time that causes the SendCloud app to flip the hell out!
                $thresholdDateString = '2020-11-18 12:00:00';
                $incomingDateString = $args[0]['main_table.updated_at']['from'];

                $format = "Y-m-d H:i:s";
                $incomingDate = \DateTime::createFromFormat($format, $incomingDateString);
                $thresholdDate = \DateTime::createFromFormat($format, $thresholdDateString);
                if ($incomingDate < $thresholdDate) {
                    $args[0]['main_table.updated_at']['from'] = $thresholdDateString;
                    Mage::log(
                        "Changed incoming 'updated_at' date filter '$incomingDateString' to '$thresholdDateString' for API call '$apiPath'.",
                        ZEND_LOG::WARN,
                        'sendcloud_debug.log'
                    );
                }
            }
        }

        return parent::call($sessionId, $apiPath, $args);
    }

    /**
     * Logs V1 API fault
     *
     * @param string $faultName
     * @param string $resourceName
     * @param string $customMessage
     */
    protected function _fault($faultName, $resourceName = null, $customMessage = null)
    {
        Mage::helper('inchoo_soaplogger/v1')
            ->logMessage('Fault while processing API call: ' . $faultName);

        parent::_fault($faultName, $resourceName, $customMessage);
    }

}