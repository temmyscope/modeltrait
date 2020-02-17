<?php
namespace Seven\Model;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Should be used in a model class that defines all of the initialised variables 
 *
 * @package ModelTrait
 * @author Elisha Temiloluwa a.k.a TemmyScope (temmyscope@protonmail.com)
 **/

trait ModelTrait
{
	/**
	* @var static $table that is defined and declared in the child class of this model
	* @example protected static $table = 'user'; 
	*
	* @var static $config that is defined and declared 
	* @example
	*	$config = [
	*	    'dbname' => 'mydb',
	*	    'user' => 'user',
	*	    'password' => 'secret',
	*	    'host' => 'localhost',
	*	    'driver' => 'pdo_mysql',
	*	];
	*
	* @var $fulltext refers to fulltext colums that can be searched using complicated match...against queries.
	*
	*
	*
	*
	*/
	private $config;
	protected static $table;
	protected static $fulltext;

	/**
	* __callStatic will be called from static content, that is, when calling a nonexistent
	* static method:
	* 	Foo::method($arg, $arg1);
	*
	* First argument will contain the method name (in example above it will be "method"),
	* and the second will contain the values of $arg and $arg1 as an array.
	*/
	public static function __callStatic($method, $args)
	{
		$conn = DriverManager::getConnection(self::$config);
		$fluent = $conn->createQueryBuilder();
		$table = static::$table;
		$method = strtolower($method);
		switch ($method) {
			/**
			 * 
			 *
			 * @return void
			**/
			case "all":
				return $conn->fetchAll("SELECT * FROM {$table}");

			/**
			 * @param array $columns to be updated
			 * @param array $where clause as conditional statement
			 * @return void
			**/
			case "update":
				return $conn->update(static::$table, $args[0], $args[1]);

			/**
			* @param $data
			* @example
			* $data = [
			*	'column' => 'data'
			* 	'name' => 'John Well',
			*	'username' => 'john086'
			* ];
			* @return string last Insert Id
			*/
			case "insert":
				$conn->insert($table, $args[0]);
				return $conn->lastInsertId();
			/**
			 * @param Array $where clause
			 *
			 * @return array of arrays containing result set
			**/
			case "findby":
				$sql = "SELECT * FROM {$table} WHERE";
				$values = [];
				foreach ($args[0] as $key => $value) {
					$sql .= " $key = ? AND";
					$values[] = $value;
				}
				$sql = rtrim($sql, ' AND');
				return $conn->fetchAll("$sql", $values);
			/**
			* @param Array $where clause
			*
			* @return bool
			**/
			case 'exists':
				$sql = "SELECT * FROM {$table} WHERE";
				$values = [];
				foreach ($args[0] as $key => $value) {
					$sql .= " $key = ? AND";
					$values[] = $value;
				}
				$sql = rtrim($sql, ' AND')." LIMIT 1";
				return (empty($conn->fetchAll("$sql", $values))) ? false : true;
			/**
			 * @param string $search is the query to be searched for
			 * @param string[] $searchable columns
			 *
			 * @return void
			**/
			case "search":
				$query = "%".$args[0]."%";
				array_shift($args);
		  		$where = '';																			
		  		$values = [];																			
		  		if(!empty(static::$fulltext)){																	
		  			foreach ($fulltext as $key){														
		  				$where .= "MATCH ({$key}) AGAINST (?) OR ";
		  				$values[] =  $query;
		  			}
		  		}
		  		if(!empty($args)){
		  			foreach($args as $column){															
			  			$where .= "$column LIKE ? OR ";
			  			$values[] =  $query;													
			  		}
		  		}
		  		$where = rtrim($where, ' OR ');
		  		return $conn->fetchall("SELECT * FROM {$table} WHERE {$where}", $values);

			/**
			 * @param string $column to be incremented
			 * @param value to use in incrementing
			 * @return number of rows affected
			**/
			case "add":
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} + {$args[1]}")->execute();

			/**
			 * @param string $column to be incremented
			 * @param value to use in decrementing
			 * @return number of rows affected
			**/
			case "minus":
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} - {$args[1]}")->execute();
			/**
			 * @param string $column to be incremented
			 * @return number of rows affected
			**/
			case "addone":
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$column} + 1")->execute();

			/**
			 * @param string $column to be decremented
			 * @return number of rows affected
			**/
			case "minusone":
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$column} - 1")->execute();
			/**
			 * @param string column to count, default is *
			 *
			 * @return void
			**/
			case "count":
				$column = $args[0] ?? 'id';
				return $conn->fetchall("SELECT COUNT({$column}) FROM {$table} WHERE {$where}", $values);
			/**
			 * @param array $where clause
			 *
			 * @return 
			**/
			case 'delete':
				return $conn->delete(static::$table, $args[0]);

			case 'softdelete':
				return $conn->update(static::$table, [ 'deleted' => 'true' ], $args[0]);
			/**
			 * @return Doctrine\DBAL $queryBuilder instance
			**/
			case 'fluent':
				return $queryBuilder;
			/**
			 * If things go south and the called method does not exist
			 *
			 * @return void
			**/
			default:
				throw new BadMethodCallException("Undefined method $method");
		}

	}
}