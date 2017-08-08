<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Caches extends MY_REST_Controller {
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
	public function memcached_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->driver ( 'cache', array (
					'adapter' => 'memcached',
					'backup' => 'dummy' 
			) );
			// 變數
			$data_input = array ();
			$this->data_result = array ();
			// 接收變數
			$data_input ['key'] = $this->get ( 'key' );
			if (empty ( $data_input ['key'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 取得暫存
			$this->data_result [$data_input ['key']] = $this->cache->memcached->get ( $data_input ['key'] );
			$this->data_result ['info'] = $this->cache->memcached->cache_info ();
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$code_time = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
	public function memcached_post() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->driver ( 'cache', array (
					'adapter' => 'memcached',
					'backup' => 'dummy' 
			) );
			// 變數
			$data_input = array ();
			$this->data_result = array ();
			// 接收變數
			$data_input ['key'] = $this->post ( 'key' );
			$data_input ['value'] = $this->post ( 'value' );
			if (empty ( $data_input ['key'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 建立暫存
			$this->data_result [$data_input ['key']] = $this->cache->memcached->save ( $data_input ['key'], $data_input ['value'], 3000 );
			$this->data_result ['info'] = $this->cache->memcached->cache_info ();
			// 結束時間標記
			$this->benchmark->mark ( 'code_end' );
			// 標記時間計算
			$code_time = $this->benchmark->elapsed_time ( 'code_start', 'code_end' );
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}

	public function memcached_delete() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->driver ( 'cache', array (
					'adapter' => 'memcached',
					'backup' => 'dummy' 
			) );
			// 變數
			$data_input = array ();
			$this->data_result = array ();
			// 接收變數
			$data_input ['key'] = $this->delete ( 'key' );
			if (empty ( $data_input ['key'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 清除暫存
			$this->data_result = $this->cache->memcached->delete ( $data_input ['key'] );
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
