<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Automatics extends MY_REST_Controller {
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
	public function package_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->library ( 'vidol_billing/spgateway_api' );
			$this->load->model ( 'vidol_billing/orders_model' );
			$this->load->model ( 'vidol_billing/order_cashs_model' );
			$this->load->model ( 'vidol_billing/spgatewayResponse_model' );
			$this->load->model ( 'vidol_billing/pay2goInvoice_model' );
			$this->load->model ( 'vidol_billing/call_function_model' );
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'order' => false,
					'cash' => false
			);
			$data_input['time_start'] = date('Y-m-d H:i:s', strtotime('-7 day'));//七天前
			$data_input['time_end'] = date('Y-m-d H:i:s', strtotime('+3 day'));//三天後
			$data_input['limit'] = 500;//處理筆數
			// 取得即將過期有約定信用卡(自動續扣)的舊訂單
			$query = $this->order_cashs_model->get_Order_cashs_by_rs ('oc_pk,oc_order_sn,oc_user_creat,oc_user_no,oc_mongo_id,oc_member_id,oc_package_no,oc_package_title,oc_payment_no,oc_invoice_type',$data_input['time_start'], $data_input['time_end'], $data_input['limit']);
			if ($query->num_rows () <= 0) {
				// 沒有需要續扣資料
				$this->data_result ['message'] = $this->lang->line ( 'database_not_data' );
				$this->data_result ['code'] = 'D0100009';
				$this->response ( $this->data_result, 404 );
				return;
			}
			foreach ( $query->result () as $row ) {
				$this->data_result[$row->oc_pk]['order_cashs'] = $row;
				//建立訂單
				$order = $this->call_function_model->add_order ($row->oc_user_creat, $row->oc_user_no, $row->oc_mongo_id, $row->oc_member_id, $row->oc_package_no, $row->oc_payment_no, '', $row->oc_invoice_type, '127.0.0.1');
				$this->data_result[$row->oc_pk]['order'] = $order;
				if ($order ['status_code'] != '200') {
					// 沒有需要續扣資料
					$this->data_result ['message'] = $this->lang->line ( 'database_not_data' );
					$this->data_result ['code'] = 'D0100009';
					$this->response ( $this->data_result, 404 );
					return;
				}
				if ($order ['status_code'] == '200') {
					if ($order ['price'] == 0) {
						// 不需要付錢
						$cash = $this->call_function_model->add_to_cash ( $order ['order_sn'], '', 0, 0, null, null );
						$this->data_result[$row->oc_pk]['cash'] = $cash;
					} else {
						// 需要付錢,開立發票
						$row_invoice = $this->pay2goInvoice_model->get_row_pay2goInvoic_by_order_sn('BuyerName,BuyerUBN,BuyerPhone,BuyerAddress,BuyerEmail,LoveCode,Comment', $row->oc_order_sn);
						$this->data_result[$row->oc_pk]['row_invoice'] = $row_invoice;
						if($row_invoice != false){
							// 需要付錢,開立發票
							$comment = sprintf('[自動扣款]%s', $row_invoice->Comment);
							$invoice = $this->call_function_model->add_invoice ( $order ['order_sn'], '', $row->oc_invoice_type, $row_invoice->BuyerName, $row_invoice->BuyerUBN, $row_invoice->BuyerPhone, $row_invoice->BuyerAddress, $row_invoice->BuyerEmail, $row_invoice->LoveCode, $row->oc_package_title, $order ['price'], $comment );
							$this->data_result[$row->oc_pk]['invoice'] = $invoice;
						}
						//取得原本金流與發票資料
						$row_spgateway = $this->spgatewayResponse_model->get_row_spgatewayResponse_by_order_sn('Email,TokenTerm,R_TokenValue', $row->oc_order_sn);
						$this->data_result[$row->oc_pk]['row_spgateway'] = $row_spgateway;
						//$this->data_result[$row->oc_pk]['invoice'] = $invoice;
						//$this->data_result[$row->oc_pk]['html'] = $this->spgateway_api->mpg_gateway ( $data_input ['payment_type'], $order ['order_sn'], $order ['price'], $data_input ['package_title'], $data_input ['rs'], $data_input ['BuyerEmail'], $data_input ['Comment'], $data_input ['return_url'] );
					}
				} else {
					switch ($order ['status_code'])
					{
						case '401':
						case '404':
						case '405':
						case '406':
						case '409':
							$this->response ( $this->data_result, $order ['status_code'] );
							return;
						default:
							$this->response ( $this->data_result, $order ['status_code'] );
							return;
					}
				}
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
	public function package_bbb_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->library ( 'vidol_billing/spgateway_api' );
			$this->load->model ( 'vidol_billing/orders_model' );
			$this->load->model ( 'vidol_billing/order_cashs_model' );
			$this->load->model ( 'vidol_billing/spgatewayResponse_model' );
			$this->load->model ( 'vidol_billing/call_function_model' );
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'order' => false,
					'cash' => false 
			);
			$data_input['time_start'] = date('Y-m-d H:i:s', strtotime('-7 day'));//七天前
			$data_input['time_end'] = date('Y-m-d H:i:s', strtotime('+3 day'));//三天後
			$data_input['limit'] = 500;//處理筆數
			//取得即將過期有約定信用卡(自動續扣)的舊訂單
			$query_cashs = $this->order_cashs_model->get_Order_cashs_by_rs ('oc_order_sn,oc_user_creat,oc_user_no,oc_mongo_id,oc_member_id,oc_package_no,oc_package_title,oc_payment_no,oc_invoice_type', $data_input['time_start'], $data_input['time_end'], $data_input['limit']);
			if ($query_cashs->num_rows () > 0) {
				foreach ( $query_cashs->result () as $row_cashs ) {
					$order = $this->call_function_model->add_order (
							$row_cashs->oc_user_creat,
							$row_cashs->oc_user_no,
							$row_cashs->oc_mongo_id,
							$row_cashs->oc_member_id,
							$row_cashs->oc_package_no,
							$row_cashs->oc_payment_no,
							'',
							$row_cashs->oc_invoice_type,
							'127.0.0.1'
							);
					$this->data_result['OOO'] = $order;
					//金流
					$query_spgateway = $this->spgatewayResponse_model->get_spgatewayResponse_by_order_sn('Email,TokenTerm,R_TokenValue', $row_cashs->oc_order_sn);
					if ($query_spgateway->num_rows() > 0)
					{
						$row_spgateway = $query_spgateway->row();
						$this->data_result['spgateway'] = $row_spgateway;
					}
					array_push($this->data_result, $row_cashs);
					//
					$MerchantID = 'MS3514320';
					$HashKey = 's6l5w9O7Bpg6gldFbyE7PafwkOyHQlmj';
					$HashIV = 'sHoZRd6sCnzSINyT';
					//
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
					$this->data_result['III'] = $input_array;
					$post_data_str = http_build_query($input_array);
					$this->data_result['SSS'] = $post_data_str;
					$PostData = $this->spgateway_encrypt($HashKey, $HashIV, $post_data_str);
					$this->data_result['PPP'] = $PostData;
					//
					$parameter = array(
						'MerchantID_' => $MerchantID,
						'PostData_' => $PostData,
						'Pos_' => 'JSON',
					);
					$parameter = http_build_query ($parameter);
					$url = 'https://ccore.spgateway.com/API/CreditCard';//test
					//$url = 'https://core.spgateway.com/API/CreditCard';//
					
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
					$this->data_result['result'] = $result;
					$this->data_result['result_decode'] = json_decode($result);
					$this->data_result['retcode'] = $retcode;
				}
			}else{
				$this->data_result['Message'] = '沒有需要續扣產包資料';
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
	
	function spgateway_encrypt($key = "", $iv = "", $str = "") {
		$str = trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $this->addpadding($str), MCRYPT_MODE_CBC, $iv)));
		return $str;
	}
	
	function addpadding($string, $blocksize = 32) {
		$len = strlen($string);
		$pad = $blocksize - ($len % $blocksize);
		$string .= str_repeat(chr($pad), $pad);
		return $string;
	}
}
