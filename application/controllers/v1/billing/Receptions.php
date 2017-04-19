<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
ini_set ( "display_errors", "On" ); // On, Off
require_once APPPATH . '/libraries/MY_REST_Controller.php';
class Receptions extends MY_REST_Controller {
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
	/**
	 * 智付通
	 */
	public function spgateway_post() {
		try {
			// 開始時間標記
			$this->benchmark->mark ( 'code_start' );
			// 引入
			$this->load->library ( 'vidol_billing/spgateway_api' );
			$this->load->library ( 'vidol_billing/billing_routes' );
			$this->load->model ( 'vidol_billing/orders_model' );
			$this->load->model ( 'vidol_billing/spgatewayResponse_model' );
			$this->load->model ( 'vidol_billing/spgatewayError_model' );
			$this->load->model ( 'vidol_billing/call_function_model' );
			// 變數
			$data_input = array ();
			$data_update = array ();
			$this->data_result = array (
					'cash' => null 
			);
			// 接收變數
			$data_input ['JSONData'] = $this->post ( 'JSONData' );
			// 沒有資料
			if (empty ( $data_input ['JSONData'] )) {
				// 必填錯誤
				$this->response ( $this->data_result, 416 );
				return;
			}
			// 資料json_decode
			$JSONData = json_decode ( $data_input ['JSONData'] );
			$JSONData_Result = json_decode ( $JSONData->Result );
			// 塞更新資料
			(isset ( $JSONData->Status )) ? $data_update ['R_Status'] = $JSONData->Status : null;
			(isset ( $JSONData->Message )) ? $data_update ['R_Message'] = $JSONData->Message : null;
			(isset ( $JSONData_Result->MerchantID )) ? $data_update ['R_MerchantID'] = $JSONData_Result->MerchantID : null;
			(isset ( $JSONData_Result->Amt )) ? $data_update ['R_Amt'] = $JSONData_Result->Amt : null;
			(isset ( $JSONData_Result->TradeNo )) ? $data_update ['R_TradeNo'] = $JSONData_Result->TradeNo : null;
			(isset ( $JSONData_Result->MerchantOrderNo )) ? $data_update ['R_MerchantOrderNo'] = $JSONData_Result->MerchantOrderNo : null;
			(isset ( $JSONData_Result->PaymentType )) ? $data_update ['R_PaymentType'] = $JSONData_Result->PaymentType : null;
			(isset ( $JSONData_Result->RespondType )) ? $data_update ['R_RespondType'] = $JSONData_Result->RespondType : null;
			(isset ( $JSONData_Result->CheckCode )) ? $data_update ['R_CheckCode'] = $JSONData_Result->CheckCode : null;
			(isset ( $JSONData_Result->PayTime )) ? $data_update ['R_PayTime'] = $JSONData_Result->PayTime : null;
			(isset ( $JSONData_Result->IP )) ? $data_update ['R_IP'] = $JSONData_Result->IP : null;
			(isset ( $JSONData_Result->EscrowBank )) ? $data_update ['R_EscrowBank'] = $JSONData_Result->EscrowBank : null;
			(isset ( $JSONData_Result->RespondCode )) ? $data_update ['R_RespondCode'] = $JSONData_Result->RespondCode : null;
			// (isset ( $JSONData_Result->Auth )) ? $data_update ['R_Auth'] = $JSONData_Result->Auth : null;
			(isset ( $JSONData_Result->Card6No )) ? $data_update ['R_Card6No'] = $JSONData_Result->Card6No : null;
			(isset ( $JSONData_Result->Card4No )) ? $data_update ['R_Card4No'] = $JSONData_Result->Card4No : null;
			(isset ( $JSONData_Result->Inst )) ? $data_update ['R_Inst'] = $JSONData_Result->Inst : null;
			(isset ( $JSONData_Result->InstFirst )) ? $data_update ['R_InstFirst'] = $JSONData_Result->InstFirst : null;
			(isset ( $JSONData_Result->InstEach )) ? $data_update ['R_InstEach'] = $JSONData_Result->InstEach : null;
			(isset ( $JSONData_Result->ECI )) ? $data_update ['R_ECI'] = $JSONData_Result->ECI : null;
			(isset ( $JSONData_Result->TokenUseStatus )) ? $data_update ['R_TokenUseStatus'] = $JSONData_Result->TokenUseStatus : null;
			(isset ( $JSONData_Result->Exp )) ? $data_update ['R_Exp'] = $JSONData_Result->Exp : null;
			(isset ( $JSONData_Result->TokenValue )) ? $data_update ['R_TokenValue'] = $JSONData_Result->TokenValue : null;
			(isset ( $JSONData_Result->TokenLife )) ? $data_update ['R_TokenLife'] = $JSONData_Result->TokenLife : null;
			(isset ( $JSONData_Result->PayBankCode )) ? $data_update ['R_PayBankCode'] = $JSONData_Result->PayBankCode : null;
			(isset ( $JSONData_Result->PayerAccount5Code )) ? $data_update ['R_PayerAccount5Code'] = $JSONData_Result->PayerAccount5Code : null;
			(isset ( $JSONData_Result->CodeNo )) ? $data_update ['R_CodeNo'] = $JSONData_Result->CodeNo : null;
			(isset ( $JSONData_Result->Barcode_1 )) ? $data_update ['R_Barcode_1'] = $JSONData_Result->Barcode_1 : null;
			(isset ( $JSONData_Result->Barcode_2 )) ? $data_update ['R_Barcode_2'] = $JSONData_Result->Barcode_2 : null;
			(isset ( $JSONData_Result->Barcode_3 )) ? $data_update ['R_Barcode_3'] = $JSONData_Result->Barcode_3 : null;
			(isset ( $JSONData_Result->PayStore )) ? $data_update ['R_PayStore'] = $JSONData_Result->PayStore : null;
			if (empty ( $JSONData_Result->Amt ) || empty ( $JSONData->Message ) || empty ( $JSONData_Result->TradeNo ) || empty ( $JSONData_Result->MerchantOrderNo ) || empty ( $JSONData_Result->CheckCode )) {
				// 訂單錯誤
				$this->response ( $this->data_result, 409 );
				return;
			}
			// 檢核碼
			$CheckCode = $this->spgateway_api->CheckCode ( $JSONData_Result->Amt, $JSONData_Result->MerchantOrderNo, $JSONData_Result->TradeNo );
			if ($CheckCode != $JSONData_Result->CheckCode) {
				// 檢核碼錯誤
				$this->response ( $this->data_result, 401 );
				return;
			}
			// 回傳資料更新
			$update_status = $this->spgatewayResponse_model->update_spgatewayResponse_by_order_sn ( $JSONData_Result->MerchantOrderNo, $data_update );
			if (empty ( $update_status )) {
				// 錯誤金留紀錄
				$this->spgatewayError_model->insert_SpgatewayError_for_data ( $data_update );
				// 更新金流記錄錯誤
				$this->response ( $this->data_result, 405 );
				return;
			}
			if ($JSONData->Status == 'SUCCESS') {
				// 回傳成功
				// 取回訂單資料
				$query = $this->orders_model->get_Orders_by_order_sn ( $JSONData_Result->MerchantOrderNo );
				if ($query->num_rows () > 0) {
					$row = $query->row ();
					if ($row->o_subtotal == $JSONData_Result->Amt) {
						// 訂單價錢跟智付通回傳價錢正確(status=1)
						// 成功處理
						if (isset ( $JSONData_Result->TokenUseStatus )) {
							$this->data_result ['cash'] = $this->call_function_model->add_to_cash ( $JSONData_Result->MerchantOrderNo, $JSONData_Result->TradeNo, $JSONData_Result->TokenUseStatus, $JSONData_Result->TokenUseStatus, $JSONData_Result->TokenValue, $JSONData_Result->TokenLife );
						} else {
							$this->data_result ['cash'] = $this->call_function_model->add_to_cash ( $JSONData_Result->MerchantOrderNo, $JSONData_Result->TradeNo, 0, 0, null, null );
						}
					} else {
						// 訂單價錢跟智付通回傳價錢異常(status=3)
						$this->data_result ['cash'] = $this->call_function_model->add_to_cancel_cash ( $JSONData_Result->MerchantOrderNo, 3, 0, sprintf ( '[%s]:金額異常-%s', $JSONData->Status, $JSONData->Message ) );
					}
				} else {
					// 訂單錯誤
					$this->response ( $this->data_result, 405 );
					return;
				}
			} else {
				// 回傳非成功
				// 失敗處理
				$this->data_result ['cash'] = $this->call_function_model->add_to_cancel_cash ( $JSONData_Result->MerchantOrderNo, - 1, 0, sprintf ( '[%s]:%s', $JSONData->Status, $JSONData->Message ) );
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
	public function test_post() {
		$this->data_result ['POST'] = $_POST;
		$this->data_result ['GET'] = $_GET;
		$this->response ( $this->data_result, 200 );
	}
}
