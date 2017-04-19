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
	public function all_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->helper ( 'url' );
			$this->load->library ( 'pagination' );
			$this->load->model ( 'vidol_billing/sold_packages_model' );
			$this->load->driver ( 'cache', array (
					'adapter' => 'memcached',
					'backup' => 'dummy' 
			) );
			// 變數
			$data_input = array ();
			$data_cache = array ();
			$this->data_result = array (
					'result' => false,
					'pagination' => false 
			);
			// debug_快取名稱變數
			if($this->data_debug == true){
				$this->data_result ['cache_name'] = array();
			}
			// 接收變數
			$data_input ['page'] = $this->get ( 'page' );
			if (empty ( $data_input ['page'] ) || $data_input ['page'] <= 0 || ! is_numeric ( $this->get ( 'page' ) )) {
				$data_input ['page'] = 1; // 最小值
			}
			$data_input ['page_size'] = $this->get ( 'page_size' );
			if (empty ( $data_input ['page_size'] ) || $data_input ['page_size'] <= 0 || ! is_numeric ( $this->get ( 'page_size' ) )) {
				$data_input ['page_size'] = 10; // 最小值
			} elseif ($data_input ['page_size'] > 50) {
				$data_input ['page_size'] = 50; // 最大值
			}
			$data_input ['type'] = is_null ( $this->get ( 'type' ) ) ? null : $this->get ( 'type' );
			$data_input ['show'] = is_null ( $this->get ( 'show' ) ) ? null : $this->get ( 'show' );
			// pagination分頁資料
			// 資料總筆數
			$cache_name_packages_count = sprintf ( '%s_get_packages_count_by_status_%s_%s', ENVIRONMENT, $data_input ['type'], $data_input ['show'] );
			// debug_紀錄快取名稱
			if($this->data_debug == true){
				array_push($this->data_result ['cache_name'], $cache_name_packages_count);
			}
			// $this->cache->memcached->delete ( $cache_name_packages_count );
			$data_cache [$cache_name_packages_count] = $this->cache->memcached->get ( $cache_name_packages_count );
			if ($data_cache [$cache_name_packages_count] == false) {
				$data_cache [$cache_name_packages_count] = $this->sold_packages_model->get_packages_count_by_status ( $data_input ['type'], $data_input ['show'] );
				$this->cache->memcached->save ( $cache_name_packages_count, $data_cache [$cache_name_packages_count], 3000 );
			}
			$count = $data_cache [$cache_name_packages_count];
			// 分頁資訊
			$config ['total_rows'] = $count;
			$config ['cur_page'] = $data_input ['page'];
			$config ['per_page'] = $data_input ['page_size'];
			$this->pagination->initialize ( $config );
			$this->data_result ['pagination'] = $this->pagination->get_pagination_info ();
			// 資料庫分頁用
			$database_limit = $this->pagination->get_database_limit ();
			// packages產品包
			$cache_name_packages = sprintf ( '%s_get_packages_by_status_%s_%s_%s_%s', ENVIRONMENT, $data_input ['type'], $data_input ['show'], $database_limit ['start'], $database_limit ['limit'] );
			// debug_紀錄快取名稱
			if($this->data_debug == true){
				array_push($this->data_result ['cache_name'], $cache_name_packages);
			}
			// $this->cache->memcached->delete ( $cache_name_packages );
			$data_cache [$cache_name_packages] = $this->cache->memcached->get ( $cache_name_packages );
			if ($data_cache [$cache_name_packages] == false) {
				// 防止array組合型態錯誤警告
				$data_cache [$cache_name_packages] = array();
				$query = $this->sold_packages_model->get_packages_by_status ( $data_input ['type'], $data_input ['show'], $database_limit ['start'], $database_limit ['limit'] );
				if ($query->num_rows () > 0) {
					foreach ( $query->result () as $row ) {
						$tmpe_package = array(
								'no' => $row->sp_pk,
								'title' => $row->sp_title,
								'description' => str_replace ( array (
										"\r\n",
										"\r",
										"\n",
										"\n\r" 
								), '', $row->sp_des ),
								'cost' => $row->sp_cost,
								'price' => $row->sp_price,
								'createdAt' => $row->sp_time_creat,
								'updatedAt' => $row->sp_time_update 
						);
						array_push($data_cache [$cache_name_packages], $tmpe_package);
						unset($tmpe_package);
					}
				}
				$this->cache->memcached->save ( $cache_name_packages, $data_cache [$cache_name_packages], 3000 );
			}
			$this->data_result ['result'] = $data_cache [$cache_name_packages];
			unset($data_cache [$cache_name_packages]);
			// debug_輸入資料
			if($this->data_debug == true){
				$data_input['data_debug'] = $this->data_debug;
				$this->data_result['data_input'] = $data_input;
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
	public function package_get($package_no) {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->model ( 'vidol_billing/payment_model' );
			$this->load->model ( 'vidol_billing/sold_packages_model' );
			$this->load->driver ( 'cache', array (
					'adapter' => 'memcached',
					'backup' => 'dummy' 
			) );
			// 變數
			$data_input = array ();
			$data_cache = array ();
			$this->data_result = array (
					'result' => false,
					'payment' => false 
			);
			// debug_快取名稱變數
			if($this->data_debug == true){
				$this->data_result ['cache_name'] = array();
			}
			// 接收變數
			$data_input ['package_no'] = $package_no;
			if (empty ( $data_input ['package_no'] ) || ! is_numeric ( $data_input ['package_no'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// package 產品包
			$cache_name_packages = sprintf ( '%s_get_package_by_pk_%s', ENVIRONMENT, $data_input ['package_no'] );
			// debug_紀錄快取名稱
			if($this->data_debug == true){
				array_push($this->data_result ['cache_name'], $cache_name_packages);
			}
			// $this->cache->memcached->delete($cache_name_packages);
			$data_cache [$cache_name_packages] = $this->cache->memcached->get ( $cache_name_packages );
			if ($data_cache [$cache_name_packages] == false) {
				// 取得資料
				$query = $this->sold_packages_model->get_package_by_pk ( $data_input ['package_no'] );
				if ($query->num_rows () > 0) {
					$row = $query->row ();
					$data_cache [$cache_name_packages] = array (
							'no' => $row->sp_pk,
							'title' => $row->sp_title,
							'description' => str_replace ( array (
									"\r\n",
									"\r",
									"\n",
									"\n\r" 
							), '', $row->sp_des ),
							'cost' => $row->sp_cost,
							'price' => $row->sp_price,
							'createdAt' => $row->sp_time_creat,
							'updatedAt' => $row->sp_time_update 
					);
				}
				$this->cache->memcached->save ( $cache_name_packages, $data_cache [$cache_name_packages], 3000 );
			}
			$this->data_result ['result'] = $data_cache [$cache_name_packages];
			unset($data_cache [$cache_name_packages]);
			// payment 付款方式
			$cache_name_payment = sprintf ( '%s_get_payment_by_package_no_%s', ENVIRONMENT, $data_input ['package_no'] );
			// debug_紀錄快取名稱
			if($this->data_debug == true){
				array_push($this->data_result ['cache_name'], $cache_name_payment);
			}
			// $this->cache->memcached->delete($cache_name_payment);
			$data_cache [$cache_name_payment] = $this->cache->memcached->get ( $cache_name_payment );
			if ($data_cache [$cache_name_payment] == false) {
				// 防止array組合型態錯誤警告
				$data_cache [$cache_name_payment] = array();
				$query = $this->payment_model->get_payment_by_package_no ( $data_input ['package_no'] );
				if ($query->num_rows () > 0) {
					foreach ( $query->result () as $row ) {
						$tmpe_payment = array (
								'no' => $row->p_pk,
								'title' => $row->p_title,
								'description' => str_replace ( array (
										"\r\n",
										"\r",
										"\n",
										"\n\r" 
								), '', $row->p_des ),
								'proxy' => $row->p_proxy,
								'type' => $row->p_type,
								'rs' => $row->p_rs 
						);
						array_push($data_cache [$cache_name_payment], $tmpe_payment);
						unset($tmpe_payment);
					}
				}
				$this->cache->memcached->save ( $cache_name_payment, $data_cache [$cache_name_payment], 3000 );
			}
			$this->data_result ['payment'] = $data_cache [$cache_name_payment];
			unset($data_cache [$cache_name_payment]);
			// debug_輸入資料
			if($this->data_debug == true){
				$data_input['data_debug'] = $this->data_debug;
				$this->data_result['data_input'] = $data_input;
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
