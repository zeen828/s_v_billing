<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class SpgatewayResponse_model extends CI_Model
{
	private $table_name = 'SpgatewayResponse_tbl';
	private $fields_pk = 'sr_pk';
	
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
    
    public function insert_SpgatewayResponse_for_data($data){
    	$this->w_db->insert($this->table_name, $data);
    	$id = $this->w_db->insert_id();
    	//echo $this->w_db->last_query();
    	return $id;
    }
    
    public function update_SpgatewayResponse_for_data($pk, $data){
    	$this->w_db->where($this->fields_pk, $pk);
    	$this->w_db->update($this->table_name, $data);
    	$result = $this->w_db->affected_rows();
    	//echo $this->w_db->last_query();
    	return $result;
    }
    
    public function get_row_SpgatewayResponse_by_pk ($select, $pk)
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
     * 更新智付通回傳資料
     * @param unknown $order_sn		訂單序號
     * @param unknown $data			更新資料的陣列
     * @return unknown
     */
    public function update_spgatewayResponse_by_order_sn ($order_sn, $data)
    {
    	$this->w_db->where('MerchantOrderNo', $order_sn);
    	$this->w_db->update('SpgatewayResponse_tbl', $data);
    	$result = $this->w_db->affected_rows();
    	//echo $this->w_db->last_query();
    	return $result;
    }
    
    /**
     * 取得智付通紀錄資料
     * @param unknown $select		查詢欄位
     * @param unknown $order_sn		訂單序號
     * @return unknown
     */
    public function get_spgatewayResponse_by_order_sn ($select, $order_sn)
    {
    	//SELECT * FROM `spgatewayResponse_tbl` WHERE `MerchantOrderNo` = 00014785720930000175 AND `MerchantOrderNo` = `R_MerchantOrderNo` AND `Amt` = `R_Amt`
    	if(!empty($select)){
    		$this->r_db->select($select);
    	}
    	$this->r_db->where('MerchantOrderNo', $order_sn);
    	$query = $this->r_db->get('SpgatewayResponse_tbl');
    	//echo $this->r_db->last_query();
    	return $query;
    }
    
    public function get_row_spgatewayResponse_by_order_sn ($select, $order_sn)
    {
    	//SELECT * FROM `spgatewayResponse_tbl` WHERE `MerchantOrderNo` = 00014785720930000175 AND `MerchantOrderNo` = `R_MerchantOrderNo` AND `Amt` = `R_Amt`
    	if(!empty($select)){
    		$this->r_db->select($select);
    	}
    	$this->r_db->where('MerchantOrderNo', $order_sn);
    	$query = $this->r_db->get('SpgatewayResponse_tbl');
    	//echo $this->r_db->last_query();
    	if ($query->num_rows() > 0){
    		return $query->row();
    	}
    	return false;
    }
}
