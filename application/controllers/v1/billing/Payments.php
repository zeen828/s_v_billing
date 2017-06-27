<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Payments extends MY_REST_Controller {
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
		unset($this->data_result);
	}
	public function payment_post() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->library ( 'vidol_billing/spgateway_api' );
			$this->load->model ( 'vidol_billing/payment_model' );
			$this->load->model ( 'vidol_billing/order_cashs_model' );
			$this->load->model ( 'vidol_billing/call_function_model' );
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'order' => false,
					'invoice' => false,
					'cash' => false,
					'html' => false 
			);
			// 接收變數
			$data_input ['user_creat'] = $this->post ( 'user_creat' );
			$data_input ['user_no'] = $this->post ( 'user_no' );
			$data_input ['mongo_id'] = $this->post ( 'mongo_id' );
			$data_input ['member_id'] = $this->post ( 'member_id' );
			$data_input ['package_no'] = $this->post ( 'package_no' );
			$data_input ['package_title'] = $this->post ( 'package_title' );
			$data_input ['payment_proxy'] = $this->post ( 'payment_proxy' );
			$data_input ['payment_type'] = $this->post ( 'payment_type' );
			$data_input ['coupon_sn'] = $this->post ( 'coupon_sn' );
			$data_input ['rs'] = $this->post ( 'rs' );
			$data_input ['invoice_type'] = $this->post ( 'invoice_type' );
			$data_input ['BuyerName'] = $this->post ( 'BuyerName' );
			$data_input ['BuyerUBN'] = $this->post ( 'BuyerUBN' );
			$data_input ['BuyerPhone'] = $this->post ( 'BuyerPhone' );
			$data_input ['BuyerAddress'] = $this->post ( 'BuyerAddress' );
			$data_input ['BuyerEmail'] = $this->post ( 'BuyerEmail' );
			$data_input ['LoveCode'] = $this->post ( 'LoveCode' );
			$data_input ['Comment'] = $this->post ( 'Comment' );
			$data_input ['return_url'] = $this->post ( 'return_url' );
			$data_input ['ip'] = $this->post ( 'ip' );
			// 必填檢查
			if (empty ( $data_input ['mongo_id'] ) || strlen ( $data_input ['mongo_id'] ) != 10 || empty ( $data_input ['package_no'] ) || empty ( $data_input ['package_title'] ) || empty ( $data_input ['payment_proxy'] ) || !in_array($data_input ['payment_proxy'], array('spgateway', 'pay2go')) || empty ( $data_input ['payment_type'] ) || !in_array($data_input ['payment_type'], array('CREDIT', 'WEBATM', 'VACC', 'CVS', 'BARCODE')) || empty ( $data_input ['invoice_type'] ) || !in_array($data_input ['invoice_type'], array('1', '2', '3')) || empty ( $data_input ['BuyerName'] ) || empty ( $data_input ['BuyerPhone'] ) || empty ( $data_input ['BuyerAddress'] ) || empty ( $data_input ['BuyerEmail'] ) || empty ( $data_input ['return_url'] ) || empty ( $data_input ['ip'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 金流類型
			$data_input ['payment_no'] = $this->payment_model->get_payment_no_by_proxy_type ( $data_input ['payment_proxy'], $data_input ['payment_type'] );
			if (empty ( $data_input ['payment_no'] )) {
				$this->response ( $this->data_result, 408 );
				return;
			}
			// 檢查sp_type單片是否購買過
			$order = $this->order_cashs_model->get_Order_cashs_by_repeat('*', $data_input ['mongo_id'], $data_input ['package_no']);
			if(!empty($order)){
				$this->response ( $this->data_result, 406 );
				return;
			}
			// 訂單
			$order = $this->call_function_model->add_order ( $data_input ['user_creat'], $data_input ['user_no'], $data_input ['mongo_id'], $data_input ['member_id'], $data_input ['package_no'], $data_input ['payment_no'], $data_input ['coupon_sn'], $data_input ['invoice_type'], $data_input ['ip'] );
			$this->data_result ['order'] = $order;
			if ($order ['status_code'] == '200') {
				if ($order ['price'] == 0) {
					// 不需要付錢
					$cash = $this->call_function_model->add_to_cash ( $order ['order_sn'], '', 0, 0, null, null );
					$this->data_result ['cash'] = $cash;
				} else {
					// 需要付錢,開立發票
					$invoice = $this->call_function_model->add_invoice ( $order ['order_sn'], '', $data_input ['invoice_type'], $data_input ['BuyerName'], $data_input ['BuyerUBN'], $data_input ['BuyerPhone'], $data_input ['BuyerAddress'], $data_input ['BuyerEmail'], $data_input ['LoveCode'], $data_input ['package_title'], $order ['price'], $data_input ['Comment'] );
					$this->data_result ['invoice'] = $invoice;
					// 發票開失敗沒關係,事後回頭補開
					//if ($invoice ['status_code'] == '200') {
						// 開立發票,智付通API付款頁
						$this->data_result ['html'] = $this->spgateway_api->mpg_gateway ( $data_input ['payment_type'], $order ['order_sn'], $order ['price'], $data_input ['package_title'], $data_input ['rs'], $data_input ['BuyerEmail'], $data_input ['Comment'], $data_input ['return_url'] );
					//} else {
						// 開立發票失敗
						//$cash = $this->call_function_model->add_to_cancel_cash ( $order ['order_sn'], - 1, 0, sprintf ( '[%s]:%s', $invoice ['status_code'], '訂單開立發票失敗' ) );
						//$this->data_result ['cash'] = $cash;
						//switch ($invoice ['status_code'])
						//{
							//case '404':
							//case '411':
								//$this->response ( $this->data_result, $invoice ['status_code'] );
								//return;
							//default:
								//$this->response ( $this->data_result, $invoice ['status_code'] );
								//return;
						//}
					//}
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
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$code_time = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
	public function auth_token_get() {
		try {
			
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$code_time = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
	public function echo_post() {
		try {
				
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			$this->data_result['POST'] = $_POST;
			$this->data_result['GET'] = $_GET;
			// 標記時間計算
			$code_time = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
}
