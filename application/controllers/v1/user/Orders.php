<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Orders extends MY_REST_Controller {
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
	public function all_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->config->load ( 'restful_status_code' );
			$this->load->library ( 'pagination' );
			$this->load->model ( 'vidol_billing/orders_model' );
			$this->load->helper ( 'url' );
			$this->lang->load ( 'restful_status_lang', 'traditional-chinese' );
			$this->load->driver ( 'cache', array (
					'adapter' => 'memcached',
					'backup' => 'dummy' 
			) );
			// 變數
			$data_input = array ();
			$data_cache_name = array ();
			$data_cache = array ();
			$this->data_result = array (
					'result' => array (),
					'pagination' => array (),
					'code' => $this->config->item ( 'system_default' ),
					'message' => '',
					'time' => 0 
			);
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
			$data_input ['user_no'] = $this->get ( 'user_no' );
			$data_input ['mongo_id'] = $this->get ( 'mongo_id' );
			$data_input ['member_id'] = $this->get ( 'member_id' );
			$data_input ['status'] = $this->get ( 'status' );
			$data_input ['sort'] = $this->get ( 'sort' );
			$data_input ['debug'] = $this->get ( 'debug' );
			if (empty ( $data_input ['user_no'] ) && (empty ( $data_input ['mongo_id'] ) || strlen ( $data_input ['mongo_id'] ) != 10) && empty ( $data_input ['member_id'] )) {
				// 必填錯誤
				$this->data_result ['message'] = $this->lang->line ( 'input_required_error' );
				$this->data_result ['code'] = $this->config->item ( 'input_required_error' );
				$this->response ( $this->data_result, 416 );
				return;
			}
			// pagination分頁資料
			// 資料總筆數
			$count = $this->orders_model->get_Orders_count_by_user ( $data_input ['user_no'], $data_input ['mongo_id'], $data_input ['member_id'], $data_input ['status'] );
			// 分頁資訊
			$config ['total_rows'] = $count;
			$config ['cur_page'] = $data_input ['page'];
			$config ['per_page'] = $data_input ['page_size'];
			$this->pagination->initialize ( $config );
			$this->data_result ['pagination'] = $this->pagination->get_pagination_info ();
			// 資料庫分頁用
			$database_limit = $this->pagination->get_database_limit ();
			// 使用者訂單
			// cache name key
			$data_cache_name ['user_orders'] = sprintf ( '%s_get_Orders_by_user_%s_%s_%s_%s_%s_%s_%s', ENVIRONMENT, $data_input ['user_no'], $data_input ['mongo_id'], $data_input ['member_id'], $data_input ['status'], $data_input ['sort'], $database_limit ['start'], $database_limit ['limit'] );
			// $this->cache->memcached->delete ( $data_cache_name ['user_orders'] );
			$data_cache [$data_cache_name ['user_orders']] = $this->cache->memcached->get ( $data_cache_name ['user_orders'] );
			if ($data_cache [$data_cache_name ['user_orders']] == false) {
				// 防止array組合型態錯誤警告
				$data_cache [$data_cache_name ['user_orders']] = array ();
				$query = $this->orders_model->get_Orders_by_user ( $data_input ['user_no'], $data_input ['mongo_id'], $data_input ['member_id'], $data_input ['status'], $data_input ['sort'], $database_limit ['start'], $database_limit ['limit'] );
				if ($query->num_rows () > 0) {
					foreach ( $query->result () as $row ) {
						if ($row->o_time_deadline == '2037-12-31 00:00:00') {
							$row->o_time_deadline = '無期限';
						}
						$tmpe_Orders = array (
								'order_sn' => $row->o_order_sn,
								'package_no' => $row->o_package_no,
								'package_title' => $row->o_package_title,
								'coupon_sn' => $row->o_coupon_sn,
								'coupon_title' => $row->o_coupon_title,
								'expenses' => $row->o_expenses,
								'subtotal' => $row->o_subtotal,
								'invoice' => $row->o_invoice,
								'status' => $row->o_status,
								'createdAt' => $row->o_time_creat,
								'activeAt' => $row->o_time_active,
								'deadlineAt' => $row->o_time_deadline,
								'note' => $row->o_note 
						);
						array_push ( $data_cache [$data_cache_name ['user_orders']], $tmpe_Orders );
						unset ( $tmpe_Orders );
					}
				}
				$this->cache->memcached->save ( $data_cache_name ['user_orders'], $data_cache [$data_cache_name ['user_orders']], 30000 );
			}
			$this->data_result ['result'] = $data_cache [$data_cache_name ['user_orders']];
			$this->data_result ['message'] = $this->lang->line ( 'system_success' );
			$this->data_result ['code'] = $this->config->item ( 'system_success' );
			// DEBUG印出
			if ($data_input ['debug'] == 'debug') {
				$this->data_result ['debug'] ['data_input'] = $data_input;
				$this->data_result ['debug'] ['data_cache_name'] = $data_cache_name;
				$this->data_result ['debug'] ['data_cache'] = $data_cache;
				$this->data_result ['debug'] ['count'] = $count;
				$this->data_result ['debug'] ['config'] = $config;
				$this->data_result ['debug'] ['database_limit'] = $database_limit;
			}
			unset ( $database_limit );
			unset ( $config );
			unset ( $count );
			unset ( $data_cache );
			unset ( $data_cache_name );
			unset ( $data_input );
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
