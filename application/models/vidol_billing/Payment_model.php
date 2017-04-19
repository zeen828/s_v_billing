<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Payment_model extends CI_Model
{
	private $table_name = 'Payments_tbl';
	private $fields_pk = 'p_pk';
	
	public function __construct ()
	{
		parent::__construct();
		// $this->load->config('set/databases_fiels', TRUE);
		$this->r_db = $this->load->database('vidol_billing_read', TRUE);
		$this->w_db = $this->load->database('vidol_billing_write', TRUE);
	}
	
	public function __destruct() {
    	$this->r_db->close();
    	unset($this->r_db);
    	$this->w_db->close();
    	unset($this->w_db);
		//parent::__destruct();
	}
	
	public function insert_Payments_for_data($data){
		$this->w_db->insert($this->table_name, $data);
		$id = $this->w_db->insert_id();
		//echo $this->w_db->last_query();
		return $id;
	}
	
	public function update_Payments_for_data($pk, $data){
		$this->w_db->where($this->fields_pk, $pk);
		$this->w_db->update($this->table_name, $data);
		$result = $this->w_db->affected_rows();
		//echo $this->w_db->last_query();
		return $result;
	}
	
	public function get_row_Payments_by_pk ($select, $pk)
	{
		if(!empty($select)){
			$this->r_db->select($select);
		}
		$this->r_db->where($this->fields_pk, $pk);
		$query = $this->r_db->get($this->table_name);
		//echo $this->r_db->last_query();
		if ($query->num_rows() > 0){
			return $query->row();
		}
		return false;
	}
	
	/**
	 * 取得產品包金流方式
	 * @param unknown $package_no	產品包號碼
	 * @return unknown
	 */
	public function get_payment_by_package_no ($package_no)
	{
		$this->r_db->select('p_pk, p_title, p_des, p_proxy, p_type, p_rs');
		$this->r_db->where('spp_package_no', $package_no);
		$this->r_db->where('p_status', '1');
		$this->r_db->join('Payments_tbl', 'Payments_tbl.p_pk = Sold_package_payment_tbl.spp_payment_no', 'left');
		$query = $this->r_db->get('Sold_package_payment_tbl');
		//echo $this->r_db->last_query();
		return $query;
	}
	
	public function get_payment_no_by_proxy_type($proxy, $type)
	{
		$this->r_db->select('p_pk');
		$this->r_db->join('Payments_tbl', 'Payments_tbl.p_pk = Sold_package_payment_tbl.spp_payment_no', 'left');
		$this->r_db->where('p_proxy', $proxy);
		$this->r_db->where('p_type', $type);
		$this->r_db->where('p_status', '1');
		$query = $this->r_db->get('Sold_package_payment_tbl');
		//echo $this->r_db->last_query();
		if ($query->num_rows () > 0) {
			$row = $query->row();
			return $row->p_pk;
		}
		return false;
	}
}
