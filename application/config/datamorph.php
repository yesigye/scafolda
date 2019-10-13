<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* Name: datamorph Config
*
* Author: 	Yesigye Ignatius
* 			ignatiusyesigye@gmail.com

/*
|--------------------------------------------------------------------------
| Exclude databases
|--------------------------------------------------------------------------
| List databases that you want hidden.
*/
$config['exclude'] = array(
	'sys',
	'mysql',
	'information_schema',
	'performance_schema'
);

/*
|--------------------------------------------------------------------------
| Field types
|--------------------------------------------------------------------------
| List of database field types: To be used as a dropdown select.
*/
$config['field_types'] = array(
	'Basics' => array(
		'INT'       => 'INT',
		'VARCHAR'   => 'VARCHAR',
		'TEXT'      => 'TEXT',
		'DATE'      => 'DATE'
		),

	'Numeric' => array(
		'TINYINT'   => 'TINYINT',
		'SMALLINT'  => 'SMALLINT',
		'MEDIUMINT' => 'MEDIUMINT',
		'INT'       => 'INT',
		'BIGINT'    => 'BIGINT',
		'DECIMAL'   => 'DECIMAL',
		'FLOAT'     => 'FLOAT',
		'DOUBLE'    => 'DOUBLE',
		'REAL'      => 'REAL',
		'BIT'       => 'BIT',
		'BOOLEAN'   => 'BOOLEAN',
		'SERIAL'    => 'SERIAL'
		),

	'Date and time' => array(
		'DATE'      => 'DATE',
		'DATETIME'  => 'DATETIME',
		'TIMESTAMP' => 'TIMESTAMP',
		'TIME'      => 'TIME',
		'YEAR'      => 'YEAR'
		),

	'String' => array(
		'CHAR'      => 'CHAR',
		'VARCHAR'   => 'VARCHAR',
		'TINYTEXT'  => 'TINYTEXT',
		'TEXT'      => 'TEXT',
		'MEDIUMTEXT'=> 'MEDIUMTEXT',
		'LONGTEXT'  => 'LONGTEXT',
		'BINARY'    => 'BINARY',
		'VARBINARY' => 'VARBINARY',
		'TINYBLOB'  => 'TINYBLOB',
		'MEDIUMBLOB'=> 'MEDIUMBLOB',
		'BLOB'      => 'BLOB',
		'LONGBLOB'  => 'LONGBLOB',
		'ENUM'      => 'ENUM',
		'SET'       => 'SET'
		),

	'Spatial' => array(
		'GEOMETRY'          => 'GEOMETRY',
		'POINT'             => 'POINT',
		'LINESTRING'        => 'LINESTRING',
		'POLYGON'           => 'POLYGON',
		'MULTIPOINT'        => 'MULTIPOINT',
		'MULTILINESTRING'   => 'MULTILINESTRING',
		'MULTIPOLYGON'      => 'MULTIPOLYGON',
		'GEOMETRYCOLLECTION'=> 'GEOMETRYCOLLECTION'
		)
);

/*
|--------------------------------------------------------------------------
| Data types
|--------------------------------------------------------------------------
| List of data types that can be generated: To be used as a dropdown select.
|
| As defined by Faker PHP library
*/
$config['data_types'] = array(
	'Basics'    => array(
		'randomDigit'   => 'Int',
		'boolean'       => 'Boolean'
		),

	'Text'      => array(
		"word"          => "Word",
		"sentence"      => "Sentence",
		"paragraph"     => "Paragraph",
		"text"          => "Text"
		),

	"Person"    => array(
		"title"         => "Title",
		"name"          => "Name",
		"firstName"     => "First Name",
		"lastName"      => "Last Name",
		"phoneNumber"   => "Phone number"
		),

	"Address"   => array(
		"country"       => "Country",
		"latitude"      => "Latitude",
		"longitude"     => "Longitude",
		"postcode"      => "Postcode",
		"address"       => "Address",
		"state"         => "State",
		"streetAddress" => "Street Address",
		"stateAbbr"     => "State Abbreviation",
		"city"          => "City",
		"citySuffix"    => "City Suffix",
		"streetName"    => "Street Name",
		"secondaryAddress"  => "Secondary Address",
		"cityPrefix"    => "City Prefix",
		"streetSuffix"  => "Street Suffix",
		"buildingNumber"    => "Building Number"
		),

	"DateTime"  => array(
		"date"      => "Date",
		"time"      => "Time",
		"year"      => "Year",
		"century"   => "Century",
		"timezone"  => "Timezone"
		),

	"Internet"  => array(
		"freeEmail"     => "email",
		"companyEmail"  => "Company email",
		"userName"      => "Username",
		"password"      => "Password",
		"domainName"    => "Domain Name",
		"domainWord"    => "Domain Word",
		"tld"           => "tld",
		"url"           => "url",
		"slug"          => "slug",
		"ipv4"          => "IPv4",
		"localIpv4"     => "local IPv4",
		"ipv6"          => "ipv6",
		"macAddress"    => "mac Address"
		),

	"UserAgent" => array(
		"userAgent" => "userAgent",
		"chrome"    => "chrome",
		"firefox"   => "firefox",
		"safari"    => "safari",
		"opera"     => "opera",
		"internetExplorer" => "internetExplorer"
		),

	"Payment"   => array(
		"creditCardType"    => "Credit Card Type",
		"creditCardNumber"  => "Credit Card Number",
		"creditCardExpirationDateString" => "credit Card Exp Date",
		"creditCardDetails" => "Credit Card Details",
		"swiftBicNumber"    => "SWIFTBIC  Number"
		),

	"Color"     => array(
		"hexcolor"      => "Hex Color",
		"rgbcolor"      => "rgb Color",
		"rgbCssColor"   => "CSS rgb Color",
		"safeColorName" => "Color Name"
		),

	"Image"     => array(
		"imageUrl"  =>"Image Url",
		"image"     => "Image"
		),

	"Barcode"   => array(
		"ean13"     => "ean13",
		"ean8"      => "ean8",
		"isbn13"    => "isbn13",
		"isbn10"    => "isbn10"
		),

	"Miscellaneous" => array(
		"md5"    => "md5",
		"sha1"   => "sha1",
		"sha256" => "sha256",
		"locale" => "locale",
		"countryCode"   => "Country Code",
		"languageCode"  => "language Code",
		"currencyCode"  => "Currency Code"
		),
);

/*
|--------------------------------------------------------------------------
| Pagination Limit
|--------------------------------------------------------------------------
| Number of entries to show per page before applying pagiation
*/
$config['page_limit'] =20;

/*
|--------------------------------------------------------------------------
| Splice Tables
|--------------------------------------------------------------------------
| List of table names that should not appear in view to the user.
|
| matches - keywords that match(case-insensitive) the table name
|
| similar - keywords that the table name may contain
*/
$config['splice_tables'] = array(

	'matches' => array(
		'migrate', 'session', 'login_attempts'
	),

	'similar' => array(
		'migrations'
	),
);

/*
|--------------------------------------------------------------------------
| Splice Fields
|--------------------------------------------------------------------------
| List of field names that should not appear in view to the user.
|
| matches - keywords that match(case-insensitive) the field nmae
|
| similar - keywords that the field nmae may contain
*/
$config['splice_fields'] = array(

	'matches' => array(
		'password', 'salt', 'forgotten_password', 'ip_address', 'created_on',
		'remember_code', 'date_created', 'id'
	),

	'similar' => array(
		'forgotten_password', 'activation_code', 'last_login', '_id'
	),
);