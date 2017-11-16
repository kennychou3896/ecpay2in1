## ecpay2in1

*整合綠界線上刷卡及超商物流*

***適用對象:以Laravel 5開發商務網站，欲使用綠界線上金流、物流服務。***

***實作版本：Laravel 5.2***


**step 1 : Download the package**

	composer命令安裝	
	
	composer require kennychou3896/ecpay2in1 dev-master
	
	或者是新增package至composer.json
	
	"require": {
	  "kennychou3896/ecpay2in1": "dev-master"
	},
	
	然後更新安裝
	
	composer update
	
	或全新安裝
	
	composer install


**step 2 : Modify config file**

	增加`config/app.php`中的`providers`和`aliases`的參數 。
	
	'providers' => [ // ... kennychou3896\ecpay2in1\EcpayServiceProvider::class, ]
	
	'aliases' => [ // ... 'Ecpay' => kennychou3896\ecpay2in1\Facade\Ecpay::class, ]


**step 3 : Publish config to your project**

	執行下列命令，將package的config檔配置到你的專案中
	
	php artisan vendor:publish

	至config/ecpay.php中確認Ecpay設定：

    return [
        'ServiceURL' => 'http://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V2',    
        'HashKey'    => '5294y06JbISpM5x9',    //這是綠界給的test Key ，正式上線由此抽換為你的Key
        'HashIV'     => 'v77hoKGq4kWxNNIS',    
        'MerchantID' => '2000132',    
    ];
    
**step 4 : .env中設定參數(非必須)**

	#付款測試 true : 直接使用測試的特店參數, false : 使用config/ecpay.php中的參數.
	APP_PAY_TEST=true	
	PAY_SERVICE_URL=https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V2	
	PAY_HASH_KEY=5294y06JbISpM5x9	
	PAY_HASH_IV=v77hoKGq4kWxNNIS	
	PAY_MERCHANT_ID=2000132

**How To Use -->線上刷卡篇**

	在Controller中
      
    use Ecpay; 
    public function Demo()
    {   
          
      //基本參數(可依系統規劃自行調整)
      Ecpay::i()->Send['ReturnURL']         = "http://www.yourwebsites.com.tw/ReturnURL" ; 
                                            //交易結果回報的網址
      Ecpay::i()->Send['ClientBackURL']     = "http://www.yourwebsites.com.tw/ClientBackURL" ; 
                                            //交易結束，讓user導回的網址
      Ecpay::i()->Send['MerchantTradeNo']   = "Test".time() ;           //訂單編號
      Ecpay::i()->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');      //交易時間
      Ecpay::i()->Send['TotalAmount']       = 2000;                     //交易金額
      Ecpay::i()->Send['TradeDesc']         = "good to drink" ;         //交易描述
      Ecpay::i()->Send['EncryptType']      = '1' ;  
      Ecpay::i()->Send['ChoosePayment']     = "Credit" ;     //付款方式:信用卡
      Ecpay::i()->Send['PaymentType']        = 'aio' ;

      //訂單的商品資料
      array_push(Ecpay::i()->Send['Items'], 
              array('Name' => "美美小包包", 
              'Price' => (int)"2000",'Currency' => "元", 
              'Quantity' => (int) "1", 
              'URL' => "http://www.yourwebsites.com.tw/Product"));

      //Go to EcPay    
      echo "線上刷卡頁面導向中...";    
      echo Ecpay::i()->CheckOutForm();
    
      //開發階段，如果你希望看到表單的內容，可以改為以下敘述：   
      //echo Ecpay::i()->CheckOutForm('按我，才送出');
    
    }

**超商物流篇--到店付**
    
    1.選擇『到付店』：
      Ecpay::l()->Send['MerchantTradeNo'] = 'Test-'.date('YmdHis');
      Ecpay::l()->Send['LogisticsSubType'] = 'UNIMARTC2C'; //或FAMIC2C,全家
      Ecpay::l()->Send['IsCollection'] = 'N';//是否代收貨款
      Ecpay::l()->Send['ServerReplyURL'] = url('shop_option_reply'); //超商系統回覆路徑post
      Ecpay::l()->Send['ExtraData'] = ''; //附帶資料
      Ecpay::l()->Send['Device'] = '0';		
      $logisticsForm = Ecpay::l()->CvsMap();
      echo $logisticsForm;
    
    2.取得『到付店』之回覆資訊：
      $data = array();		
      $data['merchant_trade_no'] = $request->input('MerchantTradeNo'); //訂單編號
      $data['LogisticsSubType'] = $request->input('LogisticsSubType'); //物流通路代碼,如統一:UNIMART
      $data['CVSStoreID'] = $request->input('CVSStoreID');//商店代碼
      $data['CVSStoreName'] = $request->input('CVSStoreName');
      $data['CVSAddress'] = $request->input('CVSAddress');//User 所選之超商店舖地址
      $data['CVSTelephone'] = $request->input('CVSTelephone');//User 所選之超商店舖電話
      $data['ExtraData'] = $request->input('ExtraData');//額外資訊,原資料回傳
    
    3.產生『到付店』托運單：
    
    //背景建立店到付物流單
    	try {
			$AL = Ecpay::l();
	  		$AL->HashKey = config('ecpay.HashKey');
	  		$AL->HashIV = config('ecpay.HashIV');
	    	$AL->Send = array(
	            'MerchantID' => config('ecpay.MerchantID'),
	            'MerchantTradeNo' => 'mic-' . date('YmdHis'),
	            'MerchantTradeDate' => date('Y/m/d H:i:s'),
	            'LogisticsType' => 'CVS',
	            'LogisticsSubType' => 'UNIMARTC2C',
	            'GoodsAmount' => 100,
	            'CollectionAmount' => 100,
	            'IsCollection' => 'Y',    //是否代收貨款
	            'GoodsName' => '商品名稱',
	            'SenderName' => '李小華',
	            'SenderPhone' => '0226550115',
	            'SenderCellPhone' => '0911222333',
	            'ReceiverName' => '周大大',
	            'ReceiverPhone' => '0233881234',
	            'ReceiverCellPhone' => '0912555666',
	            'ReceiverEmail' => 'user@email.com',
	            'TradeDesc' => '測試交易敘述',
	            'ServerReplyURL' => url('logistics_order_reply'),        //物流狀態回覆網址
	            'LogisticsC2CReplyURL' => url('logistics_order_C2C_reply'),    //到付店若有異動訊息回覆網址
	            'Remark' => '測試備註',
	            'PlatformID' => '',
	        );
	        $AL->SendExtend = array(
	             'ReceiverStoreID' => '136392',     //到付店id
	             'ReturnStoreID' => '991182'        //回退店id,一般與寄件店id同
	        );
			$Result = $AL->BGCreateShippingOrder();   //超商系統回覆內容
			echo '<pre>' . print_r($Result, true) . '</pre>';          
          	if($Result['RtnCode'] == 300){
            		//托運單成功建立

          	}
		} catch(Exception $e) {
			$Result = $e->getMessage();
          	echo $e->getMessage();
    	} 
    3.1 取消『到付店』托運單(僅統一超商)：
        // 取消物流單(統一超商C2C)
        $ships['AllPayLogisticsID'] = '15474';	//綠界物流編號
        $ships['CVSPaymentNo']='F0015091';	//統一超商寄貨單號
        $ships['CVSValidationNo']='3207';	//驗證碼
        try {
          $AL = Ecpay::l();
          $AL->HashKey = config('ecpay.HashKey');
          $AL->HashIV = config('ecpay.HashIV');
          $AL->Send = array(
            'MerchantID' => config('ecpay.MerchantID'),
            'AllPayLogisticsID' => $ships['AllPayLogisticsID'],     //綠界物流編號
            'CVSPaymentNo' => $ships['CVSPaymentNo'],        		//統一超商寄貨單號
            'CVSValidationNo' => $ships['CVSValidationNo'],         //驗證碼
            'PlatformID' => ''
          );
          $Result = $AL->CancelUnimartLogisticsOrder();
          //	echo '<pre>' . print_r($Result, true) . '</pre>';

         } catch(Exception $e) {
            $Result = $e->getMessage();
            echo $e->getMessage();
         } 
        
    4.列印『到付店』托運＆繳款單：
      //統一超商
			try {
		        $AL = Ecpay::l();
		        $AL->HashKey = config('ecpay.HashKey');
		        $AL->HashIV = config('ecpay.HashIV');
		        $AL->Send = array(
		            'MerchantID' => config('ecpay.MerchantID'),
		            'AllPayLogisticsID' => $Result['AllPayLogisticsID'],
		            'CVSPaymentNo' => $Result['CVSPaymentNo'],
		            'CVSValidationNo' => $Result['CVSValidationNo'],
		            'PlatformID' => ''
		        );
		        // PrintUnimartC2CBill(Button名稱, Form target)
		        $html = $AL->PrintUnimartC2CBill();  //'列印繳款單(統一超商C2C)'
		        echo $html;
		    } catch(Exception $e) {
		        echo $e->getMessage();
		    }
        //全家
        try {
              $AL = Ecpay::l();
              $AL->HashKey = config('ecpay.HashKey');
              $AL->HashIV = config('ecpay.HashIV');
              $AL->Send = array(
                  'MerchantID' => config('ecpay.MerchantID'),
                  'AllPayLogisticsID' => $Result['AllPayLogisticsID'],
                  'CVSPaymentNo' => $Result['CVSPaymentNo'],
                  'PlatformID' => ''
              );
              // PrintFamilyC2CBill(Button名稱, Form target)
              $html = $AL->PrintFamilyC2CBill(); //'全家列印小白單(全家超商C2C)'
              echo $html;  
          } catch(Exception $e) {
              echo $e->getMessage();
          }
    
