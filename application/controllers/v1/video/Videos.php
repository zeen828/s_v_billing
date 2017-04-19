<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Videos extends MY_REST_Controller {
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
	public function package_get($video_type, $video_no) {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->model ( 'vidol_billing/sold_items_model' );
			// 變數
			$data_input = array ();
			$this->data_result = array (
					'result' => false 
			);
			// 接收變數
			$data_input ['video_no'] = $video_no;
			$data_input ['video_type'] = $video_type;
			$data_input ['show'] = is_null ( $this->get ( 'show' ) ) ? null : $this->get ( 'show' );
			if (empty ( $data_input ['video_type'] ) || empty ( $data_input ['video_no'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 取得資料
			$query = $this->sold_items_model->get_package_by_video ( $data_input ['video_type'], $data_input ['video_no'], $data_input ['show'] );
			if ($query->num_rows () > 0) {
				$this->data_result ['result'] = array();
				foreach ( $query->result () as $row ) {
					// print_r($row);
					$tmpe_package = array (
							'no' => $row->sp_pk,
							'title' => $row->sp_title,
							'description' => $row->sp_des,
							'cost' => $row->sp_cost,
							'price' => $row->sp_price,
							'createdAt' => $row->sp_time_creat,
							'updatedAt' => $row->sp_time_update 
					);
					array_push($this->data_result ['result'], $tmpe_package);
					unset($tmpe_package);
				}
			} else {
				$this->data_result ['result'] = array ();
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
	public function package_post($video_type, $video_no) {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->model ( 'vidol_billing/sold_items_model' );
			// 變數
			$data_input = array ();
			$this->data_result = array ();
			// 接收變數
			$data_input ['programme_no'] = $this->post ( 'programme_no' );
			$data_input ['video_no'] = $video_no;
			$data_input ['video_type'] = $video_type;
			$data_input ['package'] = $this->post ( 'package' );
			if (empty ( $data_input ['video_type'] ) || empty ( $data_input ['video_no'] ) || empty ( $data_input ['package'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 先全部取消
			// $this->sold_items_model->update_package_status_by_video($data_input ['video_type'], $data_input ['video_no']);
			// 異動後
			$package_set_arr = explode ( ',', $data_input ['package'] );
			if (count ( $package_set_arr ) > 0) {
				foreach ( $package_set_arr as $package ) {
					$tmpe_package = $this->sold_items_model->insert_package_by_video ( $data_input ['programme_no'], $package, $data_input ['video_type'], $data_input ['video_no'], 1 );
					array_push($this->data_result, $tmpe_package);
					unset($tmpe_package);
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
	public function package_put($video_type, $video_no) {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->model ( 'vidol_billing/sold_items_model' );
			// 變數
			$data_input = array ();
			$this->data_result = array ();
			// 接收變數
			$data_input ['programme_no'] = $this->put ( 'programme_no' );
			$data_input ['video_no'] = $video_no;
			$data_input ['video_type'] = $video_type;
			$data_input ['package'] = $this->put ( 'package' );
			if (empty ( $data_input ['video_type'] ) || empty ( $data_input ['video_no'] ) || empty ( $data_input ['package'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 先全部取消
			$this->sold_items_model->update_package_status_by_video ( $data_input ['video_type'], $data_input ['video_no'] );
			// 異動後
			$package_set_arr = explode ( ',', $data_input ['package'] );
			if (count ( $package_set_arr ) > 0) {
				foreach ( $package_set_arr as $package ) {
					$tmpe_package = $this->sold_items_model->insert_package_by_video ( $data_input ['programme_no'], $package, $data_input ['video_type'], $data_input ['video_no'], 1 );
					array_push($this->data_result, $tmpe_package);
					unset($tmpe_package);
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
	public function package_delete($video_type, $video_no) {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->model ( 'vidol_billing/sold_items_model' );
			// 變數
			$data_input = array ();
			$this->data_result = array ();
			// 接收變數
			$data_input ['programme_no'] = $this->delete ( 'programme_no' );
			$data_input ['video_no'] = $video_no;
			$data_input ['video_type'] = $video_type;
			$data_input ['package'] = $this->delete ( 'package' );
			if (empty ( $data_input ['video_type'] ) || empty ( $data_input ['video_no'] ) || empty ( $data_input ['package'] )) {
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 先全部取消
			// $this->sold_items_model->update_package_status_by_video($data_input ['video_type'], $data_input ['video_no']);
			// 異動後
			$package_set_arr = explode ( ',', $data_input ['package'] );
			if (count ( $package_set_arr ) > 0) {
				foreach ( $package_set_arr as $package ) {
					$tmpe_package = $this->sold_items_model->insert_package_by_video ( $data_input ['programme_no'], $package, $data_input ['video_type'], $data_input ['video_no'], 0 );
					array_push($this->data_result, $tmpe_package);
					unset($tmpe_package);
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
}
