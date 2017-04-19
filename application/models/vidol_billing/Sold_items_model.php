<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Sold_items_model extends CI_Model
{
	private $table_name = 'Sold_items_tbl';
	private $fields_pk = 'si_pk';
	
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
	
	//不重複建立前一天要爬資料
	public function insert_package_by_video($programme_no, $spackage_no, $video_type, $video_no, $status)
	{
		$this->r_db->where('si_package_no', $spackage_no);
		$this->r_db->where('si_type', $video_type);
		$this->r_db->where('si_type', $video_type);
		$this->r_db->where('si_type_video_no', $video_no);
		$count = $this->r_db->count_all_results($this->table_name);
		//echo $this->r_db->last_query();
		if($count == 0){
			$sql = sprintf("INSERT INTO Sold_items_tbl (`si_package_no`, `si_package_title`, `si_package_des`, `si_programme_no`, `si_type`, `si_type_video_no`, `si_unit`, `si_unit_value`, `si_status`) SELECT `sp_pk`, `sp_title`, `sp_des`, '%d', '%s', %d, `sp_unit`, `sp_unit_value`, %d FROM `Sold_packages_tbl` WHERE `sp_pk`=%d", $programme_no, $video_type, $video_no, $status, $spackage_no);
			$this->w_db->simple_query($sql);
			$id = $this->w_db->insert_id();
			//echo $this->w_db->last_query();
			return $id;
		}else{
			$this->w_db->where('si_package_no', $spackage_no);
			$this->w_db->where('si_type', $video_type);
			$this->w_db->where('si_type_video_no', $video_no);
			$this->w_db->set('si_status', $status);
			$this->w_db->update($this->table_name);
			$result = $this->w_db->affected_rows();
			//echo $this->w_db->last_query();
			return $result;
		}
	}
	
	public function update_package_status_by_video ($video_type, $video_no)
	{
		$this->w_db->where('si_type', $video_type);
		$this->w_db->where('si_type_video_no', $video_no);
		$this->w_db->set('si_status', '0');
		$this->w_db->update($this->table_name);
		$result = $this->w_db->affected_rows();
		//echo $this->w_db->last_query();
		return $result;
	}
	
	public function get_package_by_video ($video_type, $video_no, $show=null)
	{
		$this->r_db->select('sp_pk,sp_title,sp_des,sp_type,sp_cost,sp_price,sp_status,sp_time_creat,sp_time_update');
		$this->r_db->join('Sold_packages_tbl', 'Sold_packages_tbl.sp_pk = Sold_items_tbl.si_package_no', 'left');			
		$this->r_db->where('si_type', $video_type);
		$this->r_db->where('si_type_video_no', $video_no);
		$this->r_db->where('si_status', '1');
		if(!empty($show) && $show == '1'){
			$this->r_db->where('Sold_packages_tbl.sp_show', '1');
		}
		$this->r_db->group_by('si_package_no');
		$query = $this->r_db->get($this->table_name);
		//echo $this->r_db->last_query();
		return $query;
	}
}
