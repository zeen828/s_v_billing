<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Checks extends MY_REST_Controller {
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
	public function rights_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->model ( 'vidol_billing/sold_items_model' );
			$this->load->model ( 'vidol_billing/call_function_model' );
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'check_rights' => false,
					'ad' => false
			);
			// 接收變數
			$data_input ['user_no'] = $this->get ( 'user_no' );
			$data_input ['mongo_id'] = $this->get ( 'mongo_id' );
			$data_input ['member_id'] = $this->get ( 'member_id' );
			$data_input ['video_type'] = $this->get ( 'video_type' );
			$data_input ['video_no'] = $this->get ( 'video_no' );
			if (empty ( $data_input ['mongo_id'] ) || strlen ( $data_input ['mongo_id'] ) != 10 || empty ( $data_input ['video_type'] ) || empty ( $data_input ['video_no'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 序號兌換
			$check_rights = $this->call_function_model->check_rights ( $data_input ['user_no'], $data_input ['mongo_id'], $data_input ['member_id'], $data_input ['video_type'], $data_input ['video_no'] );
			if (empty ( $check_rights ['status_code'] )) {
				$this->response ( $this->data_result, 401 );
			}
			$this->data_result ['check_rights'] = $check_rights;
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
