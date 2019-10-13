<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Test_app extends CI_Migration {

	public function up()
	{
		// Table structure for table 'groups'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE,
				'unique' => TRUE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
			),
			'description' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('groups', TRUE);
		// Dumping data for table 'groups'
		if ($this->db->count_all('groups') == 0) {
			$this->db->insert_batch('groups', array(
				array(
					'id' => '1',
					'name' => 'admin',
					'description' => 'Administrator'
				),
				array(
					'id' => '2',
					'name' => 'members',
					'description' => 'General User'
				)
			));
		}
		
		// Table structure for table 'users'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'ip_address' => array(
				'type' => 'VARCHAR',
				'constraint' => '16'
			),
			'username' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => FALSE,
			),
			'password' => array(
				'type' => 'VARCHAR',
				'constraint' => '80',
				'null' => FALSE,
			),
			'salt' => array(
				'type' => 'VARCHAR',
				'constraint' => '40'
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => FALSE,
			),
			'activation_code' => array(
				'type' => 'VARCHAR',
				'constraint' => '40',
				'null' => TRUE
			),
			'forgotten_password_code' => array(
				'type' => 'VARCHAR',
				'constraint' => '40',
				'null' => TRUE
			),
			'forgotten_password_time' => array(
				'type' => 'INT',
				'constraint' => '11',
				'unsigned' => TRUE,
				'null' => TRUE
			),
			'remember_code' => array(
				'type' => 'VARCHAR',
				'constraint' => '40',
				'null' => TRUE
			),
			'created_on' => array(
				'type' => 'INT',
				'constraint' => '11',
				'unsigned' => TRUE,
			),
			'last_login' => array(
				'type' => 'INT',
				'constraint' => '11',
				'unsigned' => TRUE,
				'null' => TRUE
			),
			'active' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'unsigned' => TRUE,
			),
			'avatar' => array(
				'type' => 'VARCHAR',
				'constraint' => '220',
				'null' => TRUE
			),
			'first_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
			),
			'last_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
			),
			'address' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => TRUE
			),
			'phone' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => TRUE
			),
			'postal' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => TRUE
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('users', TRUE);
		// Dumping data for table 'users'
		if ($this->db->count_all('users') == 0) {
			$this->db->insert_batch('users', array(
				array(
					'id' => '1',
					'ip_address' => '127.0.0.1',
					'username' => 'samduke',
					'password' => '$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36',
					'salt' => '',
					'email' => 'samduke@email.com',
					'activation_code' => '',
					'forgotten_password_code' => NULL,
					'created_on' => '1268889823',
					'last_login' => '1268889823',
					'active' => '1',
					'avatar' => 'https://fakeimg.pl/300/',
					'first_name' => 'Samuel',
					'last_name' => 'Duke',
					'address' => 'Home town',
					'phone' => '',
				),
				array(
					'id' => '2',
					'ip_address' => '127.0.0.1',
					'username' => 'miller@23',
					'password' => '$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36',
					'salt' => '',
					'email' => 'miller@23@email.com',
					'activation_code' => '',
					'forgotten_password_code' => NULL,
					'created_on' => '1268889823',
					'last_login' => '1268889823',
					'active' => '1',
					'avatar' => 'https://fakeimg.pl/300/',
					'first_name' => 'Jayson',
					'last_name' => 'Miller',
					'address' => 'south center town',
					'phone' => '',
				),
				array(
					'id' => '3',
					'ip_address' => '127.0.0.1',
					'username' => 'doejohn',
					'password' => '$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36',
					'salt' => '',
					'email' => 'doejohn@email.com',
					'activation_code' => '',
					'forgotten_password_code' => NULL,
					'created_on' => '1268889823',
					'last_login' => '1268889823',
					'active' => '1',
					'avatar' => 'https://fakeimg.pl/300/',
					'first_name' => 'John',
					'last_name' => 'Doe',
					'address' => 'Central town',
					'phone' => '12345678',
				),
				array(
					'id' => '4',
					'ip_address' => '127.0.0.1',
					'username' => 'jackieSue',
					'password' => '$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36',
					'salt' => '',
					'email' => 'jackieSue@email.com',
					'activation_code' => '',
					'forgotten_password_code' => NULL,
					'created_on' => '1268889823',
					'last_login' => '1268889823',
					'active' => '1',
					'avatar' => 'https://fakeimg.pl/300/',
					'first_name' => 'Jackie',
					'last_name' => 'Susan',
					'address' => 'Northern Camp',
					'phone' => '',
				),
				array(
					'id' => '5',
					'ip_address' => '127.0.0.1',
					'username' => 'milner64',
					'password' => '$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36',
					'salt' => '',
					'email' => 'milner64@email.com',
					'activation_code' => '',
					'forgotten_password_code' => NULL,
					'created_on' => '1268889823',
					'last_login' => '1268889823',
					'active' => '1',
					'avatar' => 'https://fakeimg.pl/300/',
					'first_name' => 'Jack',
					'last_name' => 'Milner',
					'address' => 'Central town',
					'phone' => '',
				),
			));
		}

		// Table structure for table 'users_groups'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE,
				'unique' => TRUE,
				'auto_increment' => TRUE
			),
			'user_id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE
			),
			'group_id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE
			)
		));
		$this->dbforge->add_field('CONSTRAINT `fk_users_groups_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
		$this->dbforge->add_field('CONSTRAINT `fk_users_groups_groups` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('users_groups', TRUE);
		// Dumping data for table 'users_groups'
		if ($this->db->count_all('users_groups') == 0) {
			$this->db->insert('users_groups', array(
				'id' => '1',
				'user_id' => '1',
				'group_id' => '1',
			));
		}

		// Table structure for table 'orders'
		$this->dbforge->add_field(array(
			'ord_number' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unique' => TRUE,
			),
			'user_id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE
			),
			'ord_total' => array(
				'type' => 'DOUBLE',
				'constraint' => '10,2',
				'default' => '0.00'
			),
			'ord_status' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => 'pending'
			),
			'ord_date' => array(
				'type' => 'DATETIME',
				'default' => '20180101123000'
			),
			'ord_demo_comments' => array(
				'type' => 'LONGTEXT'
			)
		));
		$this->dbforge->add_field('CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
		$this->dbforge->add_key('ord_number', TRUE);
		$this->dbforge->add_key('user_id');
		$this->dbforge->create_table('orders', TRUE);
		// Dumping data for table 'orders'
		if ($this->db->count_all('orders') == 0) {
			$this->db->insert_batch('orders', array(
				array(
					'ord_number' => '0000001',
					'user_id' => '2',
					'ord_total' => '20.00',
					'ord_status' => 'pending',
					'ord_date' => '20180113140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000002',
					'user_id' => '4',
					'ord_total' => '74.95',
					'ord_status' => 'pending',
					'ord_date' => '20180113140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000003',
					'user_id' => '3',
					'ord_total' => '16.77',
					'ord_status' => 'pending',
					'ord_date' => '20180213140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000004',
					'user_id' => '5',
					'ord_total' => '40.64',
					'ord_status' => 'pending',
					'ord_date' => '20180113140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000005',
					'user_id' => '1',
					'ord_total' => '139.44',
					'ord_status' => 'pending',
					'ord_date' => '20180213140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000006',
					'user_id' => '5',
					'ord_total' => '98.50',
					'ord_status' => 'pending',
					'ord_date' => '20180213140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000007',
					'user_id' => '2',
					'ord_total' => '36.62',
					'ord_status' => 'pending',
					'ord_date' => '20180213140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000008',
					'user_id' => '1',
					'ord_total' => '44.19',
					'ord_status' => 'pending',
					'ord_date' => '20180313140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000009',
					'user_id' => '2',
					'ord_total' => '8.50',
					'ord_status' => 'pending',
					'ord_date' => '20180413140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000010',
					'user_id' => '4',
					'ord_total' => '36.62',
					'ord_status' => 'pending',
					'ord_date' => '20180413140004',
					'ord_demo_comments' => '',
				),
				array(
					'ord_number' => '0000011',
					'user_id' => '4',
					'ord_total' => '10.19',
					'ord_status' => 'pending',
					'ord_date' => '20180413140004',
					'ord_demo_comments' => '',
				),
			));
		}

		// Table structure for table 'products'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => '11',
				'unique' => TRUE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => '100'
			),
			'description' => array(
				'type' => 'TEXT'
			),
			'price' => array(
				'type' => 'DOUBLE',
				'constraint' => '8,2',
				'default' => '0.00'
			),
			'thumbnail' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => ''
			),
			'quantity' => array(
				'type' => 'INT',
				'constraint' => '10',
				'default' => '0'
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('products', TRUE);
		// Dumping data for table 'products'
		if ($this->db->count_all('products') == 0) {
			$this->db->insert_batch('products', array(
				array(
					'id' => '1',
					'name' => 'Spotted black dress',
					'description' => 'Lorem ipsum doret imacuted idicet',
					'price' => '16.99',
					'thumbnail' => 'https://fakeimg.pl/300/',
					'quantity' => '5',
				),
				array(
					'id' => '2',
					'name' => 'Vintage cool dress',
					'description' => 'Lorem ipsum doret imacuted idicet',
					'price' => '15.99',
					'thumbnail' => 'https://fakeimg.pl/300/',
					'quantity' => '8',
				),
				array(
					'id' => '3',
					'name' => 'Bodycon party dress',
					'description' => 'Lorem ipsum doret imacuted idicet',
					'price' => '12.5',
					'thumbnail' => 'https://fakeimg.pl/300/',
					'quantity' => '5',
				),
				array(
					'id' => '4',
					'name' => 'Stripped evening dress',
					'description' => 'Lorem ipsum doret imacuted idicet',
					'price' => '10',
					'thumbnail' => 'https://fakeimg.pl/300/',
					'quantity' => '12',
				),
				array(
					'id' => '5',
					'name' => 'Plokadot designer dress',
					'description' => 'Lorem ipsum doret imacuted idicet',
					'price' => '18.99',
					'thumbnail' => 'https://fakeimg.pl/300/',
					'quantity' => '15',
				),
			));
		}

		// Table structure for table 'product_images'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => '11',
				'unique' => TRUE,
				'auto_increment' => TRUE
			),
			'product_id' => array(
				'type' => 'INT',
				'constraint' => '11'
			),
			'image' => array(
				'type' => 'VARCHAR',
				'constraint' => '255'
			),
			'sort_order' => array(
				'type' => 'SMALLINT',
				'constraint' => '5'
			)
		));
		$this->dbforge->add_field('CONSTRAINT `fk_product_images_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('product_images', TRUE);

		// Table structure for table 'product_categories'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'SMALLINT',
				'constraint' => '5',
				'unique' => TRUE,
				'auto_increment' => TRUE
			),
			'parent_id' => array(
				'type' => 'SMALLINT',
				'constraint' => '5',
				'null' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => '50'
			),
			'slug' => array(
				'type' => 'VARCHAR',
				'constraint' => '50'
			),
			'sort_order' => array(
				'type' => 'SMALLINT',
				'constraint' => '5'
			),
			'status' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => '1'
			)
		));
		$this->dbforge->add_field('CONSTRAINT `fk_categories` FOREIGN KEY (`parent_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('product_categories', TRUE);
	}

	public function down()
	{
		$tables = $this->db->list_tables();

		foreach ($tables as $table)
		{
			$this->dbforge->drop_table($table, TRUE);
		}
	}
}
