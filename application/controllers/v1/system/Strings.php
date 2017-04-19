<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Strings extends MY_REST_Controller {
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
		unset ( $this->data_debug );
		unset ( $this->data_result );
	}
	public function __CI_aes_encode_post() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->library ( 'encryption' );
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'result' => array (),
					'code' => 'S0000000',
					'message' => '',
					'time' => 0 
			);
			// 接收變數
			$data_input ['key'] = $this->post ( 'key' );
			$data_input ['string'] = $this->post ( 'string' );
			if (empty ( $data_input ['string'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			$this->encryption->initialize ( array (
					'cipher' => 'aes-256',
					'mode' => 'cbc',
					'key' => $data_input ['key'] 
			) );
			$this->data_result ['result'] = $this->encryption->encrypt ( $data_input ['string'] );
			
			$this->data_result ['AAAA'] = $this->encryption;
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$this->data_result ['time'] = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
	public function aes_encode_post() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			//引入
			$this->config->load('vidol');
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'result' => array (),
					'code' => 'S0000000',
					'message' => '',
					'time' => 0
			);
			// 接收變數
			$data_input ['key'] = $this->post ( 'key' );
			$data_input ['iv'] = $this->post ( 'iv' );
			$data_input ['string'] = $this->post ( 'string' );
			if (empty($data_input ['key']) || empty($data_input ['iv']) || empty ( $data_input ['string'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			$aes_method = $this->config->item('aes_method');
			$this->data_result ['result'] =openssl_encrypt($data_input ['string'], $aes_method, $data_input ['key'], 0, $data_input ['iv']);
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$this->data_result ['time'] = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
	public function __CI_aes_decode_post() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->library ( 'encryption' );
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'result' => array (),
					'code' => 'S0000000',
					'message' => '',
					'time' => 0 
			);
			// 接收變數
			$data_input ['key'] = $this->post ( 'key' );
			$data_input ['string'] = $this->post ( 'string' );
			if (empty ( $data_input ['string'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			$this->encryption->initialize ( array (
					'cipher' => 'aes-256',
					'mode' => 'cbc',
					'key' => $data_input ['key'] 
			) );
			$this->data_result ['result'] = $this->encryption->decrypt ( $data_input ['string'] );
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$this->data_result ['time'] = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
	public function aes_decode_post() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			//引入
			$this->config->load('vidol');
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'result' => array (),
					'code' => 'S0000000',
					'message' => '',
					'time' => 0
			);
			// 接收變數
			$data_input ['key'] = $this->post ( 'key' );
			$data_input ['iv'] = $this->post ( 'iv' );
			$data_input ['string'] = $this->post ( 'string' );
			if (empty($data_input ['key']) || empty($data_input ['iv']) || empty ( $data_input ['string'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			$aes_method = $this->config->item('aes_method');
			$this->data_result ['result'] = openssl_decrypt($data_input ['string'], $aes_method, $data_input ['key'], 0, $data_input ['iv']);
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$this->data_result ['time'] = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
}
