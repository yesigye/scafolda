<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Guess data types of columns.
 *
 * @category Guess
 * @package  Dataspark
 * @author   Ignatius Yesigye <ignatiusyesigye@gmail.com>
 * @license  MIT <http://opensource.org/licenses/MIT>
 * @link     null
 */
class Guesser
{
    protected $generator;

    public function __construct($params)
    {
        $this->generator = $params['faker'];
    }

    /**
	 * Guess the field type based on the field constraints
	 * 
	 * @param object $field field data constraints
	 *
	 * @return string
	 **/
	public function fieldType($field)
	{
		switch (strtoupper($field->type)) {
            case 'BOOLEAN':
            case 'BOOLEAN_EMU':
                return 'boolean';
            case 'NUMERIC':
            case 'DECIMAL':
                $size = $field->max_length;
                return $this->generator->randomNumber($size + 2) / 100;
            case 'TINYINT':
                $size = $field->max_length;
                if ($size == 1) {
                    // assume a boolean data type
                    return mt_rand(0, 1);
                } else {
                    return mt_rand(0, 127);
                }
            case 'SMALLINT':
                return mt_rand(0, 32767);
            case 'INT':
            case 'INTEGER':
                return mt_rand(0, intval('2147483647'));
            case 'BIGINT':
                return mt_rand(0, intval('9223372036854775807'));
            case 'FLOAT':
                return mt_rand(0, intval('2147483647'))/mt_rand(1, intval('2147483647'));
            case 'DOUBLE':
            case 'REAL':
                return mt_rand(0, intval('9223372036854775807'))/mt_rand(1, intval('9223372036854775807'));
            case 'CHAR':
            case 'VARCHAR':
            case 'BINARY':
            case 'VARBINARY':
                $size = $field->max_length;
                return $this->generator->text($size);
            case 'LONGVARCHAR':
            case 'LONGVARBINARY':
            case 'CLOB':
            case 'CLOB_EMU':
            case 'BLOB':
            case 'TEXT':
                return $this->generator->text();
            case 'ENUM':
                $valueSet = $column->getValueSet();
                return $this->generator->randomElement($valueSet);
            case 'OBJECT':
            case 'PHP_ARRAY':
                // no smart way to guess what the user expects here
                return null;
        }
    }
    
    /**
	 * Guess the field name based on the field constraints
	 * 
	 * @param object $field field data constraints
	 *
	 * @return string
	 **/
	public function fieldName($field)
	{
        $name = strtolower($field->name);
        if (preg_match('/^is[_A-Z]/', $name)) {
            return 'boolean';
        }
        if (preg_match('/(_a|A)t$/', $name)) {
            return 'dateTime';
        }
        switch ($name) {
            case 'first_name':
            case 'firstname':
                return 'firstName';
            case 'last_name':
            case 'lastname':
                return 'lastName';
            case 'username':
            case 'login':
                return 'userName';
            case 'password':
                // bcrypt value of password
                return '$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36';
            case 'email':
            case 'mail':
                return 'email';
            case 'phone_number':
            case 'phonenumber':
            case 'phone':
                return 'phoneNumber';
            case 'address':
                return 'address';
            case 'ip_address':
                return 'ipv4';
            case 'city':
                return 'city';
            case 'streetaddress':
                return 'streetAddress';
            case 'postcode':
            case 'zipcode':
                return 'postcode';
            case 'state':
                return 'state';
            case 'country':
                return 'country';
            case 'title':
                return 'sentence';
            case 'body':
            case 'summary':
                return 'text';
        }
    }
}

/* End of file Guesser.php */
/* Location: ./application/models/Guesser.php */