<?php
/**
 * MySQL Result Set
 *
 * Class definition for the {@link MySqlResultSet} object.
 *
 * @packagemysql-database
 */

/**
 * MySQL Result Set
 *
 * An iteratable object representing the result set from a MySQL SELECT query.
 * The Iterator interface allows the object to be iterated over by PHP in a
 * foreach loop as objects or arrays representing each row of data.
 *
 * For most applications you will not need to call any of this object's methods
 * directly. Instead, it is typically obtained from {@link MySqlDatabase::iterate()}
 * and iterated over using a foreach loop:
 *
 * <code>
 * $db = MySqlDatabase::getInstance();
 * $db->connect('localhost', 'user', 'password', 'database_name');
 *
 * // $db->iterate() returns a new MySqlResultSet instance
 *   foreach ($db->iterate("SELECT * FROM users LIMIT 100") as $row) {
 *   print_r($row);
 * }
 * </code>
 *
 * @packagemysql-database
 * @author Micah Carrick
 * @copyright(c) 2010 - Micah Carrick
 * @version2.0
 * @licenseBSD
 */
defined('_MCSHOP') or die("Security block!");

class MySqlResultSet
{
	private $query;

	/**
	 * @var PDOStatement
	 */
	private $result;
	private $num_rows = 0;
	private $row = false;
	private $type;

	/**
	 * @var PDO
	 */
	private $database;

	/**
	 *Object Data
	 *
	 *The data will be fetched as an object, where the columns of the table
	 *are property naems of the object. See
	 *{@link mysqli_fetch_object()}.
	 */
	const DATA_OBJECT = 1;

	/**
	 *Numeric Array Data
	 *
	 *The data will be fetched as a numerically indexed array. See
	 *{@link mysqli_fetch_row()}.
	 */
	const DATA_NUMERIC_ARRAY = 2;

	/**
	 *Keyed Array Data
	 *
	 *The data will be fetched as an associative array. See
	 *{@link mysqli_fetch_assoc()}.
	 */
	const DATA_ASSOCIATIVE_ARRAY = 3;

	/**
	 *Array Data
	 *
	 *The data will be fetched as both an associative and indexed array. See
	 *{@link mysqli_fetch_array()}.
	 */
	const DATA_ARRAY = 4;
	const DATA_FETCH_ONE = 5;
	/**
	 *Constructor
	 *
	 *The constructor requires an SQL query which should be a query that
	 *returns a MySQL result resource such as a SELECT query. If the query
	 *fails or does not return a result resource, the constructor will throw
	 *an exception.
	 *
	 *The optional $data_type parameter specifies how to fetch the data. One
	 *of the data constants can be specified or the default
	 *{@link MySqlResultSet::DATA_OBJECT} will be used.
	 *
	 * @param \PDO $database
	 * @param $query
	 * @param array $parameters
	 * @param int $data_type
	 * @throws Exception
	 * @internal param array $paramters
	 * @internal param \MySQLi $link
	 * @internal param $string
	 */
	public function __construct(PDO $database, $query, array $parameters = array(), $data_type = MySqlResultSet::DATA_OBJECT)
	{
		$this->database = $database;

		$this->result = $this->query($query, $parameters, $data_type);

		if ($this->database->errorCode() !== "00000") {
			throw new Exception("" . $this->database->errorInfo());
		}

		$this->query = $query;
		$this->num_rows = count($this->result);
		$this->type = $data_type;
	}

	private function query($query, array $parameters, $dataType)
	{
		//vd($query, $parameters);

		$query = $this->database->prepare($query);

		foreach ($parameters as $parameter => $value) {
			$query->bindValue($parameter, $value);
		}

		$query->execute();

		switch($dataType) {
			case self::DATA_ARRAY:
			case self::DATA_NUMERIC_ARRAY:
			case self::DATA_ASSOCIATIVE_ARRAY:
				return $query->fetchAll(PDO::FETCH_ASSOC);
				break;
			break;
			case self::DATA_OBJECT:
				return $query->fetchAll(PDO::FETCH_OBJ);
				break;
			case self::DATA_FETCH_ONE:
				return $query->fetch(PDO::FETCH_COLUMN);
				break;
			default:
				return $query->fetchAll(PDO::FETCH_OBJ);
		}
	}

	/**
	 *Destructor
	 *
	 *The destructor will free the MySQL result resource if it is valid.
	 */
	public function __destruct()
	{
		unset($this->database);
	}

	/**
	 * @return array
	 */
	public function getResultResource()
	{
		return $this->result;
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		if ($this->num_rows == 0) {
			return true;
		}

		return false;
	}

	/**
	 *Valid
	 *
	 *Determines if the current row is valid.
	 *
	 * @return boolean
	 */
	function valid()
	{
		if ($this->row === false) return false;
		else return true;
	}
}