<?php
/**
 * @package    mysql-database
 */

/**
 *  MySQL Database
 *
 *  A singleton object which provides convenience methods for interfacing with
 *  a MySQL database in PHP 5. You can get the object's instance using the
 *  static {@link getInstance()} method. Being a singleton object, this class
 *  only supports one open database connection at a time and idealy suited to
 *  single-threaded applications. You can read
 *  about {@link http://php.net/manual/en/language.oop5.patterns.php the singleton 
 *  pattern in the PHP manual}.
 *
 *  <b>Getting Started</b>
 *  <code>
 *  $db = MySqlDatabase::getInstance();
 *
 *  try {
 *      $db->connect('localhost', 'user', 'password', 'database_name');
 *  }
 *  catch (Exception $e) {
 *      die($e->getMessage());
 *  }
 *  </code>
 *
 * @package    mysql-database
 * @author     Micah Carrick
 * @copyright  (c) 2010 - Micah Carrick
 * @version    2.0
 * @license    BSD
 */
defined('_MCSHOP') or die("Security block!");

class MySqlDatabase
{
	/**
	 * @var string
	 */
	private $conn_str;

	/**
	 * @var MySqlDatabase
	 */
	private static $instance;

	const MYSQL_DATE_FORMAT = 'Y-m-d';
	const MYSQL_TIME_FORMAT = 'H:i:s';
	const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

	const INSERT_GET_AUTO_INCREMENT_ID = 1;
	const INSERT_GET_AFFECTED_ROWS = 2;

	/**
	 * @var PDO
	 */
	private $database;

	/**
	 * @var PDOStatement
	 */
	private $lastStatement;

	/**
	 *  Constructor
	 *
	 *  Private constructor as part of the singleton pattern implementation.
	 */
	private function __construct()
	{
		$this->connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD, SQL_DB, false);
	}

	/**
	 *  Connect
	 *
	 *  Establish a connection to a MySQL database. Returns the MySQL link
	 *  link identifier or throws an exception if there is an error.
	 *
	 *  <code>
	 *  // get an instance of the Database singleton
	 *  $db = MySqlDatabase::getInstance();
	 *
	 *  // connect to a MySQL database (use your own login information)
	 *  try {
	 *      $db->connect('localhost', 'user', 'password', 'database_name');
	 *  }
	 *  catch (Exception $e) {
	 *      die($e->getMessage());
	 *  }
	 *  </code>
	 *
	 * @param $host
	 * @param $user
	 * @param $password
	 * @param bool|string $database
	 * @param bool $persistent
	 * @throws Exception
	 * @internal param $string
	 * @internal param $string
	 * @internal param $string
	 * @internal param $string
	 * @return resource
	 */
	public function connect($host, $user, $password, $database = "", $persistent = false)
	{
		$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", $host, $database);

		$this->database = new PDO($dsn, $user, $password, array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_PERSISTENT => $persistent
		));

		$version = $this->database->getAttribute(PDO::ATTR_SERVER_VERSION);
		$this->conn_str = "'$database' on '$user@$host' (MySQL $version)";
	}

	/**
	 *  Delete
	 *
	 *  Executes the DELETE statement specified in the query and returns the
	 *  value from either the PHP {@link mysqli_affected_rows()} function. Throws
	 *  and exception if there is a MySQL error in the query.
	 *
	 *  Note: With MySQL versions prior to 4.1.2, the affected rows on DELETE
	 *  statements with no WHERE clause is 0. See {@link mysqli_affected_rows()}
	 *  for more information.
	 *
	 * @param string $query
	 * @param array $parameters
	 * @internal param $string
	 * @return integer
	 */
	public function delete($query, $parameters = array())
	{
		return $this->updateOrDelete($query, $parameters);
	}

	/**
	 *  Get Connection String
	 *
	 *  Gets a string representing the connection.
	 *
	 *  <code>
	 *  echo $db->getConnectionString();
	 *  // example output: 'test_database' on 'web_user@localhost' (MySQL 5.1.47)
	 *  </code>
	 *
	 * @return string
	 */
	public function getConnectionString()
	{
		return $this->conn_str;
	}

	/**
	 *  Get Instance
	 *
	 *  Gets the singleton instance for this object. This method should be called
	 *  statically in order to use the Database object:
	 *
	 *  <code>
	 *  $db = MySqlDatabase::getInstance();
	 *  </code>
	 *
	 * @return MySqlDatabase
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new MySqlDatabase();
		}

		return self::$instance;
	}

	/**
	 *  Fetch One From Each Row
	 *
	 *  Convenience method to get a single value from every row in a given
	 *  query. This is usefull in situations where you know that the result will
	 *  only have only one column of data and you need that all in a simple
	 *  array.
	 *
	 *  <code>
	 *
	 *  $query = "SELECT name FROM users";
	 *  $names = $db->fetchOneFromEachRow($query);
	 *  echo 'Users: ' . implode(', ', $names);
	 *  </code>
	 *
	 * @param string $query
	 * @param array $parameters
	 * @return array
	 */
	public function fetchOneFromEachRow($query, $parameters = array())
	{
		$retVal = array();

		$result = $this->query($query, $parameters);

		$results = $result->fetch_array();
		foreach ($results as $row) {
			$retVal[] = $row[0];
		}

		return $retVal;
	}

	/**
	 *  Fetch One Row
	 *
	 *  Convenience method to get a single row from a given query. This is
	 *  usefull in situations where you know that the result will only contain
	 *  one record and therefore do not need to iterate over it.
	 *
	 *  You can
	 *  optionally  specify the type of data to be returned (object or array)
	 *  using one of the MySqlResultSet Data Constants. The default is
	 *  {@link MySqlResultSet::DATA_OBJECT}.
	 *
	 *  <code>
	 *  // get one row of data
	 *  $query = "SELECT first, last FROM users WHERE user_id = 24 LIMIT 1";
	 *  $row = $db->fetchOneRow($query);
	 *  echo $row->foo;
	 *  echo $row->bar;
	 *  </code>
	 *
	 * @param string $query
	 * @param array $parameters
	 * @param int $data_type
	 * @return mixed
	 */
	public function fetchOneRow($query, $parameters = array(), $data_type = MySqlResultSet::DATA_OBJECT)
	{
		$result = new MySqlResultSet($this->database, $query, $parameters, $data_type);

		$retval = $result->getResultResource();

		return $retval[0];
	}

	/**
	 *  Fetch One
	 *
	 *  Convenience method to get a single value from a single row. Returns the
	 *  value if the query returned a record, false if there were no results, or
	 *  throws an exception if there was an error with the query.
	 *
	 *  <code>
	 *  // get the number of records in the 'users' table
	 *  $count = $db->fetchOne("SELECT COUNT(*) FROM users");
	 *  </code>
	 *
	 * @param string $query
	 * @param array $parameters
	 * @return mixed
	 */
	public function fetchOne($query, array $parameters = array())
	{
		$result = new MySqlResultSet($this->database, $query, $parameters, MySqlResultSet::DATA_FETCH_ONE);
		$row = $result->getResultResource();

		if (!$row) {
			return false;
		}

		return $row;
	}

	/**
	 *  Import SQL File
	 *
	 *  Runs the queries defined in an SQL script file. The double-hyphen style
	 *  comments must have a single space after the hyphens. Hash style comments
	 *  and C-style comments are also supported.
	 *
	 *  An optional user callback function can be specified to get information
	 *  about each MySQL statement. The user callback function takes 3
	 *  parameters: the line number as an integer, the query as a string, and the
	 *  result of the query as a boolean.
	 *
	 *  <code>
	 *  function import_sql_callback($line_number, $sql_query, $result)
	 *  {
	 *      echo "Line $line_number: $sql_query ";
	 *      if ($result) echo "(OK)<br/>";
	 *      else echo "(FAIL)<br/>";
	 *  }
	 *  </code>
	 *
	 *  You can optionally specify whether or not to abort importing statements
	 *  when an SQL error occurs (defaults to 'true') in which case an exception
	 *  will be thrown for any MySQL error.
	 *
	 *  Returns the number of queries executed from the script or throws an
	 *  exception if there is an error.
	 *
	 *  <code>
	 *  // no callback, throw exception on MySQL errors
	 *  $number = $db->importSqlFile('queries.sql');
	 *
	 *  // callback for each query, skip queries with MySQL errors
	 *  $number = $db->importSqlFile('queries.sql', 'import_sql_callback', false);
	 *  </code>
	 *
	 *  TODO: Ensure this works with huge files. Might need to use fopen()
	 *
	 * @param  string
	 * @param callable $callback
	 * @param bool $abort_on_error
	 * @throws Exception
	 * @internal param $boolean
	 * @return integer
	 */
	public function importSqlFile($filename, $callback = null, $abort_on_error = true)
	{
		if ($callback && !is_callable($callback)) {
			throw new Exception("Invalid callback function.");
		}

		$lines = $this->loadFile($filename);

		$num_queries = 0;
		$sql_line = 0;
		$sql = '';
		$in_comment = false;

		foreach ($lines as $num => $line) {

			$line = trim($line);
			$num++;
			if (empty($sql)) $sql_line = $num;

			// ignore comments

			if ($in_comment) {
				$comment = strpos($line, '*/');

				if ($comment !== false) {
					$in_comment = false;
					$line = substr($line, $comment + 2);
				} else {
					continue;
				}

			} else {

				$comment = strpos($line, '/*');

				if ($comment !== false) {

					if (strpos($line, '*/') === false) {
						$in_comment = true;
					}

					$line = substr($line, 0, $comment);

				} else {

					// single line comments

					foreach (array('-- ', '#') as $chars) {
						$comment = strpos($line, $chars);

						if ($comment !== false) {
							$line = substr($line, 0, $comment);
						}
					}
				}
			}

			// check if the statement is ready to be queried

			$end = strpos($line, ';');

			if ($end === false) {
				$sql .= $line;
			} else {
				$sql .= substr($line, 0, $end);
				$result = $this->quickQuery($sql);
				$num_queries++;

				if (!$result && $abort_on_error) {
					$file = basename($filename);
					$error = $this->database->errorCode();
					throw new Exception("Error in $file on line $sql_line: $error");
				}

				if ($callback) {
					call_user_func($callback, $sql_line, $sql, $result);
				}

				$sql = ''; // clear for next statement

			}
		}

		return $num_queries;
	}

	/**
	 *  Is Connected
	 *
	 *  Determines if there is a connection open to the database.
	 *
	 * @return boolean
	 */
	public function isConnected()
	{
		if (!$this->database) {
			return false;
		}

		return $this->database->getAttribute(PDO::ATTR_CONNECTION_STATUS) !== null;
	}

	/**
	 *  Get unique id
	 *
	 * Gets an unique identifier from the database
	 *
	 * @returns int/string
	 */
	public function getUUID()
	{
		return $this->fetchOne("select uuid_short()");
	}

	/**
	 *  Insert
	 *
	 *  Executes the INSERT statement specified in the query and returns the
	 *  value from either the PHP {@link mysqli_insert_id()} function or the
	 *  php {@link mysqli_affected_rows()} function depending on the value of the
	 *  $return_type parameter.
	 *
	 *  <code>
	 *  $db = MySqlDatabase::getInstance();
	 *  $query = "INSERT INTO foobar (col1, col2) VALUES (1, 2), (2, 3)";
	 *  $rows = $db->insert($query, MySqlDatabase::INSERT_GET_AFFECTED_ROWS);
	 *  echo $rows; // output: 2
	 *  </code>
	 *
	 *
	 * @param string $query
	 * @param array $parameters
	 * @param int $r_type
	 * @internal param $string
	 * @internal param $integer
	 * @return integer
	 */
	public function insert($query, $parameters = array(), $r_type = MySqlDatabase::INSERT_GET_AUTO_INCREMENT_ID)
	{
		$this->query($query, $parameters);

		if ($r_type == MySqlDatabase::INSERT_GET_AFFECTED_ROWS) {
			return $this->lastStatement->rowCount();
		} else {
			return $this->database->lastInsertId();
		}
	}

	/**
	 *  Iterate Result Set
	 *
	 *  Returns a {@link MySQL_ResultSet} iteratable object for a query. The $type
	 *  parameter indicates the data being iterated should be an object,
	 *  a numerically indexed array, an associative array, or an array with
	 *  both numeric and associative indexes. Defaults to objects.
	 *
	 *  <code>
	 *  $sql_query = "SELECT col1, col2 FROM table";
	 *
	 *  // iterate as objects
	 *  foreach ($db->iterate("SELECT col1, col2 FROM table") as $row) {
	 *      echo $row->col1 . '<br/>';
	 *      echo $row->col2 . '<br/>';
	 *  }
	 *
	 *  // iterate as both associative and numerically indexed array
	 *  foreach ($db->iterate($sql_query, MySQL_Db::DATA_ARRAY) as $row) {
	 *      echo $row[0] . '<br/>';
	 *      echo $row['col1'] . '<br/>';
	 *  }
	 *  </code>
	 *
	 * @param string $sql
	 * @param array $parameters
	 * @param int $data_type
	 * @return boolean
	 * @deprecated
	 */
	public function iterate($sql, $parameters = array(), $data_type = MySqlResultSet::DATA_OBJECT)
	{
		return new MySqlResultSet($this->database, $sql, $parameters, $data_type);
	}

	/**
	 *  Load File
	 *
	 *  Loads the specified filename into an array of lines. Throws an exception
	 *  if there is an error.
	 *
	 * @param  string
	 * @throws Exception
	 * @return array
	 */
	private function loadFile($filename)
	{
		if (!file_exists($filename)) {
			throw new Exception("File does not exist: $filename");
		}

		$file = file($filename, FILE_IGNORE_NEW_LINES);

		if (!$file) {
			throw new Exception("Could not open $filename");
		}

		return $file;
	}

	/**
	 * @param $query
	 * @param array $parameters
	 * @return array
	 * @throws Exception
	 */
	public function query($query, array $parameters = array())
	{
		//vd($query, $parameters);
		$this->connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD, SQL_DB);

		$this->lastStatement = $this->database->prepare($query);

		if (count($parameters) > 0) {
			foreach ($parameters as $parameter => $value) {
				if (isNumber($value)) {
					$this->lastStatement->bindValue($parameter, (int)$value, PDO::PARAM_INT);
				} else {
					$this->lastStatement->bindValue($parameter, $value);
				}
			}
		} else {
			return $this->simpleQuery($query);
		}

		$success = false;
		try {
			$success = $this->lastStatement->execute();
		} catch (PDOException $ignored) {

		}

		if (!$success) {
			throw new Exception("Query Error: " . $this->database->errorInfo());
		}

		try {
			return $this->lastStatement->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $ignored) {

		}

		return array();
	}

	/**
	 *  Quick Query
	 *
	 *  Executes a MySQL query and returns a boolean value indicating success
	 *  or failure. This method will close any resources opened from
	 *  SELECT, SHOW, DESCRIBE, or EXPLAIN statements and would not be very
	 *  usefull for those types of queries. This method is used internally for
	 *  importing SQL scripts.
	 *
	 * @param string $query
	 * @param array $parameters
	 * @return array
	 */
	public function quickQuery($query, $parameters = array())
	{
		return $this->query($query, $parameters);
	}

	/**
	 *  Update
	 *
	 *  Executes the UPDATE statement specified in the query and returns the
	 *  value from either the PHP {@link mysqli_affected_rows()} function. Throws
	 *  and exception if there is a MySQL error in the query.
	 *
	 *  Note: The number of rows affected include only those in which the new
	 *  value was not the same as the old value. See {@link mysqli_affected_rows()}
	 *  for more information.
	 *
	 * @param string $query
	 * @param array $parameters
	 * @return integer
	 */
	public function update($query, $parameters = array())
	{
		return $this->updateOrDelete($query, $parameters);
	}

	/**
	 * @param $query
	 * @param array $parameters
	 * @return int
	 */
	private function updateOrDelete($query, $parameters = array())
	{
		$this->query($query, $parameters);

		return $this->lastStatement->rowCount();
	}

	private function simpleQuery($query)
	{
		try {
			$this->lastStatement = $this->database->query($query);
		} catch (PDOException $ignored) {

		}

		if (!($this->lastStatement instanceof PDOStatement)) {
			throw new Exception("Invalid query: " . $query . "; Error: " . $this->database->errorCode() . " " . $this->database->errorInfo());
		}

		return $this->lastStatement->fetchAll(PDO::FETCH_OBJ);
	}
}