<?php
defined ( 'BASEPATH' ) OR exit ( 'No direct script access allowed' );

class Pay2go_api
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
	
	// 時間戳記
	private $TimeStamp;
	
	// 串接程式版本
	private $Version;
	
	// 串接表單資料
	private $pi_pk;
	private $post_data;
	private $post_data_str;
	private $format_data;
	private $format_data_str;
	
	// 訂單序號
	private $MerchantOrderNo;
	
	// 發票號碼
	private $InvoiceNumber;
	
	// 回傳用
	private $data_result;
	public function __construct() {
		$this->CI = & get_instance ();
		$this->CI->config->load('vidol');
		$this->CI->config->load ( 'pay2go' );
		$this->server_domain = $this->CI->config->item ( 'server_domain' );
		$data_config = $this->CI->config->item ( 'pay2go' );
		$this->api_domain = $data_config ['api_domain'];
		$this->MerchantID = $data_config ['MerchantID'];
		$this->HashKey = $data_config ['HashKey'];
		$this->HashIV = $data_config ['HashIV'];
	}
	public function debug() {
		echo "<br/>\n", "api_domain : ", $this->api_domain, "<br/>\n";
		echo "<br/>\n", "MerchantID : ", $this->MerchantID, "<br/>\n";
		echo "<br/>\n", "HashKey : ", $this->HashKey, "<br/>\n";
		echo "<br/>\n", "HashIV : ", $this->HashIV, "<br/>\n";
		echo "<br/>\n", "<br/>\n";
	}
	/**
	 * 開立發票資料整理(一)
	 * 
	 * @param unknown $date_invoice
	 *        	資料庫開立發票資料
	 */
	public function format_invoice($date_invoice) {
		$this->TimeStamp = time ();
		$this->format_data = array (
				"RespondType" => "JSON",
				"Version" => "1.3",
				"TimeStamp" => $this->TimeStamp,
				"TransNum" => "",
				"MerchantOrderNo" => "",
				"Status" => "1",
				// "CreateStatusTime" => "",
				"Category" => "",
				"BuyerName" => "",
				"BuyerUBN" => "",
				"BuyerAddress" => "",
				"BuyerEmail" => "",
				"BuyerPhone" => "",
				"CarrierType" => "",
				"CarrierNum" => "",
				"LoveCode" => "",
				"PrintFlag" => "",
				"TaxType" => "",
				"TaxRate" => "",
				"Amt" => "",
				"TaxAmt" => "",
				"TotalAmt" => "",
				"ItemName" => "",
				"ItemCount" => "",
				"ItemUnit" => "",
				"ItemPrice" => "",
				"ItemAmt" => "",
				"Comment" => "" 
		);
		if (count ( $date_invoice ) > 0) {
			// 保留資料主鍵更新使用
			$this->pi_pk = $date_invoice->pi_pk;
			$this->MerchantOrderNo = $date_invoice->MerchantOrderNo;
			foreach ( $date_invoice as $field => $value ) {
				if (! empty ( $value ) && ! in_array ( $field, array (
						'pi_pk',
						'Status',
						'CreateStatusTime' 
				) )) {
					$this->format_data [$field] = $value;
				}
			}
		}
		// 直接開立
		$this->format_data_str = http_build_query ( $this->format_data );
	}
	public function spgateway_encrypt() {
		$str = trim ( bin2hex ( mcrypt_encrypt ( MCRYPT_RIJNDAEL_128, $this->HashKey, $this->addpadding ( $this->format_data_str ), MCRYPT_MODE_CBC, $this->HashIV ) ) );
		return $str;
	}
	public function addpadding($string, $blocksize = 32) {
		$len = strlen ( $string );
		$pad = $blocksize - ($len % $blocksize);
		$string .= str_repeat ( chr ( $pad ), $pad );
		return $string;
	}
	/**
	 * CURL開立發票
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
	/**
	 * 更新資料回去
	 * @param unknown $result	開立發票回傳資料
	 * @return mixed
	 */
	public function update_database($result) {
		$update_data = array ();
		$web_info = json_decode ( $result ['web_info'] );
		(isset ( $web_info->Status )) ? $update_data ['Result_Status'] = $web_info->Status : null;
		(isset ( $web_info->Message )) ? $update_data ['Result_Message'] = $web_info->Message : null;
		if (! empty ( $web_info->Result )) {
			$Result = json_decode ( $web_info->Result );
			$web_info->Result = $Result;
			if (isset ( $Result->InvoiceNumber )) {
				
				$update_data ['Result_InvoiceNumber'] = $Result->InvoiceNumber;
				//
				$this->CI->load->model ( 'vidol_billing/orders_model' );
				$this->CI->load->model ( 'vidol_billing/order_cashs_model' );
				$this->CI->orders_model->update_Orders_Invoice_by_order_sn ( $this->MerchantOrderNo, $Result->InvoiceNumber );
				$this->CI->order_cashs_model->update_Order_cashs_Invoice_by_order_sn ( $this->MerchantOrderNo, $Result->InvoiceNumber );
			}
			(isset ( $Result->RandomNum )) ? $update_data ['Result_RandomNum'] = $Result->RandomNum : null;
			(isset ( $Result->CreateTime )) ? $update_data ['Result_CreateTime'] = $Result->CreateTime : null;
			(isset ( $Result->CheckCode )) ? $update_data ['Result_CheckCode'] = $Result->CheckCode : null;
			(isset ( $Result->BarCode )) ? $update_data ['Result_BarCode'] = $Result->BarCode : null;
			(isset ( $Result->QRcodeL )) ? $update_data ['Result_QRcodeL'] = $Result->QRcodeL : null;
			(isset ( $Result->QRcodeR )) ? $update_data ['Result_QRcodeR'] = $Result->QRcodeR : null;
		}
		$this->CI->load->model ( 'vidol_billing/pay2goInvoice_model' );
		$this->CI->pay2goInvoice_model->update_Pay2goInvoice_for_data ( $this->pi_pk, $update_data );
		return $web_info;
	}
	/**
	 * 開立發票
	 * @param unknown $date_invoice		開立發票資料
	 * @return mixed
	 */
	public function send_invoice($date_invoice) {
		// 開立發票API位置
		$this->api_url = sprintf ( 'https://%s/API/invoice_issue', $this->api_domain );
		// 開立發票資料整理(一)
		$this->format_invoice ( $date_invoice );
		//print_r($this->data_result);
		//print_r($this->format_data);
		//print_r($this->format_data_str);
		//$this->data_result ['format_data'] = $this->format_data;
		//$this->data_result ['format_data_str'] = $this->format_data_str;
		// 開立發票資料整理(二)
		$PostData = $this->spgateway_encrypt();
		//$PostData = trim ( bin2hex ( mcrypt_encrypt ( MCRYPT_RIJNDAEL_128, $this->HashKey, $this->addpadding ( $this->format_data_str ), MCRYPT_MODE_CBC, $this->HashIV ) ) );
		$this->post_data = array ( // 送出欄位
				"MerchantID_" => $this->MerchantID,
				"PostData_" => $PostData 
		);
		$this->post_data_str = http_build_query ( $this->post_data );
		// CURL開立發票
		$result = $this->curl_work ( $this->api_url, $this->post_data_str );
		// 更新資料回去
		$this->data_result = $this->update_database ( $result );
		return $this->data_result;
	}
}

/* End of file Pay2go_api.php */
/* Location: ./application/library/vidol_billing/Pay2go_api.php */