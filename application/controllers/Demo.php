<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
require APPPATH . '/libraries/REST_Controller.php';
class Demo extends REST_Controller {
	private $data_result;
	public function __construct() {
		parent::__construct ();
		// 資料庫
		// $this->load->database();
	}
	public function index_get() {
		try {
			$this->response ( $this->data_result, 200 );
		} catch ( Exception $e ) {
			show_error ( $e->getMessage () . ' --- ' . $e->getTraceAsString () );
		}
	}
}

/* End of file Demo.php */
/* Location: ./application/controllers/Demo.php */
