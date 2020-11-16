<?php

class Inchoo_SoapLogger_Model_Api_Server_Adapter_Xmlrpc extends Mage_Api_Model_Server_Adapter_Xmlrpc
{

    /**
     * Run webservice
     *
     * @return Mage_Api_Model_Server_Adapter_Xmlrpc
     */
    public function run()
    {
        $result = parent::run();
        Mage::log($result->getController()->getResponse()->getBody(), null, 'xmlrpc.log');

        return $result;
    }
}
