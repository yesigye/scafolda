<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This script may take longer that the default 30 sec to finish.
ini_set("max_execution_time", 600); // 10 minutes.
// Generating huge amounts of data may use more memory that usual.
ini_set('memory_limit', '512M'); // 512MB memory usage.

class Table_model extends CI_Model {

	protected $table;
	protected $generator;
	public $error = "";
	public $rows = 0;

    /**
	 * Return any errors that occur.
	 *
	 * @return string
	 */
	public function error()
	{
		return $this->error;
	}

    /**
	 * Sets table name.
	 *
     * @param string $table table name
	 *
	 * @return void
	 */
	public function set($table)
	{
        $this->table = $table;
	}
	
	/**
	 * Create a new table.
	 *
     * @param string $fieldsData fields meta data
	 *
	 * @return boolean
	 */
	public function create($fieldsData)
	{
		$this->load->dbutil();
		$this->load->dbforge();

		foreach ($fieldsData as $key => $field) {
			// Define field name as column.
			$column = $field['name'];
			// Initialize field parameters.
			$fields[$column] = array();
			
			// Assign foreign keys.
			if ($field['foreign_key'] !== '') {
				$segments = explode('.', $field['foreign_key']);
				// Get referenced table name.
				$refTableName = $segments[0];
				// Get referenced column name.
				$refColumnName = $segments[1];

				// For a column designated as foreign key, its constraints
				// must match those of the referenced column. To make sure of
				// this, we use constraints data for the referenced column
				$columnsData = $this->columnsData($refTableName);
				$refCol = $columnsData[$refColumnName];
				// Add column constraints to the fields array.
				$fields[$column]['type'] = $refCol->type;
				if ($def = $refCol->default) $fields[$column]['default'] = $def;
				$fields[$column]['constraint'] = $refCol->max_length;
				$fields[$column]['null'] = $refCol->null;
				if ($refCol->extra=='UNI') $fields[$column]['unique'] = true;
				if ($refCol->unsigned) $fields[$column]['unsigned'] = true;

				// Assign foreign keys.
				$foreignKey = 'CONSTRAINT FOREIGN KEY ('.$column.') REFERENCES '.$refTableName.'('.$refColumnName.')';
				if(isset($field['on_delete'])) $foreignKey .= ' ON DELETE '.$field['on_delete'];
				if(isset($field['on_update'])) $foreignKey .= ' ON UPDATE '.$field['on_update'];
				$this->dbforge->add_field($foreignKey);
			} else {
				// Add column constraints to the fields array.
				$fields[$column]['type'] = $field['type'];
				if ($field['default']) {
					$fields[$column]['default'] = $field['default'];
				}
				$fields[$column]['constraint'] = $field['length'] ? $field['length'] : false;
				$fields[$column]['auto_increment'] = isset($field['auto']) ? true : false;
				$fields[$column]['null'] = isset($field['null']) ? true : false;
				$fields[$column]['unique'] = isset($field['unique']) ? true : false;
				$fields[$column]['unsigned'] = isset($field['unsigned']) ? true : false;
			}
			
			// Assign primary keys.
			if ($field['index'] === 'primary') {
				$this->dbforge->add_key($column, true);
			}
		}
		// Add fields.
		$this->dbforge->add_field($fields);
		
		// Attempt to create table.
		return $this->dbforge->create_table($this->table);
	}

    /**
	 * Returns table meta data.
	 *
	 * @return array
	 */
	public function meta(String $table = '')
	{
		$this->db->select('table_collation');
		$this->db->select('Auto_increment');
		$this->db->select('Engine');
		$this->db->select('table_name');
		$this->db->select('Version');
		$this->db->select('table_rows');
		$this->db->select('from_unixtime(Create_time) created');
		$this->db->select('from_unixtime(Update_time) updated');
		$this->db->select('CONCAT(round(((Data_length + Index_length) / 1024 / 1024), 2), " MB") size');
		$this->db->from('information_schema.tables');
		$this->db->where('table_schema', $this->db->database);
		$this->db->where('table_name', $table ? $table : $this->table);
		$data = $this->db->get()->result_array();

		return (!empty($data)) ? $data[0] : $data;
	}

	/**
	 * Return rows in a table
	 *
	 * @param array $options query parameters
	 *
	 * @return array
	 */
    public function table_data($options = [])
	{
		// Set default query options
		if ( ! isset($options['start'])) $options['start'] = 10;
		if ( ! isset($options['limit'])) $options['limit'] = null;
		if ( ! isset($options['where'])) $options['where'] = [];
		if ( ! isset($options['search'])) $options['search'] = '';
		// Initiate columns.
		if(!isset($options['fields'])) $options['fields'] = [];
		
		if (isset($options['pivot'])) {
			// Query to know if the given table and a referenced table are
			// connected through another table (mapping/pivot table).
			$mapping = $this->map->pivotTable($this->table, $options['pivot']);
			$keys = array_keys($mapping);
			
			if ( ! empty($mapping)) {
				// Convert object to string
				foreach ($mapping[$keys[0]] as $key => $route) {
					if ($route->referenced_table_name == $this->table) {
						$this->db->select($route->referenced_column_name.' AS _ref_');
						break;
					}
				}
			}
			// We cache because we need to remember our filter/where clauses.
			// This allows us to get both the row count and then the records without rewriting the clauses
			$this->db->start_cache();
			$this->db->select($this->guess->screenName($this->table, [$route->referenced_column_name]));
			$this->db->from($this->table);

		} elseif (isset($options['reference'])) {
			// Start cache
			$this->db->start_cache();
			$this->db->select($options['reference'].' AS _ref_');
			$this->db->select($this->guess->screenName($this->table).' AS name');
			$this->db->from($this->table);
		} else {
			/*
			* -----------------------------------------------------------------------------------------------------------
			* First. Define fields of the table
			* -----------------------------------------------------------------------------------------------------------
			*/
			$fields = empty($options['fields']) ? $this->db->list_fields($this->table) : $options['fields'];

			/*
			* -----------------------------------------------------------------------------------------------------------
			* Second. we query a copy of the table with an appended column '_position_'.
			* This column will serve as a unique row identifier
			* -----------------------------------------------------------------------------------------------------------
			*/
			$order_by_clause = '';
			
			foreach ($fields as $key => $field) {
				// Select acceptable fields
				$this->db->select(is_object($field) ? "t.$field->name" : "t.$field", false);
				$order_by_clause .= ($order_by_clause ? ', ' : null ).(is_object($field) ? "t.$field->name" : "t.$field");
				
				// Stop. User defined this many fields to select
				if (isset($options['fields_limit']) && $options['fields_limit'] == $key-1) break;
			}

			// Include the position part in inner Query.
			$this->db->select("@rownum := @rownum + 1 AS _position_", false);
			$this->db->from($this->table." t JOIN (SELECT @rownum := 0) r) x");
			$table_with_pos = "(".$this->db->get_compiled_select();
			
			// Reset Query builder
			$this->db->reset_query();

			/*
			* -----------------------------------------------------------------------------------------------------------
			* Third. We build a query to select from this table copy
			* -----------------------------------------------------------------------------------------------------------
			*/
			$this->db->start_cache();
			
			foreach ($fields as $key => $field) {
				// Select acceptable fields
				$this->db->select(is_object($field) ? "x.$field->name" : "x.$field");
				
				// Stop. User defined this many fields to select
				if (isset($options['fields_limit']) && $options['fields_limit'] == $key-1) break;
			}
			$this->db->select('x._position_');
	
			
			// For WHERE clauses
			foreach ($options['where'] as $column => $value) {
				// Only when a given column is acually a field
				if (in_array($key, $fields)) $this->db->where("x.$column", $value);
			}

			$this->db->from($table_with_pos, false);
			
			// Query groups by search
			if (isset($options['search']) && $options['search'] !== '') {
				$search_clause = '';
				
				foreach (array_values($fields) as $key => $field) {
					// Select acceptable fields
					$search_clause .= (($key == 0) ? '' : ' OR ')."x.".(is_object($field) ? $field->name : $field)." LIKE '%".$options['search']."%'";
				}
	
				$this->db->where("($search_clause)", null, false);
			}
			
			// Count the items before applying pagination.
			$this->count = $this->db->count_all_results();
			
			// Limit number of objects in the results.
			// This is primarily for pagination purposes.
			if (isset($options['limit'])) $this->db->limit($options['limit'], $options['start']);
			
			// Apply ordering
			if (isset($options['order'])) {
				if ($options['order']['column'] == 'group') {
					$this->db->order_by('group_join.name', $options['order']['dir']);
				} else {
					$this->db->order_by('x.'.$options['order']['column'], $options['order']['dir']);
				}
			}
		}
		
		$result = $this->db->get()->result_array();
		
		// Don't forget to stop and clear the cache.
		$this->db->stop_cache();
		$this->db->flush_cache();

		return $result;
	}
	
	/**
	 * Return object in a table
	 *
	 * @param string  $table     name of the table
	 * @param int     $offset    index identifying the row
	 * @param boolean $do_filter allow selection of fields
	 *
	 * @return array
	 */
    public function getRowData($table, $offset, $fields=[], $screen_fields = true)
	{
		if ($screen_fields) {
			if (!empty($fields)) {
				foreach ($fields as $key => $field) $this->db->select($field);
			} else {
				$fields = $this->db->list_fields($table);
				// Select only permitted fields.
				foreach ($fields as $column) $this->db->select($column);
				// Include unique of primary key fields.
				$this->db->select($this->guess->uniqueKey($table));
			}
		}
		// To get row we use offset position not the usual where clause
		// because not all tables will have unique or primary keys
		$this->db->from($table)->limit(1, (int)$offset-1);
		$row = $this->db->get()->result_array();

		return empty($row) ? $row : $row[0];
	}

    /**
	 * Count rows in a table
	 *
	 * @param array $options configuration for return data
	 *
	 * @return array
	 */
    public function table_count_rows($options = array())
	{
		if ( ! isset($options['start'])) $options['start'] = 0;
		if ( ! isset($options['limit'])) $options['limit'] = null;
		
		$this->db->limit($options['limit'], $options['start']);
		return $this->db->count_all_results($this->table);
	}

	/**
	 * Return table field names.
	 *
	 * @param string $this->table
	 * @param array $options configuration for return data
	 *
	 * @return array
	 */
	public function fields($options = array())
	{
		$resultData = array();
		// Set default options
		if (!isset($options['except'])) $options['except'] = null;
		// Get all fields for the table
		$list = $this->db->list_fields($this->table);
		if (isset($options['splice'])) {
			$list = $this->dataspark->spliceFields($list);
		}
		foreach ($list as $field) {
			if ($options['except'] !== $field) {
				array_push($resultData, $field);
			}
			if (isset($options['limit'])) {
				if(count($resultData) == $options['limit']) break;
			}
		}
		return $resultData;
	}

	/**
	 * Return table field names.
	 *
	 * @param string $this->table
	 * @param array $options configuration for return data
	 *
	 * @return array
	 */
	public function fields_data($options = array())
	{
		$resultData = array();
		// Set default options
		if (!isset($options['except'])) $options['except'] = null;
		// Get all fields for the table
		$list = $this->db->field_data($this->table);
		$list = $this->dataspark->spliceFields($list);
		foreach ($list as $field) {
			if ($options['except'] !== $field) {
				array_push($resultData, $field);
			}
			if (isset($options['limit'])) {
				if(count($resultData) == $options['limit']) break;
			}
		}
		return $resultData;
	}

	/**
	 * Return table indexes.
	 * 
	 * 
	 * @return array
	 *
	 **/
	public function getIndexes()
	{
		$queryIndexes = $this->db->query('SHOW INDEX FROM '.$this->table);
		return is_object($queryIndexes) ? $queryIndexes->result_array() : array();
	}
    
    /**
	 * Delete multiple rows in a table.
	 * 
	 * @param array $keys positions of rows in the table
	 *
	 * @return boolean
	 **/
	public function deleteRows($keys)
	{
		$count_before = $this->db->count_all($this->table);
		$fields = $this->map->tableFields($this->table);
		$unique_column = $this->uniqueKey();

		// Initialize image fields
		$image_fields = [];

		foreach ($fields as $key => $field) {
			// Gather all fields that contain image files.
			if ($field->type == 'image') array_push($image_fields, $field->name);
		}

		if (!empty($image_fields)) $this->load->library('image');

		// resort array in descending order.
		// NOTE: When deleting rows by their positions in the table as opposed to their unique column,
		// we must delete from the bottom up otherwise, position integrity is compromized.
		// Given we need to delete rows "b" & "c" from table ["a", "b", "c", "d"]. If we deleted in ascending order
		// deleting from position 2 would delete "b" leaving ["a", "c", "d"], now, deleting from position 3 removes "d" 
		rsort($keys);

		foreach ($keys as $position) {
			// Get row data of a specified position
			$current = $this->getRowData($this->table, $position, [], false);

			foreach ($image_fields as $image_field) {
				// Delete images occurances from the disk.
				if (isset($current[$image_field])) $this->image->delete($current[$image_field]);
			}

			if ($unique_column) {
				// Only apply unique column in where clause.
				$this->db->where($unique_column, $current[$unique_column]);
			} else {
				// Use all columns in where clause.
				// NOTE: If two rows are exactly the same, they will both be deleted
				// This is a bad schema design anyway but am not to judge.
				foreach ($current as $field => $value) $this->db->where($field, $value);
			}

			$this->db->delete($this->table);
		}

		$count_after = $this->db->count_all($this->table);	
		$this->rows = $count_before - $count_after;
		
		return ($this->rows > 0) ? true : false;
	}
    
    /**
     * Realationship meta data of table.
     *
     * This function considers tables that reference to the given table directly and also
     * tables that reference to the given table through a mapping or pivot table.
     *
     * @return array 
     **/
    public function references()
	{
        $resultData = array();

        // Query a referenced table. (one-to-many relationship)
        $this->db->select('TABLE_NAME, REFERENCED_TABLE_NAME');
        $this->db->select('COLUMN_NAME, REFERENCED_COLUMN_NAME');
        $this->db->where('TABLE_SCHEMA', $this->db->database);
		$this->db->where('TABLE_NAME', $this->table);
        $this->db->where('REFERENCED_COLUMN_NAME !=', null);
        $this->db->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE');
		$refs = $this->db->get()->result();
		
		foreach ($refs as $field) {
			if ($field->REFERENCED_TABLE_NAME) {
				$resultData[$field->COLUMN_NAME] = $field->REFERENCED_TABLE_NAME.'.'.$field->REFERENCED_COLUMN_NAME;
			}
		}
		return $resultData;
	}
    
    /**
     * Realationship meta data of table.
     *
     * This function considers tables that reference to the given table directly and also
     * tables that reference to the given table through a mapping or pivot table.
     *
     * @return array 
     **/
    public function referenceMeta()
	{
        $resultData = array();

        // Query a referenced table. (one-to-many relationship)
        $this->db->select('TABLE_NAME');
        $this->db->select('COLUMN_NAME');
        $this->db->select('CONSTRAINT_NAME	');
        $this->db->select('REFERENCED_TABLE_SCHEMA');
        $this->db->select('REFERENCED_TABLE_NAME');
        $this->db->select('REFERENCED_COLUMN_NAME');
        $this->db->where('TABLE_SCHEMA', $this->db->database);
        $this->db->where('TABLE_NAME', $this->table);
        $this->db->where('REFERENCED_COLUMN_NAME !=', null);
		$this->db->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE');
		
		return $this->db->get()->result_array();
	}

	/**
     * Realationship data of a given table.
     *
     * This function considers tables that reference to the given table directly and also
     * tables that reference to the given table through a mapping or pivot table.
     *
     * @return array 
     **/
    public function relationships()
	{
		$resultData = array();
		
        // Query a referenced table. (one-to-many relationship)
        $this->db->select('TABLE_NAME, REFERENCED_TABLE_NAME');
        $this->db->select('COLUMN_NAME, REFERENCED_COLUMN_NAME');
        $this->db->where('TABLE_SCHEMA', $this->db->database);
        $this->db->where('REFERENCED_TABLE_NAME', $this->table);
        $this->db->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE');
        $refs = $this->db->get()->result();

        if (!empty($refs)) {
            
            foreach ($refs as $field) {
                // Define the reference table array structure.
                $resultData[$field->TABLE_NAME] = array(
                    'fields' => array(),
                    'pivot' => false // is a pivot table involved.
                );
                // Define the table and column a field references
                $resultData[$field->TABLE_NAME]['fields'][$field->COLUMN_NAME] = array(
                    'refTable' => $field->REFERENCED_TABLE_NAME,
                    'refColumn' => $field->REFERENCED_COLUMN_NAME,
                );
                
                // Query a pivot table for the referenced table. (many-to-many relationship)
                $this->db->select('TABLE_NAME, REFERENCED_TABLE_NAME');
                $this->db->select('COLUMN_NAME, REFERENCED_COLUMN_NAME');
                $this->db->where('TABLE_NAME', $field->TABLE_NAME);
                $this->db->where('REFERENCED_TABLE_SCHEMA', $this->db->database);
                $this->db->where('REFERENCED_COLUMN_NAME !=', null);
                $this->db->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE');
                $relation = $this->db->get()->result();

                if (count($relation) > 1) {
                    // Confirm that relationship is through pivot table.
                    $resultData[$field->TABLE_NAME]['pivot'] = true;
                }
                
                foreach ($relation as $col) {
                    // Define the table and column a field references
                    $resultData[$field->TABLE_NAME]['fields'][$col->COLUMN_NAME] = array(
                        'refTable' => $col->REFERENCED_TABLE_NAME,
                        'refColumn' => $col->REFERENCED_COLUMN_NAME,
                    );
                }
            }
            return $resultData;
        }
	}

	/**
	 * rename a table.
	 *
	 * @param string $newName new table name
	 *
	 * @return boolean
	 **/
	public function rename($newName)
	{
		$this->load->dbforge();
		return $this->dbforge->rename_table($this->table, $newName);
	}

	/**
	 * Empty an existing table.
	 *
	 * @return boolean
	 **/
	public function trucante()
	{
		// if(empty($child = $this->map->child($this->table))) {

		// 	$this->error = "no children";
		// } else {
		// 	$this->error = "This table has a";
		// }
		
		// return false;
		$this->rows = $this->db->count_all($this->table);

		$this->db->truncate($this->table);
		
		if ($this->db->count_all($this->table) === 0) {
			return true;
		} else {
			$this->error = "Someting went wrong on our end";
			return false;
		}
	}

	/**
	 * Delete an existing table.
	 *
	 * @return boolean
	 **/
	public function delete()
	{
		$this->load->dbforge();
		$this->dbforge->drop_table($this->table);
		if ($this->db->table_exists($this->table)) {
			return false;
		}
		return true;
	}

	/**
	 * Generate data for a table table.
	 *
     * @param $fieldsData post data for fields
     * @param $rows       number of rows to generate
     * 
     * @return response
	*/
	public function generate($fieldsData, $rows)
	{
		// Define referenced columns.
		$columnsData = $this->columnsData();
		// Define referenced columns.
		$refColumns = $this->references();
		// Count total rows before we begin.
		$count_before = $this->db->count_all($this->table);
		// Load the Faker library.
		$this->load->library('faker');
		$faker = Faker\Factory::create();

		foreach ($columnsData as $column) {
			if ($column->foreignKey) {
				// Add it to the fields array if it was not there before
				if(!isset($fieldsData[$column->name])) $fieldsData[$column->name] = '';
			}
		}
		
		for ($i=1; $i < $rows+1; $i++) {
			
			// Data to be generated for this row.
			$rowData = array();

			/* Generate data for every field */
			foreach ($fieldsData as $column => $value) {
				// Skip auto increment fields.
				if ($columnsData[$column]->auto) continue;

				if ($this->isForeignKey($column)) {
					// For foreign key fields. use the referencing table column
					// as the data source for the generator.
					$ref = $this->getReference($column);
					// Query a random row in the reference table.
					$this->db->limit(1);
					$this->db->from($ref['REFERENCED_TABLE_NAME']);
					$this->db->select($ref['REFERENCED_COLUMN_NAME']);
					$this->db->order_by($ref['REFERENCED_COLUMN_NAME'], 'RANDOM');
					$randomRow = $this->db->get()->result_array();
					
					if (!empty($randomRow)) {
						$rowData[$column] = $randomRow[0][$ref['REFERENCED_COLUMN_NAME']];
						// Move on to the next field.
						continue;
					} else {
						// Reference table has no data.
						$this->error = 'The referenced table '.$ref['REFERENCED_TABLE_NAME'].' must have data';
						continue;
					}
				}
				
				if($value) {
					// Only assign values that are not null

					$parts = explode('.', $value);
					// Define function and parameters that the faker
					// object will consume.
					$function = $parts[0];
					$param1 = isset($parts[1]) ? $parts[1] : null;
					$param2 = isset($parts[2]) ? $parts[2] : null;
					$param3 = isset($parts[3]) ? $parts[3] : null;
					$param4 = isset($parts[4]) ? $parts[4] : null;
					$param5 = isset($parts[5]) ? $parts[5] : null;
					$param6 = isset($parts[6]) ? $parts[6] : null;
					$param7 = isset($parts[7]) ? $parts[7] : null;
					$param8 = isset($parts[8]) ? $parts[8] : null;
					$row_value = $faker->$function($param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8);
					
					if(strlen($row_value) > $columnsData[$column]->max_length) {
						// Trim the value if it is too long for the column.
						$row_value = substr($row_value, 0, $columnsData[$column]->max_length-0);
					}
					
					$rowData[$column] = $row_value;
				}
			}

			// Insert data into table.
			$this->db->insert($this->table, $rowData);
		}
		
		// Count total rows after inserting data.
		$count_after = $this->db->count_all($this->table);
		$this->rows = $count_after - $count_before;


		return ($count_after > $count_before) ? true : false;
	}

	/**
	 * Automatically generate data for a table table.
	 * Data will be generated based on row type.
	 *
     * @param $rows number of rows to generate
     * 
     * @return response
	*/
	public function auto_generate($rows)
	{
		// Load the Faker library.
		$this->load->library('faker');
		$faker = Faker\Factory::create();	
		// Define detailed fields data.
		$fields = $this->columnsData();
		// We will need to guess data for columns.
		$this->load->library('guesser', array('faker' => $faker));
		
		// Count total rows after inserting data.
		$count_before = $this->db->count_all($this->table);
		
		$rowData = array();
		for ($i=1; $i < $rows+1; $i++) {
			// Data to be generated for this row.
			$rowData = array();

			/* Generate data for every field */
			foreach ($fields as $column => $field) {
				// Skip auto increment fields.
				if ($field->auto) continue;

				if ($this->isForeignKey($column)) {
					// For foreign key fields. use the referencing table column
					// as the data source for the generator.
					$ref = $this->getReference($column);
					// Query a random row in the reference table.
					$this->db->limit(1);
					$this->db->from($ref['REFERENCED_TABLE_NAME']);
					$this->db->select($ref['REFERENCED_COLUMN_NAME']);
					$this->db->order_by($ref['REFERENCED_COLUMN_NAME'], 'RANDOM');
					$randomRow = $this->db->get()->result_array();
					
					if (!empty($randomRow)) {
						$rowData[$column] = $randomRow[0][$ref['REFERENCED_COLUMN_NAME']];
						// Move on to the next field.
						continue;
					} else {
						// Reference table has no data.
						$this->error = 'The referenced table '.$ref['REFERENCED_TABLE_NAME'].' must have data';
						$this->rows = 0;
						// We cannot continue with a reference to an empty object.
						return false;	
					}
				}
				
				if ($string = $this->guesser->fieldName($field)) {
					// Generate data based on field name if possible.
					$rowData[$field->name] = $string;
				} else {
					// Otherwise generate data based on field type.
					$rowData[$field->name] = $this->guesser->fieldType($field);
				}
			}

			// Insert data into table.
			$this->db->insert($this->table, $rowData);
		}
		
		// Count total rows after inserting data.
		$count_after = $this->db->count_all($this->table);
		$this->rows = $count_after - $count_before;

		return ($count_after > $count_before) ? true : false;
	}

	/**
	 * Create a new entity.
	 * 
	 * @param string $table table name
	 *
	 * @return boolean
	 **/
	public function addEntity($table, $fields)
	{
		if ($this->validatePOST($table, $fields)) {
			$insert_data = $this->formatPOSTData($fields, $table);
			// Insert Data.
			$this->db->insert($table, $insert_data);
			
			return $this->db->affected_rows();
		}
		
		return false;
	}

	public function validatePOST($table, $fields, $allow_unique = true)
	{
		$fields = (object) $fields;

		foreach ($fields as $key => $field) {
			// Initiate validation rules.
			$rules = array();

			if ($field->extra === 'auto_increment' || $field->type == 'boolean' || $this->guess->field($field) === 'image') {
				// Skip auto-increment, boolean(checkbox) or image fields
				continue;
			}

			if (isset($field->foreign_key) && $field->foreign_key) {
				// Set rules foreign key fields
				
				if ($field->foreign_key->table !== $table) {
					// Foreign key references to another table.
					// Inserting empty data would trigger a foreign key fail.
					array_push($rules, 'required');
				} else {
					// Skip rules for foreign keys that reference to self table.
					continue;
				}
			}

			if ( ! $field->null || (isset($field->foreign) && $field->foreign == TRUE)) {
				// Fields set as "NOT NULL" or set as foreign keys are required.
				array_push($rules, 'required');
			}
			if ($field->max_length > 1) {
				// Rule for maximum length
				array_push($rules, 'max_length['.$field->max_length.']');
			}
			if ($field->type == 'email') {
				// Rule for email
				array_push($rules, 'valid_email');
			}
			if ($allow_unique && $this->isUniqueKey($table, $field->name)) {
				// Rule for unique fields
				array_push($rules, 'is_unique['.$table.'.'.$field->name.']');
			}
			if ($field->type == 'int') {
				// Rule for integers
				array_push($rules, 'integer');
			}

			// Set rules for the field.
			$this->form_validation->set_rules($field->name, $field->name.' field', $rules);
		}
		
		return $this->form_validation->run();
	}

	/**
	 * Get formatted data from POST.
	 * 
	 * @param string $table    table name
	 * @param string $offset   index identifying the row
	 * @param array  $row_data data object to insert
	 *
	 * @return boolean
	 **/
	public function formatPOSTData($fields, $table = null)
	{
		$fields = $this->map->tableFields($table, ['nofilter' => true]);

		$insert_data = array();
		// Insert post data.
		$postData = $this->input->post();

		// Populate insert data
		foreach ($fields as $key => $field) {

			// Foreign key references to its self table.
			if (is_object($field->foreign_key) && $field->foreign_key->table === $table) continue;

			// Skip auto increment fields.
			if ($field->extra === 'auto_increment') continue;

			switch ($field->type) {
				case 'image':
					// Skip the image fields. Deal with them separately.
					break;
				case 'boolean':
					// push boolean input data as 1 or 0
					$insert_data[$field->name] = boolval($this->input->post($field->name)) ? 1 : 0;
					break;
				default:
					if($this->input->post($field->name)) {
						// fields defined in POST data.
						$insert_data[$field->name] = $this->input->post($field->name);
					}elseif ( ! $field->null && ! $field->default && ! isset($insert_data[$field->name])) {
						// Field is not null and does not have a default value.
						// We force a empty value for this field for the insert to work.

						// TODO: Look out for the type of fields to decide what kind of
						// empty values to use - dates, floats, etc
						$insert_data[$field->name] = ($field->type == 'int') ? 0 : '';

					} else {
						// push non-null input data into insert_data
						if($this->input->post($field->name)) {
							$insert_data[$field->name] = $this->input->post($field->name);

						}
					}
					break;
			}
		}

		return $insert_data;
	}

	public function uploadImage($table, $key, $field) {
		// Row data for the Current table in question.
		$current = $this->getRowData($table, $key, false);
		
		foreach ($_FILES as $key => $file_data) {
			$upload_data = $this->input->post($field);
		}
		
		$this->load->library('image');
		
		if (isset($_FILES[$field.'$file$']['size']) && $_FILES[$field.'$file$']['size'] > 0) {
			// Handle uploaded file.
			if ($this->image->upload(array('field'=> $field.'$file$'))) {
								
				if ($upload_data['crop_width'] && $upload_data['crop_height']) {
					// Crop image to user defined properties.
					$this->image->crop(array(
						'width'  => $upload_data['crop_width'],
						'height' => $upload_data['crop_height'],
						'x_axis' => $upload_data['crop_x'],
						'y_axis' => $upload_data['crop_y'],
						'ratio'  => isset($upload_data['keep_ratio']) ? true : false
					));
					if (isset($upload_data['resize']) && $upload_data['resize']['width'] !== 0) {
						// Resize image to user defined properties.
						$this->image->resize(array(
							'width'  => $upload_data['resize']['width'],
							'height' => $upload_data['resize']['height'],	
						));
					}
				}
				// Delete the old image.
				if (isset($current[$field])) $this->image->delete($current[$field]);
				
				// Update image field in database.
				$this->db->update($table, [$field => $this->image->filepath]);

				return $this->db->affected_rows();
			} else {
				// Image was not uploaded.
				$this->set_error_message($this->image->error_message());
				log_message('error', 'Upload Error: '.$this->image->error_message());
				
				return false;
			}
			
		} elseif (isset($upload_data['file']) && $current[$field] !== $upload_data['file']) {
			// Delete the old image.
			if (isset($current[$field])) $this->image->delete($current[$field]);
			// Update image field in database.
			$this->db->update($table, [$field => $upload_data['file']]);

			return $this->db->affected_rows();
		}
	}
	
	/**
	 * Update an entity.
	 * 
	 * @param string $table    table name
	 * @param string $offset   index identifying the row
	 * @param array  $row_data data object to insert
	 *
	 * @return boolean
	 **/
	public function saveEntry($table, $position, $row_data)
	{
		$current = $this->getRowData($table, $position, false);
		
 		// Set the update data.
		foreach ($row_data as $field => $value) $this->db->set($field, $value);
		
		// Use current row as a reference.
		foreach ($current as $field => $value) $this->db->where($field, $value);
		
		$this->db->update($table);

		return $this->db->affected_rows();
	}

	/**
	 * Update an entity.
	 * 
	 * @param string $table table name
	 * @param array $key    index position of row
	 * @param array $field  fields meta data
	 *
	 * @return boolean
	 **/
	public function updateEntity($table, $key, $fields)
	{
		if ($this->validatePOST($table, $fields, false)) {
			$reference_updated = false;
			
			// Row data for the Current table in question.
			$current = $this->getRowData($table, $key, false);
			
			$references = $this->map->tableReferences($table);
			
			foreach ($references['pivoted'] as $ref_table) {
				
				// Get pivot table reference data.
				$pivot = $this->map->pivotTable($table, $ref_table);
				
				foreach ($pivot as $pivot_table => $ref_meta) {
					
					$ref_row_data = [];
					
					if ($ref_keys = $this->input->post($ref_table)) {
						// POST data is available for reference table
						
						foreach ($ref_keys as $key => $value) {
							
							foreach ($ref_meta as $reference => $ref_table) {
								
								if ($ref_table->referenced_table_name == $table) {
									// Pivot table column references the current table.
									$ref_row_data[$ref_table->column_name] = $current[$ref_table->referenced_column_name];
								} else {
									// Pivot table column references another table.
									// Set column data to keys provided in the POST data.
									$ref_row_data[$ref_table->column_name] = $value;
								}
							}
							// Count occurances of the pivot data.
							$existing = $this->db->where($ref_row_data)->count_all_results($pivot_table);
							
							if ($existing === 0) $this->db->insert($pivot_table, $ref_row_data);
						}
					} else {
						foreach ($ref_meta as $reference => $ref_table) {
							if ($ref_table->referenced_table_name == $table) {
								// Pivot table column references the current table.
								$ref_row_data[$ref_table->column_name] = $current[$ref_table->referenced_column_name];
								// Count occurances of the pivot data.
								$existing = $this->db->where($ref_row_data)->count_all_results($pivot_table);
								
								if ($existing > 0) $this->db->where($ref_row_data)->delete($pivot_table);
								break;
							}
						}
					}
					
					if ($this->db->affected_rows()) $reference_updated = true;
				}
			}
			
			$insert_data = $this->formatPOSTData($fields, $table);
			
			foreach ($fields as $index => $field) {
				if ($field->type == 'image') $this->uploadImage($table, $key, $field->name);
			}

			foreach ($current as $field => $value) {
				// Use current row as a reference.
				$this->db->where($field, $value);
			}

			$this->db->update($table, $insert_data);
			
			return $reference_updated ? $reference_updated : $this->db->affected_rows();
		} else {
			return false;
		}
	}

	/**
	 * Returns additional data for fields.
	 * 
	 * @param string $table name of table
	 * 
	 * @return object
	 *
	 **/
	public function columnsData($table = '')
	{
		if ($table == '') $table = $this->table;
		$data = array();
		$fields = $this->db->field_data($table);
		$meta = $this->db->query('show fields from '.$table)->result();
		foreach ($fields as $index => $field) {
			$data[$field->name] = $field;	
			$data[$field->name]->primaryKey = (bool) $field->primary_key;
			$data[$field->name]->key = $meta[$index]->Key;
			$data[$field->name]->null = ($meta[$index]->Null=='NO')?false:true;
			$data[$field->name]->extra = $meta[$index]->Extra;
			$data[$field->name]->auto = ($meta[$index]->Extra=='auto_increment')?true:false;
			$data[$field->name]->default = $meta[$index]->Default;
			$data[$field->name]->unsigned = (strpos($meta[$index]->Type, 'unsigned') !== false)?true:false;
			$data[$field->name]->foreignKey = $this->isForeignKey($field->name);
		}
		return $data;
	}

	/**
	 * Add a new table column.
	 * 
	 * @param array $fieldData column data parameters
	 * 
	 * @return boolean
	 *
	 **/
	public function addColumn($fieldData)
	{
		$this->load->dbforge();
		$column = $fieldData['name'];
		$params = array();

		// Assign foreign keys.
		if ($fieldData['foreign_key'] !== '') {
			$segments = explode('.', $fieldData['foreign_key']);
			// Get referenced table name.
			$refTableName = $segments[0];
			// Get referenced column name.
			$refColumnName = $segments[1];
			// For a column designated as foreign key, its constraints
			// must match those of the referenced column. To make sure of
			// this, we use constraints data for the referenced column
			$columnsData = $this->columnsData($refTableName);
			$refCol = $columnsData[$refColumnName];
			// Add column constraints to the fields array.
			$params[$column] = array('type' => $refCol->type);
			if ($def = $refCol->default) $params[$column]['default'] = $def;
			$params[$column]['constraint'] = $refCol->max_length;
			$params[$column]['null'] = $refCol->null;
			if ($refCol->extra=='UNI') $params[$column]['unique'] = true;
			if ($refCol->unsigned) $params[$column]['unsigned'] = true;
			// Atempt to create column
			if ($this->dbforge->add_column($this->table, $params)) {
				$this->load->helper('db');
				$key = $refTableName.'('.$refColumnName.')';
				$sql = add_foreign_key($this->table, $column, $key, $fieldData['on_delete'], $fieldData['on_update']);
				// Attempt to add the key
				$this->db->query($sql);
				if ($this->isForeignKey($column)) {
					return true;
				} else {
					// Rollback changes
					$this->deleteColumn($column);
					return false;
				}
			} else {
				return false;
			}
		} else {
			$params[$column] = array('type' => $fieldData['type']);

			if (isset($fieldData['length']) && trim($fieldData['length'] !== '')) {
				$params[$column]['constraint'] = $fieldData['length'];
			}
			if (isset($fieldData['default']) && trim($fieldData['default'] !=='')) {
				$params[$column]['default'] = $fieldData['default'];
			}
			if (isset($fieldData['unsigned']) && (bool)$fieldData['unsigned']) {
				$params[$column]['unsigned'] = true;
			}
			if (isset($fieldData['auto']) && (bool)$fieldData['auto']) {
				$params[$column]['auto_increment'] = true;
			}
			if (isset($fieldData['null']) && (bool)$fieldData['null']) {
				$params[$column]['null'] = true;
			} else {
				$params[$column]['null'] = false;
			}
			if (isset($fieldData['after']) && trim($fieldData['after'] !== '')) {
				$params[$column]['after'] = $fieldData['after'];
			}
			return $this->dbforge->add_column($this->table, $params);
		}
	}

	/**
	 * Delete a table column.
	 * 
	 * @param array $column column to be deleted
	 * 
	 * @return boolean
	 *
	 **/
	public function deleteColumn($column)
	{
		$this->load->dbforge();

		return $this->dbforge->drop_column($this->table, $column);
	}

	/**
	 * Add an index key to a table.
	 * 
	 * @param array $fieldData column data parameters
	 * 
	 * @return boolean
	 *
	 **/
	public function addKey($fieldData)
	{
		$this->load->dbforge();
		$column = $fieldData['column'];

		if ($fieldData['key'] == 'primary') {
			// Assign primary.
			$this->db->query('ALTER TABLE '.$this->table.' ADD CONSTRAINT pk_'.$this->table.' PRIMARY KEY ('.$column.')');
			return $this->isPrimaryKey($column);
		} elseif ($fieldData['key'] == 'unique') {
			// Assign primary.
			$this->db->query('ALTER TABLE '.$this->table.' ADD UNIQUE ('.$column.')');
			return $this->isUniqueKey($column);
		} elseif ($fieldData['key'] == 'foreign') {
			$this->load->helper('db');
			$segments = explode('.', $fieldData['reference']);
			// Get referenced table name.
			$refTableName = $segments[0];
			// Get referenced column name.
			$refColumnName = $segments[1];
			$key = $refTableName.'('.$refColumnName.')';
			$sql = add_foreign_key($this->table, $column, $key, $fieldData['on_delete'], $fieldData['on_update']);
			$this->db->query($sql);
			return $this->isForeignKey($column);
		} else {
			return false;
		}
	}

	/**
	 * Delete a table index.
	 * 
	 * @param array $key key to be deleted
	 * 
	 * @return boolean
	 *
	 **/
	public function deleteIndex($key)
	{
		// Attempt to add the key
		$this->db->query("DROP INDEX `$key` ON $this->table");
		$this->error = $this->db->error();
		foreach ($this->getIndexes() as $index) {
			if ($index['Key_name'] == $key) {
				return false;
				break;
			}
		}
		return true;
	}

	/**
	 * Delete a foreign key.
	 * 
	 * @param array $key key to be deleted
	 * 
	 * @return boolean
	 *
	 **/
	public function deleteForeignKey($column, $key)
	{
		$this->load->dbforge();
		$this->load->helper('db');
		// Attempt to add the key
		$this->db->query(drop_foreign_key($this->table, $key));

		if ($this->isForeignKey($column)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Modify a tables columns.
	 * 
	 * @param array $fields updated fields
	 * 
	 * @return boolean
	 *
	 **/
	public function editColumns($fields)
	{
		$this->load->dbforge();
		$modified = false;
		$columnsData = $this->columnsData();
		foreach ($fields as $name => $field) {
			$params = array();
			if ($columnsData[$name]->name !== $field['name']) {
				$params['name'] = $field['name'];
				$params['type'] = $field['type'];
				$params['constraint'] = $field['length'];
			}

			if (isset($field['unsigned']) && $columnsData[$name]->unsigned !== (bool)$field['unsigned']) {
				$params['name'] = $field['name'];
				$params['type'] = $field['type'];
				$params['constraint'] = $field['length'];
				$params['unsigned'] = true;
				$params['auto_increment'] = (isset($field['auto'])) ? true : false;
			}
			
			if (isset($field['auto']) && $columnsData[$name]->auto !== (bool)$field['auto']) {
				$params['name'] = $field['name'];
				$params['type'] = $field['type'];
				$params['constraint'] = $field['length'];
				$params['unsigned'] = (isset($field['unsigned'])) ? true : false;
				$params['auto_increment'] = true;
			}

			if ($columnsData[$name]->type !== strtolower($field['type'])) {
				$params['type'] = $field['type'];
			}
			if ($columnsData[$name]->max_length !== intval($field['length'])) {
				$params['type'] = $field['type'];
				$params['constraint'] = $field['length'];
			}
			if ($field['default'] == '') $field['default'] = null;
			
			if ($field['default'] && $columnsData[$name]->default !== $field['default']) {
				$params['default'] = $field['default'];
			}
			
			$field['null'] = (isset($field['null'])) ? true : false;

			if ($columnsData[$name]->null !== $field['null']) {
				$params['null'] = $field['null'];
				$params['type'] = $field['type'];
				$params['constraint'] = $field['length'];
			}

			if (!empty($params)) {
				$modified = $this->dbforge->modify_column($this->table, array(
					$name => $params
				));
			}
		}
		return $modified;
	}

	/**
	 * Check for a unique column in a table.
	 *
	 * @return string column name
	 *
	 **/
	public function uniqueKey()
	{
        $column = '';

        $meta = $this->db->query('show fields from '.$this->table)->result_array();

        foreach ($meta as $col) {
            if ($col['Key'] == 'PRI') {
                // This is a primary key.
                $column = $col['Field'];
            } elseif($col['Key'] == 'PRI' && $col['Null'] == 'NO') {
                // This is a non-nullable unique key.
                $column = $col['Field'];
            }
        }
        return $column;
	}

	/**
	 * Check if a column is a primary key.
	 *
	 * @param string $column column name
	 *
	 * @return boolean
	 *
	 **/
	public function isPrimaryKey($column)
	{
		$this->db->limit(1);
		$this->db->select('COLUMN_NAME');
        $this->db->where('u.TABLE_SCHEMA', $this->db->database);
        $this->db->where('k.CONSTRAINT_TYPE', 'PRIMARY KEY');
        $this->db->where('u.TABLE_NAME', $this->table);
        $this->db->where('u.COLUMN_NAME', $column);
        $this->db->join('INFORMATION_SCHEMA.TABLE_CONSTRAINTS k', 'k.CONSTRAINT_NAME = u.CONSTRAINT_NAME');
		$this->db->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE u');
		
		$found = $this->db->count_all_results();
		
        return ($found > 0) ? true : false;
	}

	/**
	 * Check if a column is a unique key.
	 *
	 * @param string $column column name
	 *
	 * @return boolean
	 *
	 **/
	public function isUniqueKey($column)
	{
		$this->db->limit(1);
		$this->db->select('COLUMN_NAME');
        $this->db->where('u.TABLE_SCHEMA', $this->db->database);
        $this->db->where('k.CONSTRAINT_TYPE', 'UNIQUE');
        $this->db->where('u.TABLE_NAME', $this->table);
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
	public function isForeignKey($column)
	{
        $this->db->limit(1);
		$this->db->select('COLUMN_NAME');
        $this->db->where('u.TABLE_SCHEMA', $this->db->database);
        $this->db->where('k.CONSTRAINT_TYPE', 'FOREIGN KEY');
        $this->db->where('u.TABLE_NAME', $this->table);
        $this->db->where('u.COLUMN_NAME', $column);
        $this->db->join('INFORMATION_SCHEMA.TABLE_CONSTRAINTS k', 'k.CONSTRAINT_NAME = u.CONSTRAINT_NAME');
		$this->db->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE u');

		$found = $this->db->count_all_results();
		
        return ($found > 0) ? true : false;
	}

	/**
	 * Returns a foreign key reference.
	 *
	 * @param string $column column name
	 *
	 * @return string
	 *
	 **/
	public function getReference($column)
	{
		$this->db->limit(1);
		$this->db->select('REFERENCED_TABLE_NAME');
		$this->db->select('REFERENCED_COLUMN_NAME');
        $this->db->where('TABLE_SCHEMA', $this->db->database);
        $this->db->where('REFERENCED_TABLE_NAME !=', null);
        $this->db->where('TABLE_NAME', $this->table);
        $this->db->where('COLUMN_NAME', $column);
        $this->db->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE');
		$results = $this->db->get()->result_array();

		return (empty($results)) ? $results : $results[0];
	}

	/**
	 * Returns the last entry in a table.
	 * 
	 * @param string $table name of table
	 *
	 * @return array table row data
	 **/
	public function lastRow($table)
	{
		// Count all rows in the table
		$count = $this->db->count_all($table);
		// Limit result to 1 using the count as an offset
		$this->db->limit(1, $count-1);
		$data = $this->db->get($table)->result_array();

		return (!empty($data)) ? $data[0] : $data;
	}

	/**
	 * Get time a table was last updated
	 * 
	 * This only works for MyISAM engine.
	 * 
	 * @param string $table name of a specific table
	 *
	 * @return array table name and update time
	 **/
	public function lastUpdated($table = null)
	{
		$tables = $this->db->list_tables();

		$this->db->limit(1);
		$this->db->where('UPDATE_TIME !=', null);
		
		if($tables) { $this->db->where_in('TABLE_NAME', $tables); }
		
		$this->db->select('TABLE_NAME AS table')->group_by('TABLE_NAME, UPDATE_TIME');
		
		if ($table) {
			$this->db->select('UPDATE_TIME AS time');
			$this->db->where('TABLE_NAME', $table);
		} else {
			$this->db->select('MAX(UPDATE_TIME) AS time');
			$this->db->order_by('UPDATE_TIME', 'DESC');
		}
		$this->db->where('TABLE_SCHEMA', $this->database);
		$data = $this->db->get('information_schema.tables')->result_array();

		return empty($data) ? $data : $data[0];
	}

	/**
	 * Set where clauses for database query.
	 * 
	 * @param array $options where column and value
	 **/
	private function _setOptions($options = array())
	{
		foreach ($options as $option => $value) {
			if ($option == 'q') {
				if ($this->table) {
					$fields = $this->db->list_fields($this->table);
					foreach ($fields as $key => $field) {
						$this->db->or_like($field, $value);
					}
				}
			} else {
				if ($option !== 'per_page') $this->db->where($option, $value);
			}
		}
    }

	/**
	 * Returns the last entry in a table.
	 * No need for auto_increment
	 * 
	 * @param string $count no of rows in the table
	 *
	 * @return array table row data
	 **/
	private function _lastRow($count)
	{
		// Limit result to 1 using the count as an offset
		$this->db->limit(1, $count-1);
		$data = $this->db->get($this->table)->result_array();

		return (!empty($data)) ? $data[0] : $data;
	}
}

/* End of file Table_model.php */
/* Location: ./application/models/Table_model.php */