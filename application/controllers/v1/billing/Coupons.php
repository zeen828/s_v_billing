<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Coupons extends MY_REST_Controller {
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
	public function exchange_post() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->model ( 'vidol_billing/call_function_model' );
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'coupon' => false,
					'cash' => false 
			);
			// 接收變數
			$data_input ['user_creat'] = $this->post ( 'user_creat' );
			$data_input ['user_no'] = $this->post ( 'user_no' );
			$data_input ['mongo_id'] = $this->post ( 'mongo_id' );
			$data_input ['member_id'] = $this->post ( 'member_id' );
			$data_input ['coupon_sn'] = $this->post ( 'coupon_sn' );
			$data_input ['ip'] = $this->post ( 'ip' );
			// 沒有資料
			if (empty ( $data_input ['mongo_id'] ) || strlen ( $data_input ['mongo_id'] ) != 10 || empty ( $data_input ['coupon_sn'] ) || empty ( $data_input ['ip'] )) {
				// 必填錯誤
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 序號兌換
			$coupon = $this->call_function_model->exchange_SN ( $data_input ['user_creat'], $data_input ['user_no'], $data_input ['mongo_id'], $data_input ['member_id'], $data_input ['coupon_sn'], $data_input ['ip'] );
			$this->data_result ['coupon'] = $coupon;
			if ($coupon ['status_code'] == '200') {
				$this->data_result ['cash'] = $this->call_function_model->add_to_cash ( $coupon ['order_sn'], '', 0, 0, null, null );
			} else {
				// 序號兌換錯誤
				$this->response ( $this->data_result, $coupon ['status_code'] );
				return;
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
