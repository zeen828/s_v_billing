<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Promos extends MY_REST_Controller {
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
	// export CI_ENV="development"; php /var/www/codeigniter/3.0.6/Billing/index.php v1 user promos dealer
	// export CI_ENV="testing"; php /var/www/codeigniter/3.0.6/Billing/index.php v1 user promos dealer
	// export CI_ENV="production"; php /var/www/codeigniter/3.0.6/Billing/index.php v1 user promos dealer
	public function dealer_post() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->config->load('vidol');
			$this->config->load('restful_status_code');
			$this->load->model ( 'vidol_billing/call_function_model' );
			$this->load->model ( 'vidol_billing/coupon_model' );
			$this->load->model ( 'vidol_dealer/dealers_model' );
			$this->lang->load ( 'restful_status_lang', 'traditional-chinese' );
			$this->load->driver ( 'cache', array (
					'adapter' => 'memcached',
					'backup' => 'dummy' 
			) );
			// 變數
			$data_input = array ();
			$data_cache = array ();
			$this->data_result = array (
					'result' => array (),
					'code' => 'S0000000',
					'message' => '',
					'time' => 0
			);
			// 接收變數
			$data_input ['user_no'] = $this->post ( 'user_no' );
			$data_input ['mongo_id'] = $this->post ( 'mongo_id' );
			$data_input ['member_id'] = $this->post ( 'member_id' );
			$data_input ['dealer'] = $this->post ( 'dealer' );
			$data_input ['ip'] = $this->post ( 'ip' );
			$data_input ['debug'] = $this->post ( 'debug' );
			//AES解密
			$aes = array(
					'method' => $this->config->item('aes_method'),
					'key' => $this->config->item('aes_key'),
					'iv' => $this->config->item('aes_iv'),
			);
			$data_input ['user_no'] = openssl_decrypt($data_input ['user_no'], $aes['method'], $aes['key'], 0, $aes['iv']);
			$data_input ['mongo_id'] = openssl_decrypt($data_input ['mongo_id'], $aes['method'], $aes['key'], 0, $aes['iv']);
			$data_input ['member_id'] = openssl_decrypt($data_input ['member_id'], $aes['method'], $aes['key'], 0, $aes['iv']);
			$data_input ['dealer'] = openssl_decrypt($data_input ['dealer'], $aes['method'], $aes['key'], 0, $aes['iv']);
			if (empty ( $data_input ['user_no'] ) && empty ( $data_input ['mongo_id'] ) && empty ( $data_input ['member_id'] ) || empty ( $data_input ['dealer'] ) || empty($data_input ['ip'])) {
				// 必填錯誤
				$this->data_result ['message'] = $this->lang->line ( 'input_required_error' );
				$this->data_result ['code'] = 'I0300001';
				$this->response ( $this->data_result, 416 );
				return;
			}
			//取得經銷商活動券
			$dealer = $this->dealers_model->get_row_by_title_status ( 'd_coupon as coupon', $data_input ['dealer'] );
			if(empty($dealer->coupon)){
				// 沒有活動卷
				$this->data_result ['message'] = $this->lang->line ( 'dealer_not_set_coupon' );
				$this->data_result ['code'] = 'D0400005';
				$this->response ( $this->data_result, 404 );
				return;
			}
			// 綁定商品
			$coupon_title = $this->coupon_model->get_row_Coupon_by_sn ( 'c_set_title as title', $dealer->coupon );
			$this->data_result ['result'] = $coupon_title->title;
			// 序號兌換
			$coupon = $this->call_function_model->exchange_SN ( 0, 0, $data_input ['mongo_id'], '', $dealer->coupon, $data_input ['ip'] );
			if ($coupon ['status_code'] != '200') {
				// 沒有活動卷
				$this->data_result ['message'] = $this->lang->line ( 'billing_exchange_SN_error' );
				$this->data_result ['code'] = 'B0500001';
				$this->response ( $this->data_result, 404 );
				return;
			}
			// 成功兌換
			$cash = $this->call_function_model->add_to_cash ( $coupon ['order_sn'], '', 0, 0, null, null );
			if ($cash ['status_code'] != '200') {
				// 沒有活動卷
				$this->data_result ['message'] = $this->lang->line ( 'billing_add_to_cash_error' );
				$this->data_result ['code'] = 'B0500002';
				$this->response ( $this->data_result, 404 );
				return;
			}
			//成功
			$this->data_result ['code'] = 'S0000200';
			// DEBUG印出
			if ($data_input ['debug'] == 'debug') {
				$this->data_result ['debug'] ['data_input'] = $data_input;
				$this->data_result ['debug'] ['data_cache'] = $data_cache;
				$this->data_result ['debug'] ['aes'] = $aes;
				$this->data_result ['debug'] ['dealer'] = $dealer;
				$this->data_result ['debug'] ['coupon_title'] = $coupon_title;
				$this->data_result ['debug'] ['coupon'] = $coupon;
				$this->data_result ['debug'] ['cash'] = $cash;
			}
			unset ( $cash );
			unset ( $coupon );
			unset ( $coupon_title );
			unset ( $dealer );
			unset ( $aes );
			unset ( $data_cache );
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
