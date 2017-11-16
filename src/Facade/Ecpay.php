<?php

namespace kennychou3896\Ecpay2in1\Facade;

use Illuminate\Support\Facades\Facade;

class Ecpay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ecpay';
    }
}
