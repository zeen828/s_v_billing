<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Packages extends MY_REST_Controller {
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
	//export CI_ENV="development"; php /var/www/codeigniter/3.0.6/Billing/index.php v1 user packages active
	//export CI_ENV="testing"; php /var/www/codeigniter/3.0.6/Billing/index.php v1 user packages active
	//export CI_ENV="production"; php /var/www/codeigniter/3.0.6/Billing/index.php v1 user packages active
	public function active_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->model ( 'vidol_billing/sold_packages_model' );
			$this->load->driver ( 'cache', array (
					'adapter' => 'memcached',
					'backup' => 'dummy' 
			) );
			// 變數
			$data_input = array ();
			$data_cache = array ();
			$this->data_result = array (
					'result' => false 
			);
			// 接收變數
			$data_input ['user_no'] = $this->get ( 'user_no' );
			$data_input ['mongo_id'] = $this->get ( 'mongo_id' );
			$data_input ['member_id'] = $this->get ( 'member_id' );
			$data_input ['member_id'] = 123;
			if (empty ( $data_input ['user_no'] ) && empty ( $data_input ['mongo_id'] ) && empty ( $data_input ['member_id'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}

			//$cache_name = sprintf ( 'chatroom_%s', 4 );
			//$this->data_result['name'] = $cache_name;
			//$this->cache->memcached->save ( $cache_name, array('a'=>'b','c'=>'d'), 3000 );
			//$this->cache->memcached->save ( 'CCC', 'ABCDEFG', 300 );
			//$this->data_result['data'] = $this->cache->memcached->get ( $cache_name );
			//$this->data_result['data'] = $this->cache->memcached->get ( 'AAA' );
			//$this->data_result['data'] = $this->cache->memcached->get ( 'CCC' );
			
			// 呼叫websocket
			//$websocket_com = sprintf("python /home/socket_server/websocket/get_userinroom.py");
			//$this->data_result = exec($websocket_com);
			//$this->data_result['debug'] = array(
			//'ws_data' => $ws_data,
			//'websocket_com' => $websocket_com
			//);
			//exec('python /home/socket_server/websocket/client.py \'' . $ws_data . '\'' );
			//$this->data_result = exec($websocket_com);
			
			$this->data_result = ENVIRONMENT;

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
