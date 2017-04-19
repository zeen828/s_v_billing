<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Pagination extends CI_Pagination
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function get_pagination_info() {
		$count_total = $this->total_rows;
		$page_total = ceil ( $this->total_rows / $this->per_page );
		$page = $this->cur_page;
		$page_previous = (($this->cur_page - 1) <= 0) ? 0 : ($this->cur_page - 1);
		$page_next = (($this->cur_page + 1) >= $page_total) ? $page_total : ($this->cur_page + 1);
		$page_size = $this->per_page;
		return array (
				'page_previous' => $page_previous,
				'page' => $page,
				'page_next' => $page_next,
				'page_size' => $page_size,
				'page_total' => $page_total,
				'count_total' => $count_total
		);
	}
	
	public function get_database_limit() {
		$start = (($this->cur_page - 1) * $this->per_page);
		$limit = $this->per_page;
		return array (
				'start' => $start,
				'limit' => $limit,
		);
	}
}

/* End of file MY_Pagination.php */
/* Location: ./application/library/MY_Pagination.php */