<?php

class Mapping_model_test extends DBTestCase {
	
	public function setUp() {
		$this->resetInstance();
		$this->CI->load->model('Mapping_model');
		$this->obj = $this->CI->Mapping_model;
		$CI =& get_instance();
	}

    public function test_tables()
    {
		// $map = $this->newModel('Mapping_model');

        $expected = [
            0 => 'groups',
            1 => 'migrations',
            2 => 'orders',
            3 => 'product_categories',
            4 => 'product_images',
            5 => 'products',
            6 => 'users',
            7 => 'users_groups',
        ];
		
		$tables = $this->obj->tables();
		
        foreach ($tables as $key => $table) {
			echo $table;
            $this->assertEquals($expected[$key], $table);
        }
    }
}
