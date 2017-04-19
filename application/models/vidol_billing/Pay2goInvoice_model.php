<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Pay2goInvoice_model extends CI_Model
{
	private $table_name = 'Pay2goInvoice_tbl';
	private $fields_pk = 'pi_pk';
	
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
    
    public function insert_Pay2goInvoice_for_data($data){
    	$this->w_db->insert($this->table_name, $data);
    	$id = $this->w_db->insert_id();
    	//echo $this->w_db->last_query();
    	return $id;
    }
    
    public function update_Pay2goInvoice_for_data($pk, $data){
    	$this->w_db->where($this->fields_pk, $pk);
    	$this->w_db->update($this->table_name, $data);
    	$result = $this->w_db->affected_rows();
    	//echo $this->w_db->last_query();
    	return $result;
    }
    
    public function get_row_Pay2goInvoice_by_pk ($select, $pk)
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
    
    public function get_row_pay2goInvoic_by_order_sn ($select, $order_sn)
    {
    	if(!empty($select)){
    		$this->r_db->select($select);
    	}
    	$this->r_db->where('MerchantOrderNo', $order_sn);
    	$query = $this->r_db->get($this->table_name);
    	//echo $this->r_db->last_query();
    	if ($query->num_rows() > 0){
    		return $query->row();
    	}
    	return false;
    }
    
    public function get_pay2goInvoice_by_7day ($limit)
    {
    	//$date_7day = date('Y-m-d H:00:00', strtotime('-7 day'));
    	$date_7day = date('Y-m-d');
    	$this->r_db->select('pi_pk,RespondType,Version,TimeStamp,TransNum,MerchantOrderNo,Status,CreateStatusTime,Category,BuyerName,BuyerUBN,BuyerPhone,BuyerAddress,BuyerEmail,CarrierType,CarrierNum,LoveCode,PrintFlag,TaxType,TaxRate,CustomsClearance,Amt,AmtSales,AmtZero,AmtFree,TaxAmt,TotalAmt,ItemName,ItemCount,ItemUnit,ItemPrice,ItemAmt,ItemTaxType,Comment');
    	$this->r_db->where('CreateStatusTime <=', $date_7day);
    	$this->r_db->where('pi_order_status', '1');
    	$this->r_db->where('Result_Status', null);
    	$this->r_db->limit($limit);
    	$query = $this->r_db->get($this->table_name);
    	//echo $this->r_db->last_query();
    	return $query;
    }
}
