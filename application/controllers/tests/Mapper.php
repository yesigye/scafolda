<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mapper extends CI_Controller {
	
	protected $tables = [
		0 => 'groups',
		1 => 'migrations',
		2 => 'orders',
		3 => 'product_categories',
		4 => 'product_images',
		5 => 'products',
		6 => 'users',
		7 => 'users_groups',
	]; 
	
	public function __construct()
    {
		parent::__construct();
		$this->load->library('unit_test');
		$this->load->model('guesser_model', 'guess');
		$this->load->model('mapping_model', 'map');
		$this->output->enable_profiler(true);
    }

	public function index()
	{
		$expected = $this->tables;
		
		$tables = $this->map->tables();
		$this->unit->run($tables, 'is_array', 'Table list is an array');
		
		foreach ($tables as $key => $table) {
			$this->unit->run($table, $expected[$key], "Check table position",
				"table at index $key is $table, expected $expected[$key]"
				);
		}

		$this->fields($tables);
		
		echo $this->unit->report();
	}
	
	public function fields($tables)
	{
        foreach ($tables as $table) {
			$fields = $this->map->tableFields($table);
			$this->orderFields($fields, $table);
			$this->unit->run($fields, 'is_array', "Fields for $table is an array");
			foreach ($fields as $key => $field) {
				$this->unit->run($fields, 'is_array', "Column for field is an object");
			}
		}
	}

	public function orderFields($fields, $table)
	{
		$ordered = $this->map->orderFields($fields);
		$this->unit->run($ordered, 'is_array', "ordered fields for $table is array");
		$this->unit->run(count($ordered), count($fields), "ordered fields for $table not decremented");
	}
	
	public function pivots()
	{
		var_dump($this->map->hasPivot('users'));
		foreach ($this->tables as $key => $table) {
			if($table === 'users' || $table === 'groups') {
				echo $this->unit->run($this->map->hasPivot($table), 'is_true', "Is $table related through a pivot");
			}else{
				echo $this->unit->run($this->map->hasPivot($table), 'is_false', "Is $table related through a pivot");
			}
		}
	}
}

/* End of file Mapper.php */
/* Location: ./application/controllers/tests/Mapper.php */
