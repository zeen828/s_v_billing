<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Levels extends MY_REST_Controller {
	private $data_debug;
	private $data_result;
	public function __construct() {
		parent::__construct ();
		$this->_my_logs_start = false;
		$this->_my_logs_type = 'user_levels';
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
	public function level_get() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->config->load ( 'vidol' );
			$this->config->load ( 'restful_status_code' );
			$this->load->model ( 'vidol_user/user_profile_model' );
			$this->load->model ( 'vidol_user/user_level_model' );
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
					'code' => $this->config->item ( 'system_default' ),
					'message' => '',
					'time' => 0 
			);
			// 接收變數
			$data_input ['token'] = $this->get ( 'token' );
			$data_input ['user_no'] = $this->get ( 'user_no' );
			$data_input ['mongo_id'] = $this->get ( 'mongo_id' );
			$data_input ['member_id'] = $this->get ( 'member_id' );
			$data_input ['debug'] = $this->get ( 'debug' );
			if ((empty ( $data_input ['mongo_id'] ) || strlen ( $data_input ['mongo_id'] ) != 10) && empty ( $data_input ['member_id'] )) {
				// 必填錯誤
				$this->data_result ['message'] = $this->lang->line ( 'input_required_error' );
				$this->data_result ['code'] = $this->config->item ( 'input_required_error' );
				$this->response ( $this->data_result, 416 );
				return;
			}
			// cache name key
			$data_cache_name ['user'] = sprintf ( '%s_get_user_%s_%s', ENVIRONMENT, $data_input ['mongo_id'], $data_input ['member_id'] );
			// $this->cache->memcached->delete ( $data_cache_name[$cache_name_dealer] );
			$data_cache [$data_cache_name ['user']] = $this->cache->memcached->get ( $data_cache_name ['user'] );
			if ($data_cache [$data_cache_name ['user']] == false) {
				// 取得會員
				$data_cache [$data_cache_name ['user']] = $this->user_profile_model->get_row_by_mongoid_memberid ( 'u_level as level', $data_input ['mongo_id'], $data_input ['member_id'] );
				$this->cache->memcached->save ( $data_cache_name ['user'], $data_cache [$data_cache_name ['user']], 30000 );
			}
			$data_user = $data_cache [$data_cache_name ['user']];
			if (empty ( $data_user )) {
				// 會員錯誤
				$this->data_result ['message'] = $this->lang->line ( 'user_error' );
				$this->data_result ['code'] = $this->config->item ( 'user_error' );
				$this->response ( $this->data_result, 400 );
				return;
			}
			// cache name key
			$data_cache_name ['user_level'] = sprintf ( '%s_get_user_level_%s', ENVIRONMENT, $data_user->level );
			// $this->cache->memcached->delete ( $data_cache_name[$cache_name_dealer] );
			$data_cache [$data_cache_name ['user_level']] = $this->cache->memcached->get ( $data_cache_name ['user_level'] );
			if ($data_cache [$data_cache_name ['user_level']] == false) {
				// 取得會員分級
				$data_cache [$data_cache_name ['user_level']] = $this->user_level_model->get_row_by_pk_status ( 'ul_pk as no, ul_title as title, ul_tag as tag', $data_user->level );
				$this->cache->memcached->save ( $data_cache_name ['user_level'], $data_cache [$data_cache_name ['user_level']], 30000 );
			}
			$data_user_level = $data_cache [$data_cache_name ['user_level']];
			if (empty ( $data_user_level )) {
				// 會員分級錯誤
				$this->data_result ['message'] = $this->lang->line ( 'user_level_error' );
				$this->data_result ['code'] = $this->config->item ( 'user_level_error' );
				$this->response ( $this->data_result, 400 );
				return;
			}
			$this->data_result ['result'] ['level'] = $data_user_level;
			$this->data_result ['message'] = $this->lang->line ( 'system_success' );
			$this->data_result ['code'] = $this->config->item ( 'system_success' );
			// DEBUG印出
			if ($data_input ['debug'] == 'debug') {
				$this->data_result ['debug'] ['data_input'] = $data_input;
				$this->data_result ['debug'] ['data_cache_name'] = $data_cache_name;
				$this->data_result ['debug'] ['data_cache'] = $data_cache;
				$this->data_result ['debug'] ['data_user'] = $data_user;
				$this->data_result ['debug'] ['data_user_level'] = $data_user_level;
			}
			unset ( $data_user_level );
			unset ( $data_user );
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
