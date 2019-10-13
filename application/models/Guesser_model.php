<?php

/**
 * Run some tests and display their results.
 *
 * @category Test
 * @package  Dashman
 * @author   Ignatius Yesigye <ignatiusyesigye@gmail.com>
 * @license  MIT <mit.com>
 * @link     null
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Run some tests and display their results.
 *
 * @category Test
 * @package  Dashman
 * @author   Ignatius Yesigye <ignatiusyesigye@gmail.com>
 * @license  MIT <http://opensource.org/licenses/MIT>
 * @link     null
 */
class Guesser_Model extends CI_Model
{
    /**
     * @var array database in use.
     */
    public $database;

    public $column_types = [];
    
    /**
     * Constructor
     **/
    public function __construct()
    {
        parent::__construct();
        
        // $this->load->model('dashman_model');
        // $this->database = $this->dashman_model->database;
        
        $this->load->config('dashman');
        $this->column_types = $this->config->item('column_name_types');
    }
    

    /**
     * Return database entities.
     *
     * Entities are the main things the database is collecting data about.
     * For example in a store database tables "users" and "products" are
     * considered entities while "users_products" is not.
     *
     * @return array 
     **/
    public function entities()
	{
        $this->load->driver('cache', ['adapter' => 'apc', 'backup' => 'file']);

        $entities = $this->cache->get('entities');

        // Attempt to get entities from cache.
        if ( ! $entities) {
            
            $this->load->model('mapping_model', 'map');

            $entity_list = array(); // list of the main tables.
            $others_list = array(); // list of the non-entity tables.
            $pushed_list = array(); // list of tables already added.
            
            $tables_list = $this->db->list_tables();
            
            foreach ($tables_list as $key => $table) {
                // To indicate a one-to-many relationship.
                // Query a referenced table.
                $this->db->select('table_name, referenced_table_name');
                $this->db->where('TABLE_SCHEMA', $this->database);
                // $this->db->where('table_name', $table);
                $this->db->where('referenced_table_name', $table);
                $this->db->from('information_schema.key_column_usage');
                $refs = $this->db->get()->result();
                
                if (!empty($refs)) {
                    foreach ($refs as $reference) {
                        // To indicate a many-to-many relationship.
                        // Query a mapping/pivot table for the referenced table.
                        $this->db->select('table_name, referenced_table_name');
                        $this->db->where('TABLE_SCHEMA', $this->database);
                        $this->db->where('table_name', $reference->table_name);
                        $this->db->where('table_name !=', $table);
                        $this->db->where('referenced_table_name !=', $table);
                        $this->db->from('information_schema.key_column_usage');
                        $pivot = $this->db->get()->result();
                        
                        // Check that relationship is via a mapping table.
                        if (!empty($pivot)) {
                            // Remove pivot tables from table list.
                            unset($tables_list[array_search($pivot[0]->table_name, $tables_list)]);
                            // Insert in enity list with the number of references as index.
                            $entity_list[count($refs)] = array(
                                'name' => $table,
                                'rows' => $this->db->count_all($table),
                            );
                            // Indicate that we added this table.
                            array_push($pushed_list, $table);
                        } else {
                            // Skip if table references to itself
                            if ($table == $reference->referenced_table_name) continue;
                            
                            // Relationship is not via a mapping table.
                            $entity_list[count($refs)] = array(
                                'name' => $reference->referenced_table_name,
                                'rows' => $this->db->count_all($reference->referenced_table_name),
                                'pivot' => $table.".".$reference->table_name."__".$reference->referenced_table_name
                            );
                            // Indicate that we added this table.
                            array_push($pushed_list, $reference->referenced_table_name);
                        }
                        // Remove referenced tables from table list.
                        unset($tables_list[array_search($reference->table_name, $tables_list)]);
                    }
                } else {
                    // Table does not reference to another table
                    $this->db->select('table_name');
                    $this->db->where('TABLE_SCHEMA', $this->database);
                    $this->db->where('table_name', $table);
                    $this->db->where('referenced_table_name !=', null);
                    $this->db->where('referenced_table_name !=', $table);
                    $count = $this->db->count_all_results('information_schema.key_column_usage');
                    
                    // Add to entity list only when table has no references and
                    // has not already been added to the entitiy list.
                    if ($count === 0 && !in_array($table, $pushed_list)) {
                        array_push($others_list, [
                                'name' => $table,
                                'rows' => $this->db->count_all($table),
                            ]
                        );
                    }
                }
            }
            
            // Sort entities by highest number of relationships.   
            krsort($entity_list);
            array_values($entity_list); // Reorder the keys.
            
            $entities = array_merge($entity_list, $others_list);

            // Save into the cache for 24 hours
            // $this->cache->save('entities', $entities, 86400);
        } else {
            foreach ($entities as $table) {
                // Verifing data integrity.
                if ( ! $this->db->table_exists($table)) {
                    // Tables recorded in cache that dont really exist.
                    $this->cache->delete('entities');
                    // re-run function if we find data inconsistences.
                    return $this->entities();
                }
            }
        }

        return $entities;
    }

    /**
     * Check if a table has an image field
     *
     * @param string $table a table in the database
     *
     * @return string 
     **/
    public function imageFields($table)
    {
        $this->load->config('dashman');
        $this->column_types = $this->config->item('column_name_types');
        
        $resultData = array();
        $fields = $this->db->field_data($table);

        foreach ($fields as $key => $fieldData) {

            if (in_array($fieldData->name, $this->column_types['image'])) {
                array_push($resultData, $fieldData->name);
            }
        }
        return $resultData;
    }

    /**
     * Check if a table has an image field
     *
     * @param string $table a table in the database
     * @param string $limit     the number of columns to return
     *
     * @return string 
     **/
    public function imageData($table, $limit=0)
    {
        $data  = array();
        $imgCols = $this->imageFields($table);

        if (!empty($imgCols)) {
            $column = $imgCols[0];

            $fields = $this->db->list_fields($table);
            
            unset($fields[array_search($column, $fields)]);
            // Reorder the keys.
            $fields = array_values($fields);
    
            $this->db->limit($limit);
            $this->db->select($column.' AS image');
            $this->db->select($fields[0].' AS description');
            $this->db->from($table);
            $data = $this->db->get()->result_array();
        }
        return $data;
    }

    /**
     * Check if a table has checkbox fields
     *
     * @param string $table a table in the database
     *
     * @return array 
     **/
    public function checkboxFields($table)
    {
        $fieldList = array();
        $fields = $this->db->field_data($table);
        
        foreach ($fields as $key => $fieldData) { 
            if (strpos(strtolower($fieldData->type), 'int') !== false) {           
                if ($fieldData->max_length == 1) {
                    // Input is of type BOOLEAN.
                    array_push($fieldList, $fieldData->name);
                }
            }
        }
        
        return $fieldList;
    }

    /**
     * Check if a table has summation fields
     *
     * @param string $table a table in the database
     *
     * @return array 
     **/
    public function totalFields($table)
    {
        $fieldList = array();
        $fields = $this->db->field_data($table);
        
        foreach ($fields as $key => $fieldData) { 
            if (strtolower($fieldData->type) == 'double'
                || strtolower($fieldData->type) == 'decimal'
                && $fieldData->max_length > 1
            ) {           
                // Field is an integer with a max length constraint greater than 1.
                array_push($fieldList, $fieldData->name);
            }
        }
        
        return $fieldList;
    }
    
    /**
     * Check if a table has date fields
     *
     * @param string $table a table in the database
     *
     * @return array 
     **/
    public function dateFields($table)
    {
        $fieldList = array();
        $fields = $this->db->field_data($table);

        foreach ($fields as $key => $fieldData) {
            if ($fieldData->type == 'datetime' || $fieldData->type == 'date' ) {
                array_push($fieldList, $fieldData->name);
            }
        }

        return $fieldList;
    }

	/**
	 * Check for a unique column in a table.
	 *
	 * @param string $table target table
	 *
	 * @return string
	 *
	 **/
	public function uniqueKey($table)
	{
        $column = '';

        $meta = $this->db->query('show fields from '.$table)->result_array();

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
     * Generate statistics data for tables.
     * A statistics table contains both a field with totals or summations and
     * a field with dates.
     *
     * @param string $limit number of tables to return
     *
     * @return array 
     **/
    public function statistics($limit=4)
    {
        $stats = array();
        $tableList = $this->db->list_tables();
        // Remove tables that should remain hidden.
        $tableList = $this->dashman_model->spliceTables($tableList);

        foreach ($tableList as $key => $table) {
            $dates = $this->dateFields($table);
            $totals = $this->totalFields($table);

            if (!empty($dates) && !empty($totals)) {
                $dateCol = $dates[0];
                $totalCol = $totals[0];
                // Get grand total of current month.
                $this->db->select($dateCol.' AS month');
                $this->db->select('IFNULL(SUM('.$table.'.'.$totalCol.'), 0) AS value');
                $this->db->like($dateCol, date('Y-m'));
                $this->db->group_by($dateCol);
                $curData = $this->db->get($table)->result_array();
                $curVal = empty($curData) ? 0.00 : intval($curData[0]['value']);
                
                // Get grand total of last month.
                $this->db->select($dateCol.' AS month');
                $this->db->select('IFNULL(SUM('.$table.'.'.$totalCol.'), 0) AS value');
                $this->db->like($dateCol, date('Y-m', strtotime('last month')));
                $this->db->group_by($dateCol);
                $lstData = $this->db->get($table)->result_array();
                $lstVal = empty($lstData) ? 0.00 : intval($lstData[0]['value']);
                
                // Compute a percentage difference.
                $percent_description = 'increase';
                if ($lstVal == 0 || $curVal == 0) {
                    $percent_difference = '0%';
                } else {
                    $percent_difference = round((($curVal-$lstVal)/$lstVal)*100, 1).'%';
                    if ($percent_difference < 0) {
                        $percent_description = 'decrease';
                        $percent_difference = ($percent_difference * -1).'%';
                    }
                }
                // Get the Grand total.
                $gtQuery = $this->db->select_sum($totals[0])->get($table)->result_array();
                $grdTotal = empty($gtQuery) ? 0 : $gtQuery[0][$totalCol];

                array_push($stats, array(
                    'name' => $table,
                    'total'=> $grdTotal,
                    'diff' => $percent_difference,
                    'type' => $percent_description,
                    )
                );
            } else {
                if (!empty($totals)) {
                    $totalCol = $totals[0];
                    // Get grand total.
                    $this->db->select('IFNULL(SUM('.$table.'.'.$totalCol.'), 0) AS value');
                    $grdData = $this->db->get($table)->result_array();
                    $grdTotal = empty($grdData) ? 0.00 : intval($grdData[0]['value']);

                    array_push($stats, array(
                        'name' => $table,
                        'total'=> $grdTotal,
                        'rows' => $this->db->count_all($table),
                    ));
                }
            }

            if (count($stats) == $limit) break;
        }
        return $stats;
    }
    
    /**
	 * Guess the field type based on the field metadata
	 * 
	 * @param object $fieldData field data metadata
	 *
	 * @return string
	 **/
	public function field($field_data)
	{
        $name = strtolower($field_data->name);
        $type = strtolower($field_data->type);

		if ($type == 'text') {
            // Field type is "text"
			return 'text';
		}

		if (in_array($name, $this->column_types['image'])) {
            // Field name indicates image type
			return 'image';
		}
        
		if ($name == 'password' || $name == 'confirm_password') {
            // Field name indicates password type
			return 'password';
		}
        
		if ($name == 'email' || $name == 'e-mail') {
            // Field name indicates email type
			return 'email';
		}
		
        if (strpos($type, 'date') !== false || $type == 'timestamp') {
            // Field name indicated a date type
			return 'date';
		}
        
		if (strpos($type, 'int') !== false) {
            // Field type contains word "int" (int, tinyint, smallint etc)
			
            // Return boolean for fields with max_length constraint of 1.
			return ($field_data->max_length == 1) ? 'boolean' : 'int';
        }
        
        if ($type == 'double' || $type == 'decimal' || $type == 'double') {
            // Field type is of float type
            return 'double';
        }

		// Return default type.
		return $type;
    }
    
    /**
     * Guess a column holding a title or description
     * 
     * @return array
     */
    public function screenName($table, $except = [])
    {
        $screen_name = '';
        $fields = $this->db->field_data($table);
        
		foreach ($fields as $key => $value) {
            if ( strpos(strtolower($value->type), 'char') !== false
                || $value->type == 'email' &&
                !in_array($value, $except)) {
				$screen_name = $value->name;
                break;				
			}
			if ($screen_name == '')  $screen_name = $value->name;
        }
        
        return $screen_name;
    }

    /**
     * Search for the first field of a given type in a table
     * 
     * @param $table table to search against
     * @param $type  type of field to search for
     * 
     * @usage fieldWhereType('products', 'image');
     * finds a field in products table that has image entries
     * 
     * @return string matched field name
     */
    public function fieldWhereType($table, $type, $except = [])
    {
        $this->load->model('mapping_model', 'map');
        
        $fields = $this->db->field_data($table);
        $fields = array_values($this->map->spliceFields($table, $fields, $except));

        foreach ($fields as $key => $field) {
            if (strpos($this->field($field), $type) !== false) {
                return $field->name;
            }
        }
        
        // $low  = 0;
        // $high = count($fields) - 1;
        // var_dump($fields);
        
        // $times = 0;
        // while ($low <= $high) {
        //     $times ++;
        //     $mid = intval(($low + $high) / 2);
        //     $field_type = $this->field($fields[$mid]);
            
        //     var_dump(strcasecmp($field_type, $type));
        //     if (strcasecmp($field_type, $type) < 0) {
        //         $low = $mid + 1;
        //     } elseif (strcasecmp($field_type, $type) > 0) {
        //         $high = $mid - 1;
        //     } else {
        //         return $fields[$mid]->name;
        //     }
        // }

        return  null;
    }
}

/* End of file Guesser.php */
/* Location: ./application/models/Guesser.php */
