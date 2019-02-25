<?php
	/**
	 * Created by PhpStorm.
	 * User: Lavryniuk Andrii
	 * Date: 25.02.2019
	 * Time: 12:59
	 */

	class DbConnectMysql
	{
		protected $connection;
		protected $host;
		protected $user;
		protected $pass;
		protected $db_name;

		/**
		 * DbConnectMysql constructor.
		 * @param $host
		 * @param $user
		 * @param $pass
		 * @param $db_name
		 * @throws Exception
		 */
		public function __construct($host, $user, $pass, $db_name)
		{
			$this->host = $host;
			$this->user = $user;
			$this->pass = $pass;
			$this->db_name = $db_name;

			$this->connection = new mysqli($host, $user, $pass, $db_name);
			$this->query('SET NAMES UTF8');
			if (mysqli_connect_error()) {
				throw new Exception('Cold not connect to DB');
			}

		}

		/**
		 * @param $sql
		 * @return bool|mysqli_result
		 * @throws Exception
		 */
		public function query($sql)
		{
			if (!$this->connection) {
				return false;
			}
			$result = $this->connection->query($sql);

			if (mysqli_error($this->connection)) {
				throw new Exception(mysqli_error($this->connection));

			}
			if (is_bool($result)) {
				return $result;
			}
		}


		public function escape($str)
		{
			return mysqli_escape_string($this->connection, $str);
		}

		public function insertId()
		{
			return mysqli_insert_id($this->connection);
		}

	}