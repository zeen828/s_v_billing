<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Pay2go extends MY_REST_Controller {
	private $data_debug;
	private $data_result;
	public function __construct() {
		parent::__construct ();
		$this->_my_logs_start = true;
		$this->_my_logs_type = 'billing';
		$this->data_debug = true;
		// 資料庫
		// $this->load->database ( 'vidol_billing_write' );
		// 效能檢查
		// $this->output->enable_profiler(TRUE);
	}
	public function __destruct() {
		parent::__destruct ();
		unset($this->data_debug);
		unset($this->data_result);
	}
	public function ___index_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			//
			$this->load->helper ( 'pay2go' );
			//
			$post_data_array = array (
					"RespondType" => "JSON",
					"Version" => "1.3",
					"TimeStamp" => time (),
					// "TransNum" => "",
					"MerchantOrderNo" => "00201611140000000012",
					"Status" => "1",
					// "CreateStatusTime" => "",
					"Category" => "B2C",
					"BuyerName" => "購買者",
					// "BuyerUBN" => "",
					// "BuyerAddress" => "舊宗路",
					"BuyerEmail" => "zeren828@gmail.com",
					// "BuyerPhone" => "09123456789",
					// "CarrierType" => "",
					// "CarrierNum" => rawurlencode ( "" ),
					// "LoveCode" => "",
					"PrintFlag" => "Y",
					"TaxType" => "1",
					"TaxRate" => "5",
					"Amt" => "161",
					"TaxAmt" => "8",
					"TotalAmt" => "169",
					"ItemName" => "一個月VIP",
					"ItemCount" => "1",
					"ItemUnit" => "個",
					"ItemPrice" => "169",
					"ItemAmt" => "169" 
			);
			// "Comment" => "TEST，備註說明",
			
			$post_data_str = http_build_query ( $post_data_array );
			$this->data_result ['post_data_str'] = $post_data_str;
			$key = "YGviml6QpIMSy70awDgeke4e20iAKtyI"; // 商店專屬串接金鑰 HashKey 值
			$iv = "931wp8nOQ9HogHzM"; // 商店專屬串接金鑰 HashIV 值
			$post_data = trim ( bin2hex ( mcrypt_encrypt ( MCRYPT_RIJNDAEL_128, $key, pay2go_addpadding ( $post_data_str ), MCRYPT_MODE_CBC, $iv ) ) ); // 加密
			$url = "https://cinv.pay2go.com/API/invoice_issue";
			$MerchantID = "3988380"; // 商店代號
			$transaction_data_array = array ( // 送出欄位
					"MerchantID_" => $MerchantID,
					"PostData_" => $post_data 
			);
			$transaction_data_str = http_build_query ( $transaction_data_array );
			$result = pay2go_curl_work ( $url, $transaction_data_str ); // 背景送出
			$this->data_result ['result'] = $result;
			
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$code_time = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
	public function send_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			//
			$this->load->library ( 'vidol_billing/pay2go_api' );
			$this->load->model ( 'vidol_billing/pay2goInvoice_model' );
			// 變數
			$this->data_result = array ();
			//
			$query = $this->pay2goInvoice_model->get_pay2goInvoice_by_7day ( 100 );
			if ($query->num_rows () > 0) {
				foreach ( $query->result () as $row ) {
					$tmpe_pay2goInvoice = $this->pay2go_api->send_invoice ( $row );
					array_push($this->data_result, $tmpe_pay2goInvoice);
					unset($tmpe_pay2goInvoice);
				}
			}else{
				$this->data_result['Message'] = '沒有需要開立發票資料';
			}
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$code_time = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
}
