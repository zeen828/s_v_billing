<?php
defined ( 'BASEPATH' ) OR exit ( 'No direct script access allowed' );

class Spgateway_api
{
	// CI繼承
	private $CI;
	
	// SERVER網域
	private $server_domain;
	
	// API網域
	private $api_domain;
	
	// API網址
	private $api_url;
	
	// API串接金鑰
	private $HashKey;
	private $HashIV;
	
	// 支付方式
	private $PaymentType;
	
	// 商店代號
	private $MerchantID;
	
	// 訂單時間戳記
	private $TimeStamp;
	
	// 串接程式版本
	private $Version;
	
	// 商店訂單編號
	private $MerchantOrderNo;
	
	// 訂單金額
	private $Amt;
	
	// 商品資訊
	private $ItemDesc;
	private $ProdDesc;
	
	// 續扣(自動扣)
	private $rs;
	
	// 付款人電子信箱
	private $Email;
	
	//
	private $PayerEmail;
	
	// 續扣Token
	private $TokenTerm;
	private $TokenValue;
	
	// 約定事項
	private $OrderComment;
	
	// 回傳網址
	private $ReturnURL;
	
	// 金流狀態回傳位置
	private $NotifyURL;
	
	// 串接表單資料
	private $post_data;
	
	// 回傳用
	private $data_result;
	
	public function __construct ()
	{
		$this->CI = & get_instance();
		$this->CI->config->load('vidol');
		$this->CI->config->load('spgateway');
		$this->server_domain = $this->CI->config->item ( 'server_domain' );
		$data_config = $this->CI->config->item('spgateway');
		$this->api_domain = $data_config['api_domain'];
		$this->MerchantID = $data_config['MerchantID'];
		$this->HashKey = $data_config['HashKey'];
		$this->HashIV = $data_config['HashIV'];
		//$this->NotifyURL = $data_config['Result_URL'];
		$this->NotifyURL = sprintf('http://%s/v1/billing/receptions/spgateway', $this->server_domain, $data_config['Result_URL']);
		$this->NotifyURL = sprintf('http://%s%s', $this->server_domain, $data_config['Result_URL']);
	}

	public function debug ()
	{
		echo "<br/>\n", "api_url : ", $this->api_url, "<br/>\n";
		echo "<br/>\n", "api_domain : ", $this->api_domain, "<br/>\n";
		echo "<br/>\n", "HashKey : ", $this->HashKey, "<br/>\n";
		echo "<br/>\n", "HashIV : ", $this->HashIV, "<br/>\n";
		echo "<br/>\n", "<br/>\n";
	}
	
	/**
	 * MPG技術串接手冊,智付通檢查碼(文件附件二)
	 * 約定信用卡付款授權技術串接手冊,CheckValue組合及加密方法(附件一)
	 * @return string
	 */
	public function CheckValue()
	{
		$data_check = array(
				'HashKey' => $this->HashKey,
				'Amt' => $this->Amt,
				'MerchantID' => $this->MerchantID,
				'MerchantOrderNo' => $this->MerchantOrderNo,
				'TimeStamp' => $this->TimeStamp,
				'Version' => $this->Version,
				'HashIV' => $this->HashIV,
		);
		$CheckValue = strtoupper(hash('sha256', http_build_query($data_check)));
		return $CheckValue;
	}
	
	/**
	 * MPG技術串接手冊,智付通檢核碼(文件附件三)
	 * 約定信用卡付款授權技術串接手冊,CheckValue產生規則(附件二)
	 * @return string
	 */
	public function CheckCode($Amt, $MerchantOrderNo, $TradeNo)
	{
		$data_check = array(
				'HashIV' => $this->HashIV,
				'Amt' => $Amt,
				'MerchantID' => $this->MerchantID,
				'MerchantOrderNo' => $MerchantOrderNo,
				'TradeNo' => $TradeNo,
				'HashKey' => $this->HashKey,
		);
		$CheckValue = strtoupper(hash('sha256', http_build_query($data_check)));
		return $CheckValue;
	}
	
	//約定信用卡付款授權技術串接手冊,PostData_加密方法(附件三)
	public function CreditCard_format_data()
	{
		$this->post_data = array(
				'TimeStamp' => $this->TimeStamp,
				'Version' => $this->Version,
				'MerchantOrderNo' => $this->MerchantOrderNo,
				'Amt' => $this->Amt,
				'ProdDesc' => $this->ItemDesc,
				'PayerEmail' => $this->Email,
				'TokenValue' => $row->oc_TokenValue,
				'TokenTerm' => $row->oc_TokenLife,
				'TokenSwitch' => 'on',
		);
		$CheckValue = strtoupper(hash('sha256', http_build_query($data_check)));
		return $CheckValue;
	}
	public function spgateway_encrypt() {
		$str = trim ( bin2hex ( mcrypt_encrypt ( MCRYPT_RIJNDAEL_128, $this->HashKey, $this->addpadding ( $this->format_data_str ), MCRYPT_MODE_CBC, $this->HashIV ) ) );
		return $str;
	}
	public function addpadding($string, $blocksize = 32) {
		$len = strlen ( $string );
		$pad = $blocksize - ( $len % $blocksize );
		$string .= str_repeat ( chr ( $pad ), $pad );
		return $string;
	}
	
	/**
	 * CURL
	 * @param string $url
	 * @param string $parameter
	 * @return string[]|mixed[]|unknown[]
	 */
	public function curl_work($url = "", $parameter = "") {
		$curl_options = array (
				CURLOPT_URL => $url,
				CURLOPT_HEADER => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => "Google Bot",
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYPEER => FALSE,
				CURLOPT_SSL_VERIFYHOST => FALSE,
				CURLOPT_POST => "1",
				CURLOPT_POSTFIELDS => $parameter
		);
		$ch = curl_init ();
		curl_setopt_array ( $ch, $curl_options );
		$result = curl_exec ( $ch );
		$retcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		$curl_error = curl_errno ( $ch );
		curl_close ( $ch );
		$return_info = array (
				"url" => $url,
				"sent_parameter" => $parameter,
				"http_status" => $retcode,
				"curl_error_no" => $curl_error,
				"web_info" => $result
		);
		return $return_info;
	}
	
	public function CreditCard($MerchantOrderNo, $Amt, $ProdDesc, $PayerEmail, $TokenValue, $TokenTerm)
	{
		$input_array = array(
				'TimeStamp' => time(),
				'Version' => '1.0',
				'MerchantOrderNo' => $order['order_sn'],
				'Amt' => $order['price'],
				'ProdDesc' => $row_cashs->oc_package_title,
				'PayerEmail' => $row_spgateway->Email,
				'TokenValue' => $row_spgateway->R_TokenValue,
				'TokenTerm' => $row_spgateway->TokenTerm,
				'TokenSwitch' => 'on',
		);
		//API網址
		$this->api_url = sprintf('https://%s/MPG/mpg_gateway', $this->api_domain);
		//資料處理

		$this->TimeStamp = time();
		$this->Version = '1.0';
		$this->MerchantOrderNo = $MerchantOrderNo;
		$this->Amt = $Amt;
		$this->ProdDesc = $ProdDesc;//
		$this->PayerEmail = $PayerEmail;
		$this->TokenValue = $TokenValue;
		$this->TokenTerm = $TokenTerm;
		

		//API接收表單資料
		$this->mpg_gateway_format_data();
		//紀錄
		$this->CI->load->model ( 'vidol_billing/spgatewayResponse_model' );
		$this->CI->spgatewayResponse_model->insert_SpgatewayResponse_for_data($this->post_data);
		//CI表單協助
		$from_html_start = form_open($this->api_url, array('class' => 'spgateway', 'id' => 'spgateway_form'), $this->post_data);
		$from_html_end = form_close();
		$auth_send_from = '<script type="text/javascript">spgateway_form.submit();</script>';
		return $from_html_start . $from_html_end . $auth_send_from;//自動發送表單
		//return $from_html_start . $from_html_end;//表單
		
		
	}
	
	/**
	 * 呼叫智付通金流使用表單資料整理
	 */
	public function mpg_gateway_format_data()
	{
		$this->post_data = array(
				'MerchantID' => $this->MerchantID,
				'RespondType' => 'JSON',
				'CheckValue' => '',
				'TimeStamp' => $this->TimeStamp,
				'Version' => $this->Version,
				'LangType' => 'zh-tw',
				'MerchantOrderNo' => $this->MerchantOrderNo,
				'Amt' => $this->Amt,
				'ItemDesc' => $this->ItemDesc,
				'ReturnURL' => $this->ReturnURL,
				//'ReturnURL' => '#',
				'NotifyURL' => $this->NotifyURL,
				'ClientBackURL' => $this->ReturnURL,
				//'ClientBackURL' => '#',
				'Email' => $this->Email,
				'EmailModify' => '0',
				'LoginType' => '0',
				'OrderComment' => $this->OrderComment,
		);
		switch ($this->PaymentType) {
			default :
			case 'CREDIT' :
				if($this->rs == 1){
					//約定信用卡,續扣(自動扣)
					$this->Version = '1.1';
					$this->post_data['Version'] = $this->Version;
					$this->post_data['OrderComment'] = sprintf('[約定信用卡付款授權]:%s', $this->OrderComment);
					$this->post_data['CREDITAGREEMENT'] = '1';
					$this->post_data['TokenTerm'] = $this->Email;
					//$this->post_data['TokenLife'] = date('ym', strtotime('+1 month'));
					$this->post_data['CheckValue'] = $this->CheckValue();
				}else{
					//一般信用卡
					$this->Version = '1.2';
					$this->post_data['Version'] = $this->Version;
					$this->post_data['OrderComment'] = sprintf('[信用卡付款]:%s', $this->OrderComment);
					$this->post_data['CREDIT'] = '1';
					$this->post_data['InstFlag'] = 0;
					$this->post_data['CheckValue'] = $this->CheckValue();
				}
				break;
			case 'WEBATM' :
				$this->Version = '1.2';
				$this->post_data['Version'] = $this->Version;
				$this->post_data['OrderComment'] = sprintf('[WEBATM]:%s', $this->OrderComment);
				$this->post_data['WEBATM'] = '1';
				$this->post_data['CheckValue'] = $this->CheckValue();
				break;
			case 'VACC' :
				$this->Version = '1.2';
				$this->post_data['Version'] = $this->Version;
				$this->post_data['OrderComment'] = sprintf('[ATM轉帳]:%s', $this->OrderComment);
				$this->post_data['VACC'] = '1';
				$this->post_data['CheckValue'] = $this->CheckValue();
				break;
			case 'CVS' :
				$this->Version = '1.2';
				$this->post_data['Version'] = $this->Version;
				$this->post_data['OrderComment'] = sprintf('[超商代碼]:%s', $this->OrderComment);
				$this->post_data['CVS'] = '1';
				$this->post_data['CheckValue'] = $this->CheckValue();
				break;
			case 'BARCODE' :
				$this->Version = '1.2';
				$this->post_data['Version'] = $this->Version;
				$this->post_data['OrderComment'] = sprintf('[條碼]:%s', $this->OrderComment);
				$this->post_data['BARCODE'] = '1';
				$this->post_data['CheckValue'] = $this->CheckValue();
				break;
		}
	}
	
	/**
	 * 建立金流連線
	 * @param unknown $PaymentType			付款類型(CREDIT:信用卡,WEBATM:WEBATM,VACC:ATM轉帳,CVS:超商代碼,BARCODE:條碼)
	 * @param unknown $MerchantOrderNo		訂單序號
	 * @param unknown $Amt					訂單價錢
	 * @param unknown $ItemDesc				商品資訊
	 * @param unknown $rs					續扣(自動扣)
	 * @param unknown $Email				付款人電子信箱
	 * @param unknown $OrderComment			約定事項
	 * @return string
	 */
	public function mpg_gateway($PaymentType, $MerchantOrderNo, $Amt, $ItemDesc, $rs, $Email, $OrderComment, $return_url)
	{
		//建立表單
		$this->CI->load->helper('form');
		//API網址
		$this->api_url = sprintf('https://%s/MPG/mpg_gateway', $this->api_domain);
		//資料處理
		$this->PaymentType = $PaymentType;
		$this->TimeStamp = time();
		$this->Version = '1.1';
		$this->MerchantOrderNo = $MerchantOrderNo;
		$this->Amt = $Amt;
		$this->ItemDesc = $ItemDesc;
		$this->rs = $rs;//續扣(自動扣)
		$this->Email = $Email;
		$this->OrderComment = $OrderComment;
		$this->ReturnURL = $return_url;
		//API接收表單資料
		$this->mpg_gateway_format_data();
		//紀錄
		$this->CI->load->model ( 'vidol_billing/spgatewayResponse_model' );
		$this->CI->spgatewayResponse_model->insert_SpgatewayResponse_for_data($this->post_data);
		//CI表單協助
		$from_html_start = form_open($this->api_url, array('class' => 'spgateway', 'id' => 'spgateway_form'), $this->post_data);
		$from_html_end = form_close();
		$auth_send_from = '<script type="text/javascript">spgateway_form.submit();</script>';
		return $from_html_start . $from_html_end . $auth_send_from;//自動發送表單
		//return $from_html_start . $from_html_end;//表單
	}
}

/* End of file Spgateway_api.php */
/* Location: ./application/library/vidol_billing/Spgateway_api.php */