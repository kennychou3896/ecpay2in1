<?php

namespace kennychou3896\Ecpay2in1;

use kennychou3896\Ecpay2in1\EcpayFactory;

class Ecpay
{

    private $instance = null;

    //--------------------------------------------------------

    public function __construct()
    {
        $this->instance = new EcpayFactory();
        require(__DIR__.'/lib/ECPay.Logistics.Integration.php');
        $this->logistics = new \ECPayLogistics();
      //  $this->logistics->ServiceURL = config('ecpay.ServiceURL');
        $this->logistics->Send['HashKey']    = config('ecpay.HashKey');
        $this->logistics->Send['HashIV']     = config('ecpay.HashIV');
        $this->logistics->Send['MerchantID'] = config('ecpay.MerchantID');
        env('APP_PAY_TEST')? $this->setForTest(): $this->setForProd();

    }

    public function instance()
    {
        return $this->instance;
    }

    public function i()
    {
        return $this->instance;
    }
    public function logistics()
    {
        return $this->logistics;
    }

    public function l()
    {
        return $this->logistics;
    }
    private function setForTest()
    {
        $this->instance->ServiceURL = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V2';
        $this->instance->HashKey = '5294y06JbISpM5x9';
        $this->instance->HashIV = 'v77hoKGq4kWxNNIS';
        $this->instance->MerchantID = '2000132';      
    }

    private function setForProd()
    {
        $this->instance->ServiceURL = config('ecpay.ServiceURL');
        $this->instance->HashKey = config('ecpay.HashKey');
        $this->instance->HashIV = config('ecpay.HashIV');
        $this->instance->MerchantID = config('ecpay.MerchantID');     
    }
}
