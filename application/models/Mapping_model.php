<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mapping_model extends CI_Model {

	/**
     * @var array database in use.
     */
	public $database;
    
    /**
     * Return database tables.
     *
     * @return array 
     **/
    public function tables()
    {
        return $this->db->list_tables();
    }

    public function tableFields(String $table, Array $options = [])
    {
        $fields = array();
		// Set default options
		if (!isset($options['except'])) $options['except'] = [];
		if (!isset($options['references'])) $options['references'] = false;
		// Get all fields for the table
        $list = $this->db->field_data($table);
        
        foreach ($list as $field) {
            if (!in_array($field->name, $options['except'])) {
                array_push($fields, $field);
			}
			if (isset($options['limit'])) {
                if(count($fields) == $options['limit']) break;
			}
        }
        
        return $this->_metaData($table, $fields);
    }

	/**
     * Change the order of table fields.
	 * This method is used to format fields for display in a table.
     * primary key fields are set to always take first position
     *
     * @param array $fields  Table fields object
     * @param array $reorder Order of fields by type. Only 4 types are supported
     *
     * @return array 
     **/
    public function orderFields(Array $fields, Array $reorder = [])
    {
		// Set the default order.
		$order = [
			1 => 'image', // images come first
			2 => 'string', // strings come second
			3 => 'figure', // ints, floats come third
			4 => 'boolean', // booleans come last
		];
		
		// Override default with defined order.
		if(!empty($reorder)) $order = $reorder;
		
		$image_key = (($i = array_keys($order, 'image')) ? $i[0] : 0);
		$string_key = (($i = array_keys($order, 'string')) ? $i[0] : 0);
		$figure_key = (($i = array_keys($order, 'figure')) ? $i[0] : 0);
		$boolean_key = (($i = array_keys($order, 'boolean')) ? $i[0] : 0);
		
		$result = [1=>[], 2=>[], 3=>[], 4=>[]];

        foreach ($fields as $key => $field) {
            if($field->key === 'PRI') {
                array_push($result[$image_key], $field);
                continue;
            }
            
            switch ($field->type) {
				case 'image':
                    array_push($result[$image_key], $field);
                    break;
                
                case 'boolean':
                    array_push($result[$boolean_key], $field);
                    break;
                
                case 'int':
                    array_push($result[$figure_key], $field);
                    break;
					
				case 'double':
                    array_push($result[$figure_key], $field);
                    break;
					
				case 'float':
                    array_push($result[$figure_key], $field);
                    break;
                    
				default:
                    array_push($result[$string_key], $field);
                    break;
            }
        }

		// Re-build and return and ordered fields object.
        return array_merge($result[1], $result[2], $result[3], $result[4]);
    }
    
    /** Is a table related through a pivot table.
     *
     * This function considers tables that reference to the given table through a mapping or pivot table.
     * These pivot tables are used primarily to map many-to-many relationships and there is often no need
     * to access them directly.
     *
     * @param string $table table name of referenced table.
     *
     * @return array 
     **/
    public function hasPivot($table)
    {
		$this->database = "scotchbox";
        // Query a referenced table. (one-to-many relationship)
        $this->db->select('table_name, referenced_table_name');
        $this->db->select('column_name, referenced_column_name');
        $this->db->where('TABLE_SCHEMA', $this->database);
        $this->db->where('referenced_table_name', $table);
        $this->db->from('information_schema.key_column_usage');
        $refs = $this->db->get()->result();
        
        if (!empty($refs)) {
            foreach ($refs as $field) {
                // Query this table that this table refer
                $this->db->select('table_name, referenced_table_name');
                $this->db->select('column_name, referenced_column_name');
                $this->db->where('table_name', $field->table_name);
                $this->db->where('referenced_table_schema', $this->database);
                $this->db->where('referenced_table_name !=', $table);
                $this->db->where('referenced_column_name !=', null);
                // $count = $this->db->get('information_schema.key_column_usage')->result();
                $count = $this->db->count_all_results('information_schema.key_column_usage');
            }
		}
        
        return empty($count) ? false : true;
    }

    /**
	 * Find the referenced table
	 *
	 * @param string $table table name
	 *
	 * @return string
	 *
	 **/
	public function parent($table)
	{
		$this->db->select('referenced_table_name');
        $this->db->where('TABLE_SCHEMA', $this->database);
        $this->db->where('table_name', $table);
        $this->db->where('referenced_table_name !=', null);
        $this->db->from('information_schema.key_column_usage');
        $refs = $this->db->get()->result();

        return (!empty($refs)) ? $refs[0]->referenced_table_name : null;
    } 
    
    /**
	 * Find the referencing table
	 *
	 * @param string $table table name
	 *
	 * @return string
	 *
	 **/
	public function child($table)
	{
		$this->db->select('table_name');
        $this->db->where('TABLE_SCHEMA', $this->database);
        $this->db->where('referenced_table_name', $table);
        $this->db->where('table_name !=', null);
        $this->db->from('information_schema.key_column_usage');
        $refs = $this->db->get()->result();

        return (!empty($refs)) ? $refs[0]->table_name : null;
	}

    /**
     * Get an object representation of a pivoting table
    */
    public function refTable($table, $ref_table)
    {
        $this->db->select('column_name, referenced_table_name, referenced_column_name');
        $this->db->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE');
        $this->db->where('table_schema', $this->database);
        $this->db->where('table_name', $ref_table);
        $this->db->where('referenced_table_name', $table);
        $ref = $this->db->get()->result_array();

        return empty($ref) ? '' : $ref[0];
    }

    /**
     * Get an object representation of a pivoting table
    */
    public function pivotTable($table, $ref_table)
    {
        $mapping = [];

        $pivot_table = $this->db->query("
            SELECT FK.table_name
            FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` FK
            WHERE FK.table_schema = '".$this->database."'
            GROUP BY `table_name`
            HAVING SUM(CASE WHEN FK.referenced_table_name = '".$table."' THEN 1 ELSE 0 END) > 0 AND
                    SUM(CASE WHEN FK.referenced_table_name = '".$ref_table."' THEN 1 ELSE 0 END) > 0
        ")->result();
        
        if ( ! empty($pivot_table)) {
            // Convert object to string
            $pivot_table = $pivot_table[0]->table_name;

            if ($pivot_table == $ref_table) {
                // self-referencing table
                $this->db->select('column_name, referenced_table_name, referenced_column_name');
                $this->db->where('table_schema', $this->database);
                $this->db->where('table_name', $pivot_table);
                $this->db->where('referenced_table_name !=', null);
                $this->db->where('referenced_table_name !=', $pivot_table);
                $this->db->where('table_name !=', $table);
                $mapping[$table] = $this->db->get('information_schema.key_column_usage')->result();
            } else {
                // Query pivot table mapping
                $this->db->select('column_name, referenced_table_name, referenced_column_name');
                $this->db->where('table_schema', $this->database);
                $this->db->where('table_name', $pivot_table);
                $this->db->where('referenced_table_name !=', null);
                $this->db->where('referenced_table_name !=', $pivot_table);
                $this->db->where('table_name !=', $table);
                $mapping[$pivot_table] = $this->db->get('information_schema.key_column_usage')->result();
            }
            

        }

        return $mapping;
    }

    /** Shows a table's references.
     *
     * This function considers references to the given table through a mapping or pivot table.
     * These pivot tables are used in map many-to-many relationships
     * And direct references that are used in one-to-one relationships
     *
     * @param string $table table name of referenced table.
     *
     * @return array associative array of both direct references and pivoted references
     **/
    public function tableReferences($table)
    {
        $result_array['direct'] = [];
        $result_array['pivoted'] = [];
        $processed_tables = [];

        // Turn caching on
        // $this->db->cache_on();

        // Query a referenced table. (one-to-many relationship)
        $this->db->select('table_name, referenced_table_name');
        $this->db->select('column_name, referenced_column_name');
        $this->db->where('TABLE_SCHEMA', $this->database);
        $this->db->where('referenced_table_name', $table);
        $this->db->from('information_schema.key_column_usage');
        $refs = $this->db->get()->result();
        
        if (!empty($refs)) {
            
            foreach ($refs as $key => $field) {
                // Query for tables that reference the table in question
                // These are tables with foreign keys pointing to table in question
                $this->db->select('table_name, referenced_table_name');
                $this->db->where('table_name', $field->table_name);
                $this->db->where('referenced_table_schema', $this->database);
                $this->db->where('referenced_column_name !=', null);
                $foreign_links = $this->db->get('information_schema.key_column_usage')->result();
                
                foreach ($foreign_links as $link) {

                    if ( ! in_array($link->table_name, $processed_tables)
                      && ! in_array($link->table_name, $result_array['pivoted'])
                    ) {
                        // Procced if we have not yet processed this table or
                        // if the table does not already exist in our result arrays
                        
                        if (count($foreign_links) == 2 && $link->referenced_table_name !== $table) {
                            // Our table in question has a referencing table with two foreign keys
                            // and one of the key points(references) to a different table,
                            // We assume this is a pivot/mapping table.
                            
                            // Push pivot table as a reference
                            if ( ! in_array($link->referenced_table_name, $result_array['pivoted'])) {
                                array_push($result_array['pivoted'], $link->referenced_table_name);
                            }
                            
                            // Query for the other table that the pivot table is refering to
                            $this->db->select('table_name, referenced_table_name');
                            $this->db->where('table_name', $link->referenced_table_name);
                            $this->db->where('referenced_table_schema', $this->database);
                            $this->db->where('referenced_table_name !=', $table);
                            $this->db->where_not_in('referenced_table_name', $result_array['pivoted']);
                            $pivot_links = $this->db->get('information_schema.key_column_usage')->result();
                            
                            if ( ! empty($pivot_links)) {
                                // Remove the pivot table as a reference
                                unset($result_array['pivoted'][array_search($link->referenced_table_name, $result_array['pivoted'])]);
                                
                                // Add the main table that is being referenced
                                array_push($result_array['pivoted'], $pivot_links[0]->referenced_table_name);
                                // We are done with tables linked by pivot table
                                array_push($processed_tables, $pivot_links[0]->referenced_table_name);
                                array_push($processed_tables, $pivot_links[0]->table_name);
                                array_push($processed_tables, $link->referenced_table_name);
                            }
                            
                            // We are done with reference table
                            array_push($processed_tables, $link->table_name);
                            // Remove pivot reference table if it was added to direct reference tables.
                            $x = array_search($link->table_name, $result_array['direct']);
                            if (isset($result_array['direct'][$x])) unset($result_array['direct'][$x]);
                        } else {
                            
                            if ( ! in_array($link->table_name, $result_array['direct']) && ! in_array($link->table_name, $processed_tables)) {
                                // Add direct relationship
                                array_push($result_array['direct'], $link->table_name);
                            }
                        }
                    }
                }
            }
        }

        // Turn caching off
        // $this->db->cache_off();

        array_unique($result_array['direct']);
        array_unique($result_array['pivoted']);

        return $result_array;
    }

    /**
     * Generate graph data for a table.
     * A graphable table has both a field of double & dates types.
     *
     * @param string $table        a table in the database
     * @param string $amount_field a table in the database
     * @param string $date_field   a table in the database
     *
     * @return array 
     **/
    public function graph($table, $amount_field, $date_field)
    {
        $result = array();
        
        // Get amount of each month in a year.
        for ($i=1; $i < 13; $i++) {
            $year = date('Y');
            $date = ($i < 10) ? $year.'-0'.$i : $year.'-'.$i;
            
            $this->db->select($date_field.' AS month');
            $this->db->select("IFNULL(SUM($amount_field), 0) AS value");
            $this->db->like($date_field, $date);
            $this->db->group_by($date_field);
            $data = $this->db->get($table)->result_array();

            if ( ! empty($data[0]['month'])) {
                array_push($result, $data[0]);
            } else {
                array_push($result, [
                    'month' => $date,
                    'value' => 0.00
                ]);
            }
        }

        return $result;
    }

    public function diff($table, $amount_field, $date_field)
    {
        $result = array();
        
        // Get amount of this month.
        $this->db->select($date_field.' AS month');
        $this->db->select("IFNULL(SUM($amount_field), 0) AS value");
        $this->db->like($date_field, date('Y-m'));
        $this->db->group_by($date_field);
        $now = $this->db->get($table)->result();
        $current_total = empty($now) ? 0 : $now[0]->value;

        // Get amount of last month.
        $this->db->select($date_field.' AS month');
        $this->db->select("IFNULL(SUM($amount_field), 0) AS value");
        $this->db->like($date_field, date('Y-m', strtotime("last month")));
        $this->db->group_by($date_field);
        $last = $this->db->get($table)->result();
        $previous_total = empty($last) ? 0 : $last[0]->value;
        
        // Compute a percentage difference.
        if (($previous_total - $current_total) < 0) {
            return '<i class="fa fa-arrow-up text-success"></i>'.((-1)*($previous_total - $current_total)).'%';
        } else {
            return '<i class="fa fa-arrow-down text-danger"></i>'.($previous_total - $current_total).'%';
        }
    }

    /**
     * Calculate a total of amounts in a table.
     *
     * @param string $table        a table in the database
     * @param string $amount_field a field of int or double type.
     *
     * @return int 
     **/
    public function total($table, $amount_field = null)
    {
        if (is_null($amount_field)) {
            $this->load->model('guesser_model', 'guess');
            // Guess what field is of double type.
            $amount_field = $this->guess->fieldWhereType($table, 'double');
            // If none is found, guess what field is of int type.
            if (is_null($amount_field)) $amount_field = $this->guess->fieldWhereType($table, 'int');
        }

        // Summation field not found, bye!
        if (is_null($amount_field)) return null;

        $data = $this->db->select_sum($amount_field)->get($table)->result();

        // Return sum value if it exists.
        return ( ! empty($data)) ? $data[0]->$amount_field : null;
    }

    public function binary($array, $needle)
    {
        $times  = 0;
        $low  = 0;
        $high = count($array) - 1;
        
        while ($low <= $high) {
            $times ++;
            $mid = intval(($low + $high) / 2);

            if (strcasecmp($array[$mid], $needle) < 0) {
               $low = $mid + 1;
            } elseif (strcasecmp($array[$mid], $needle) > 0) {
                $high = $mid - 1;
            } else {
                return $mid;
            }
        }

        return  -1;
    }

	/**
	 * Check if a column is a unique key.
	 *
	 * @param string $column column name
	 *
	 * @return boolean
	 *
	 **/
	public function isUniqueKey($table, $column)
	{
		$this->db->limit(1);
		$this->db->select('COLUMN_NAME');
        $this->db->where('u.TABLE_SCHEMA', $this->db->database);
        $this->db->where('k.CONSTRAINT_TYPE', 'UNIQUE');
        $this->db->where('u.TABLE_NAME', $table);
        $this->db->where('u.COLUMN_NAME', $column);
        $this->db->join('INFORMATION_SCHEMA.TABLE_CONSTRAINTS k', 'k.CONSTRAINT_NAME = u.CONSTRAINT_NAME');
		$this->db->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE u');
		
		$found = $this->db->count_all_results();
		
        return ($found > 0) ? true : false;
	}

	/**
	 * Check if a column is a foreign key.
	 *
	 * @param string $column column name
	 *
	 * @return boolean
	 *
	 **/
	public function isForeignKey($table, $column)
	{
		$this->db->limit(1);
        $this->db->select('referenced_table_name as table');
        $this->db->select('referenced_column_name as field');
        $this->db->where('TABLE_SCHEMA', $this->database);
        $this->db->where('table_name', $table);
        $this->db->where('column_name', $column);
        $this->db->where('referenced_column_name !=', null);
        $this->db->where('referenced_table_name !=', null);
        $this->db->from('information_schema.key_column_usage');
		
		$found = $this->db->get()->result();
		
        return !empty($found) ? $found[0] : false;
	}

	/**
	 * Returns additional data for fields.
	 * 
	 * @param string $table name of table
	 * @param object $fields field data objects
	 *
	 **/
	private function _metaData($table, $fields)
	{
		$reindex = array();
		
        $meta = $this->db->query('show fields from '.$table)->result();
        
		foreach ($fields as $index => $field) {
            $reindex[$field->name] = $field;

			foreach ($meta as $val) {
                if($field->name === $val->Field) {
                    $reindex[$field->name]->key = $val->Key;
                    $reindex[$field->name]->null = ($val->Null == 'YES') ? true : false;
                    $reindex[$field->name]->extra = $val->Extra;
                    $reindex[$field->name]->type = $this->guess->field($field);
                    $reindex[$field->name]->foreign_key = $this->isForeignKey($table, $field->name);
                }
            }
        }
        
		return $reindex;
	}

	/**
	 * Returns general information about the server.
	 * 
	 * @return array
	 **/
	public function info()
	{
		return [
			'server' => $this->session->userdata('host'),
			'database' => $this->session->userdata('database'),
			'platform' => $this->db->platform(),
			'version' => $this->db->version(),
		];
	}
}

/* End of file Mapping_model.php */
/* Location: ./application/models/Mapping_model.php */
