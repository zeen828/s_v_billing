<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Sold_packages_model extends CI_Model
{
	private $table_name = 'Sold_packages_tbl';
	private $fields_pk = 'sp_pk';
	
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
	
	public function insert_Sold_packages_for_data($data){
		$this->w_db->insert($this->table_name, $data);
		$id = $this->w_db->insert_id();
		//echo $this->w_db->last_query();
		return $id;
	}
	
	public function update_Sold_packages_for_data($pk, $data){
		$this->w_db->where($this->fields_pk, $pk);
		$this->w_db->update($this->table_name, $data);
		$result = $this->w_db->affected_rows();
		//echo $this->w_db->last_query();
		return $result;
	}
	
	public function get_row_Sold_packages_by_pk ($select, $pk)
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
	
	public function get_packages_count_by_status ($type = null, $show = null)
	{
		$this->r_db->select('sp_pk');
		if(!empty($type)){
			$this->r_db->where('sp_type', $type);
		}
		if(!is_null($show)){
			if($show == '1'){
				$this->r_db->where('sp_show', '1');
			}else if($show == '0'){
				$this->r_db->where('sp_show', '0');
			}
		}
		$this->r_db->where('sp_status', '1');
		$this->r_db->where('sp_time_start <= NOW()');
		$this->r_db->where('sp_time_end >= NOW()');
		$count = $this->r_db->count_all_results($this->table_name);
		//echo $this->r_db->last_query();
		return $count;
	}
	
	public function get_packages_by_status ($type = null, $show = null, $start = 0, $limit = 10)
	{
		$this->r_db->select('sp_pk,sp_title,sp_des,sp_type,sp_cost,sp_price,sp_status,sp_time_creat,sp_time_update');
		if(!empty($type)){
			$this->r_db->where('sp_type', $type);
		}
		if(!is_null($show)){
			if($show == '1'){
				$this->r_db->where('sp_show', '1');
			}else if($show == '0'){
				$this->r_db->where('sp_show', '0');
			}
		}
		$this->r_db->where('sp_status', '1');
		$this->r_db->where('sp_time_start <= NOW()');
		$this->r_db->where('sp_time_end >= NOW()');
		$this->r_db->limit($limit, $start);
		$this->r_db->order_by('sp_sort', 'ASC');
		$query = $this->r_db->get($this->table_name);
		//echo $this->r_db->last_query();
		return $query;
	}
	
	public function get_package_by_pk ($pk)
	{
		$this->r_db->select('sp_pk,sp_title,sp_des,sp_type,sp_cost,sp_price,sp_status,sp_time_creat,sp_time_update');
		$this->r_db->where('sp_pk', $pk);
		$this->r_db->where('sp_status', '1');
		$this->r_db->where('sp_time_start <= NOW()');
		$this->r_db->where('sp_time_end >= NOW()');
		$query = $this->r_db->get($this->table_name);
		//echo $this->r_db->last_query();
		return $query;
	}
	
	public function get_user_packages_by_status ($member_id, $type = null, $show = null, $start = 0, $limit = 10)
	{
		$this->r_db->select('sp_pk,sp_title,sp_des,sp_type,sp_cost,sp_price,sp_status,sp_time_creat,sp_time_update');
		if(!empty($type)){
			$this->r_db->where('sp_type', $type);
		}
		if(!is_null($show)){
			if($show == '1'){
				$this->r_db->where('sp_show', '1');
			}else if($show == '0'){
				$this->r_db->where('sp_show', '0');
			}
		}
		$this->r_db->where('sp_status', '1');
		$this->r_db->where('sp_time_start <= NOW()');
		$this->r_db->where('sp_time_end >= NOW()');
		$this->r_db->limit($limit, $start);
		$this->r_db->order_by('sp_sort', 'ASC');
		$query = $this->r_db->get($this->table_name);
		//echo $this->r_db->last_query();
		return $query;
	}
}
