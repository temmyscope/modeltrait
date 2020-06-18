<?php
namespace Seven\Model;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use \Exception;

/**
 * Should be used in a model class that defines all of the initialised variables 
 *
 * @package ModelTrait
 * @author Elisha Temiloluwa a.k.a TemmyScope (temmyscope@protonmail.com)
 **/

trait ModelTrait
{
	/**
	* The following protected properties must be implemented, defined and declared in the Model class using this trait
	* @var static $table that is defined and declared in the child class of this model
	* @example protected static $table = 'user'; 
	*
	* @example
	* @var static $config = [
	*	    'dbname' => 'mydb',
	*	    'user' => 'user',
	*	    'password' => 'secret',
	*	    'host' => 'localhost',
	*	    'driver' => 'pdo_mysql',
	*	];
	*
	* @var $fulltext refers to fulltext colums that can be searched using complicated match...against sql queries.
	*
	*
	*/

	public function __call($method, $args)
	{
		$conn = DriverManager::getConnection(static::$config);
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
				return $conn->update($table, $args[0], $args[1]);

			/**
			* @param $data
			* @example
			* $data = [
			*	'column' => 'data'
			* 	'name' => 'John Well',
			*	'username' => 'john086'
			* ];
			* @return int last Insert Id
			*/
			case "insert":
				$conn->insert($table, $args[0]);
				return (int)$conn->lastInsertId();

			/**
			* @param string[] $columns
			* @param optional [] $clause
			* @return array
			*/
			case 'distinct':
				$columns = implode(', ', $args[0]);
				$sql = "SELECT DISTINCT {$columns} FROM {$table}";
				$where = "";
				$values = [];
				if (!empty($args[1])) {
					$where .= " WHERE ";
					foreach ($args[1] as $key => $value) {
						$where .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
						$a = 1;
						$values[] = str_replace('!', '', $value, $a);
					}
					$where = rtrim($where, ' AND');
				}
				return $conn->fetchAll("{$sql} {$where}", $values);

			/**
			 * @param [] $where clause
			 * @param [] $others such as ['groupby'=> , 'orderby'=>, 'limit'=>]
			 *
			 * @return array of arrays containing result set
			*/
			case 'query':
				$sql = "SELECT * FROM {$table}";
				$values = [];
				if ( !empty($args[0]) ) {		
					$sql .= " WHERE";												
					foreach ( $args[0] as $key => $value) {
						$sql .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
						$a = 1;
						$values[] = str_replace('!', '', $value, $a);						
					}																				
					$sql = rtrim($sql, ' AND');																		
				}																			
				if (array_key_exists('groupby', $args[1] ) && !empty($args[1]['groupby'])) {
					$sql .= " GROUP BY {$args[1]['groupby']}";				
				}
				if (array_key_exists('orderby', $args[1] ) && !empty($args[1]['orderby'])) {												
					$sql .= " ORDER BY {$args[1]['orderby']}";											
				}											
				if (array_key_exists('limit', $args[1] ) && !empty($args[1]['limit'])) {												
					$sql .= " LIMIT {$args[1]['limit']}";												
				}
				return $conn->fetchAll("$sql", $values);
			
			/**
			 * @param Array $where clause OR $where clause
			 *
			 * @return array of arrays containing result set
			**/
			case 'findor':
				$sql = "SELECT * FROM {$table} WHERE";
				$values = [];
				$where1 = "";
				foreach ($args[0] as $key => $value) {					
					$where1 .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
					$a = 1;
					$values[] = str_replace('!', '', $value, $a);
				}
				$where2 = "";
				foreach ($args[1] as $key => $value) {					
					$where2 .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
					$a = 1;
					$values[] = str_replace('!', '', $value, $a);
				}
				$sql = $sql.' ('.rtrim($where1, ' AND'). ') OR ('.rtrim($where2, ' AND').')';
				return $conn->fetchAll("$sql", $values);
				break;

			/**
			 * @param string $values to check e.g. "1,3, 4, 5, 7"
			 * @param string $column to use e.g. "id"
			 * @return array of arrays containing result set
			**/
			case 'findin':
				$sql = "SELECT * FROM {$table} WHERE";
				$values = $args[0];
				$column = str_replace('!', '', $arg[1], $a = 1);
				$sql .= ( static::negator($column) === true ) ? " {$column} NOT IN " : " {$column} IN ";
				$sql .= " ($values)";
				return $conn->fetchAll("$sql", $values);
				break;

			/**
			 * @param Array $where clause
			 *
			 * @return array of arrays containing result set
			**/
			case "findby":
				$sql = "SELECT * FROM {$table} WHERE";
				$values = [];
				foreach ($args[0] as $key => $value) {					
					$sql .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
					$a = 1;
					$values[] = str_replace('!', '', $value, $a);
				}
				$sql = rtrim($sql, ' AND');
				return $conn->fetchAll("$sql", $values);

			/**
			 * @param Array $where clause
			 *
			 * @return array of arrays containing result set
			**/
			case "findfirst":
				$sql = "SELECT * FROM {$table}";
				$where = "";
				$values = [];
				if (!empty($args)) {
					$where .= " WHERE ";
					foreach ($args[0] as $key => $value) {
						$where .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
						$a = 1;
						$values[] = str_replace('!', '', $value, $a);
					}
					$where = rtrim($where, ' AND');
				}
				$data = $conn->fetchAll("$sql $where LIMIT 1", $values);
				return (!empty($data)) ? (object)$data[0] : [];
			/**
			* @param Array $where clause
			*
			* @return bool
			**/
			case 'exists':
				$sql = "SELECT * FROM {$table} WHERE";
				$values = [];
				foreach ($args[0] as $key => $value) {
					$sql .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
					$a = 1;
					$values[] = str_replace('!', '', $value, $a);
				}
				$sql = rtrim($sql, ' AND')." LIMIT 1";
				return (empty($conn->fetchAll("$sql", $values))) ? false : true;
			/**
			 * @param string $search is the query to be searched for
			 * @param string[] $searchable column(s)
			 *
			 * @return array of arrays containing result set
			**/
			case "search":
				$query = "%".$args[0]."%";
				array_shift($args);
		  		$where = '';
		  		$values = [];
		  		if(!empty(static::$fulltext)){
		  			foreach (static::$fulltext as $key){
		  				$where .= "MATCH ({$key}) AGAINST (?) OR ";
		  				$values[] =  $query;
		  			}
		  		}
		  		if(!empty($args[0])){
		  			foreach($args[0] as $column){															
			  			$where .= "$column LIKE ? OR ";
			  			$values[] =  $query;													
			  		}
		  		}
		  		$where = rtrim($where, ' OR ');
		  		return $conn->fetchall("SELECT * FROM {$table} WHERE {$where}", $values);
		  	/**
			 * @param int $limit is the total that can be retrieved per page
			 * @param int $page_number 
			 * 
			 * @return array of arrays containing result set
			**/
			case "paginate":
				$offset = ($args[1] > 0) ? (($args[1] * $args[0])-$args[0] ) . ", " : 0 . ", ";
				$clause = 'LIMIT ';
				$clause .= $offset. $args[0];
		  		return $conn->fetchall("SELECT * FROM {$table} {$clause}");
		  	/**
			 * @param array $args[0] updates
			 * @param array $args[1] column's value to check
			 * @param array $args[2] values to check column against
			 * @return array of arrays containing result set
			**/
		  	case 'updatemany':
		  		$sql = "";
		  		$update = [];
				foreach ($args[0] as $key => $value) {					
					$sql .= "$key = ? ,";
					$update[] = $value;
					break;
				}
				$sql = trim($sql, ' ,');
				$values = implode(', ', $args[2]);
				$count = $conn->executeUpdate("UPDATE {$table} SET {$sql} WHERE {$args[1]} IN ({$values})", $update);
				return $count;
			/**
			 * @param string $column to be incremented
			 * @param value to increment with
			 * @param where[] clause
			 * @return number of rows affected
			**/
			case "add":
				$sql = "";
				foreach ($args[2] as $key => $value) {					
					$sql .= "$key = ? ";
					$value = $value;
					break;
				}
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} + {$args[1]}")->where("{$sql}")->setParameter(0, $value)->execute();

			/**
			 * @param string $column to be incremented
			 * @param value to use in decrementing
			 * @param where[] clause
			 * @return number of rows affected
			**/
			case "minus":
				$sql = "";
				foreach ($args[2] as $key => $value) {					
					$sql .= "$key = ? ";
					$value = $value;
					break;
				}
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} - {$args[1]}")->where("{$sql}")->setParameter(0, $value)->execute();
			/**
			 * @param string $column to be incremented
			 * @param where[] clause
			 * @return number of rows affected
			**/
			case "addone":
				$sql = "";
				foreach ($args[1] as $key => $value) {					
					$sql .= "$key = ? ";
					$value = $value;
					break;
				}
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} + 1")->where("{$sql}")->setParameter(0, $value)->execute();

			/**
			 * @param string $column to be decremented
			 * @param where[] clause
			 * @return number of rows affected
			**/
			case "minusone":
				$sql = "";
				foreach ($args[1] as $key => $value) {					
					$sql .= "$key = ? ";
					$value = $value;
					break;
				}
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} - 1")->where("{$sql}")->setParameter(0, $value)->execute();
			/**
			 * @param string column to count, default is *
			 *
			 * @return int value
			**/
			case "count":
			$where = "";
			$values = [];
				if(isset($args[1])){
					$where .= "WHERE ";
		  			foreach($args[1] as $column => $value){															
						$where .= ( static::negator($value) === true ) ? " {$column}  != ? AND" : " {$column}  = ? AND";
						$a = 1;
						$values[] = str_replace('!', '', $value, $a);												
			  		}
		  		}
		  		$where = rtrim($where, ' AND');
				return (int)$conn->fetchall("SELECT COUNT({$args[0]}) as total FROM {$table} {$where}", $values)[0]['total'];
			/**
			 * @return array columns
			**/
			case 'showcolumns':
				$t = $conn->fetchall("SHOW COLUMNS FROM {$table}");
				$columns = [];
				foreach($t as $key => $value){
					$columns[$value['Field']] = true;
				}
  				return $columns;
			/**
			 * @param array $where clause
			 *
			 * @return number of affected columns
			**/
			case 'delete':
				return $conn->delete(static::$table, $args[0]);

			/**
			 * @param array $where clause
			 *
			 * @return number of affected columns
			**/
			case 'softdelete':
				return $conn->update(static::$table, [ 'deleted' => 'true' ], $args[0]);
			/**
			 * @return Doctrine\DBAL fluent $queryBuilder instance
			**/
			case 'fluent':
				return $fluent;
			/**
			 * If things go south and the called method does not exist
			 *
			 * @return void
			**/
			default:
				throw new Exception("Undefined method '$method'");
		}
	}

	public static function __callStatic($method, $args)
	{
		$conn = DriverManager::getConnection(static::$config);
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
				return $conn->update($table, $args[0], $args[1]);

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
				return (int)$conn->lastInsertId();
			/**
			* @param string[] $columns
			* @param optional [] $clause
			* @return array
			*/
			case 'distinct':
				$columns = implode(', ', $args[0]);
				$sql = "SELECT DISTINCT {$columns} FROM {$table}";
				$where = "";
				$values = [];
				if (!empty($args[1])) {
					$where .= " WHERE ";
					foreach ($args[1] as $key => $value) {
						$where .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
						$a = 1;
						$values[] = str_replace('!', '', $value, $a);
					}
					$where = rtrim($where, ' AND');
				}
				return $conn->fetchAll("{$sql} {$where}", $values);

			/**
			 * @param [] $where clause
			 * @param [] $others such as ['groupby'=> , 'orderby'=>, 'limit'=>]
			 *
			 * @return array of arrays containing result set
			*/
			case 'query':
				$sql = "SELECT * FROM {$table}";
				$values = [];
				if ( !empty($args[0]) ) {		
					$sql .= " WHERE";												
					foreach ( $args[0] as $key => $value) {
						$sql .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
						$a = 1;
						$values[] = str_replace('!', '', $value, $a);						
					}																				
					$sql = rtrim($sql, ' AND');																		
				}																			
				if (array_key_exists('groupby', $args[1] ) && !empty($args[1]['groupby'])) {
					$sql .= " GROUP BY {$args[1]['groupby']}";				
				}
				if (array_key_exists('orderby', $args[1] ) && !empty($args[1]['orderby'])) {												
					$sql .= " ORDER BY {$args[1]['orderby']}";											
				}											
				if (array_key_exists('limit', $args[1] ) && !empty($args[1]['limit'])) {												
					$sql .= " LIMIT {$args[1]['limit']}";												
				}
				return $conn->fetchAll("$sql", $values);
			
			/**
			 * @param Array $where first  clause
			 * @param Array $where alternative clause
			 * @return array of arrays containing result set
			**/
			case 'findor':
				$sql = "SELECT * FROM {$table} WHERE";
				$values = [];
				$where1 = "";
				foreach ($args[0] as $key => $value) {					
					$where1 .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
					$a = 1;
					$values[] = str_replace('!', '', $value, $a);
				}
				$where2 = "";
				foreach ($args[1] as $key => $value) {					
					$where2 .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
					$a = 1;
					$values[] = str_replace('!', '', $value, $a);
				}
				$sql = $sql.' ('.rtrim($where1, ' AND'). ') OR ('.rtrim($where2, ' AND').')';
				return $conn->fetchAll("$sql", $values);
				break;

			/**
			 * @param string $values to check e.g. "1,3, 4, 5, 7"
			 * @param string $column to use e.g. "id"
			 * @return array of arrays containing result set
			**/
			case 'findin':
				$sql = "SELECT * FROM {$table} WHERE";
				$values = $args[0];
				$column = str_replace('!', '', $arg[1], $a = 1);
				$sql .= ( static::negator($column) === true ) ? " {$column} NOT IN " : " {$column} IN ";
				$sql .= " ($values)";
				return $conn->fetchAll("$sql", $values);
				break;
				
			/**
			 * @param Array $where clause
			 *
			 * @return array of arrays containing result set
			**/
			case "findby":
				$sql = "SELECT * FROM {$table} WHERE";
				$values = [];
				foreach ($args[0] as $key => $value) {					
					$sql .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
					$a = 1;
					$values[] = str_replace('!', '', $value, $a);
				}
				$sql = rtrim($sql, ' AND');
				return $conn->fetchAll("$sql", $values);

			/**
			 * @param Array $where clause
			 *
			 * @return array of arrays containing result set
			**/
			case "findfirst":
				$sql = "SELECT * FROM {$table}";
				$where = "";
				$values = [];
				if (!empty($args)) {
					$where .= " WHERE ";
					foreach ($args[0] as $key => $value) {
						$where .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
						$a = 1;
						$values[] = str_replace('!', '', $value, $a);
					}
					$where = rtrim($where, ' AND');
				}
				$data = $conn->fetchAll("$sql $where LIMIT 1", $values);
				return (!empty($data)) ? (object)$data[0] : [];
			/**
			* @param Array $where clause
			*
			* @return bool
			**/
			case 'exists':
				$sql = "SELECT * FROM {$table} WHERE";
				$values = [];
				foreach ($args[0] as $key => $value) {
					$sql .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
					$a = 1;
					$values[] = str_replace('!', '', $value, $a);
				}
				$sql = rtrim($sql, ' AND')." LIMIT 1";
				return (empty($conn->fetchAll("$sql", $values))) ? false : true;
			/**
			 * @param string $search is the query to be searched for
			 * @param string[] $searchable column(s)
			 *
			 * @return array of arrays containing result set
			**/
			case "search":
				$query = "%".$args[0]."%";
				array_shift($args);
		  		$where = '';
		  		$values = [];
		  		if(!empty(static::$fulltext)){
		  			foreach (static::$fulltext as $key){
		  				$where .= "MATCH ({$key}) AGAINST (?) OR ";
		  				$values[] =  $query;
		  			}
		  		}
		  		if(!empty($args[0])){
		  			foreach($args[0] as $column){															
			  			$where .= "$column LIKE ? OR ";
			  			$values[] =  $query;													
			  		}
		  		}
		  		$where = rtrim($where, ' OR ');
		  		return $conn->fetchall("SELECT * FROM {$table} WHERE {$where}", $values);
		  	/**
			 * @param int $limit is the total that can be retrieved per page
			 * @param int $page_number 
			 * 
			 * @return array of arrays containing result set
			**/
			case "paginate":
				$offset = ($args[1] > 0) ? (($args[1] * $args[0])-$args[0] ) . ", " : 0 . ", ";
				$clause = 'LIMIT ';
				$clause .= $offset. $args[0];
		  		return $conn->fetchall("SELECT * FROM {$table} {$clause}");
		  	/**
			 * @param array $args[0] updates
			 * @param array $args[1] column's value to check
			 * @param array $args[2] values to check column against
			 * @return array of arrays containing result set
			**/
		  	case 'updatemany':
		  		$sql = "";
		  		$update = [];
				foreach ($args[0] as $key => $value) {					
					$sql .= "$key = ? ,";
					$update[] = $value;
					break;
				}
				$sql = trim($sql, ' ,');
				$values = implode(', ', $args[2]);
				$count = $conn->executeUpdate("UPDATE {$table} SET {$sql} WHERE {$args[1]} IN ({$values})", $update);
				return $count;
			/**
			 * @param string $column to be incremented
			 * @param value to increment with
			 * @param where[] clause
			 * @return number of rows affected
			**/
			case "add":
				$sql = "";
				foreach ($args[2] as $key => $value) {					
					$sql .= "$key = ? ";
					$value = $value;
					break;
				}
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} + {$args[1]}")->where("{$sql}")->setParameter(0, $value)->execute();

			/**
			 * @param string $column to be incremented
			 * @param value to use in decrementing
			 * @param where[] clause
			 * @return number of rows affected
			**/
			case "minus":
				$sql = "";
				foreach ($args[2] as $key => $value) {					
					$sql .= "$key = ? ";
					$value = $value;
					break;
				}
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} - {$args[1]}")->where("{$sql}")->setParameter(0, $value)->execute();
			/**
			 * @param string $column to be incremented
			 * @param where[] clause
			 * @return number of rows affected
			**/
			case "addone":
				$sql = "";
				foreach ($args[1] as $key => $value) {					
					$sql .= "$key = ? ";
					$value = $value;
					break;
				}
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} + 1")->where("{$sql}")->setParameter(0, $value)->execute();

			/**
			 * @param string $column to be decremented
			 * @param where[] clause
			 * @return number of rows affected
			**/
			case "minusone":
				$sql = "";
				foreach ($args[1] as $key => $value) {					
					$sql .= "$key = ? ";
					$value = $value;
					break;
				}
				return $fluent
				    ->update($table)
				    ->set($args[0], "{$args[0]} - 1")->where("{$sql}")->setParameter(0, $value)->execute();
			/**
			 * @param string column to count, default is *
			 *
			 * @return int value
			**/
			case "count":
			$where = "";
			$values = [];
				if(isset($args[1])){
					$where .= "WHERE ";
		  			foreach($args[1] as $column => $value){															
						$where .= ( static::negator($value) === true ) ? " {$column}  != ? AND" : " {$column}  = ? AND";
						$a = 1;
						$values[] = str_replace('!', '', $value, $a);												
			  		}
		  		}
		  		$where = rtrim($where, ' AND');
				return (int)$conn->fetchall("SELECT COUNT({$args[0]}) as total FROM {$table} {$where}", $values)[0]['total'];
			/**
			 * @return array columns
			**/
			case 'showcolumns':
				$t = $conn->fetchall("SHOW COLUMNS FROM {$table}");
				$columns = [];
				foreach($t as $key => $value){
					$columns[$value['Field']] = true;
				}
  				return $columns;
			/**
			 * @param array $where clause
			 *
			 * @return number of affected columns
			**/
			case 'delete':
				return $conn->delete(static::$table, $args[0]);

			/**
			 * @param array $where clause
			 *
			 * @return number of affected columns
			**/
			case 'softdelete':
				return $conn->update(static::$table, [ 'deleted' => 'true' ], $args[0]);
			/**
			 * @return Doctrine\DBAL fluent $queryBuilder instance
			**/
			case 'fluent':
				return $fluent;
			/**
			 * If things go south and the called method does not exist
			 *
			 * @return void
			**/
			default:
				throw new Exception("Undefined method '$method'");
		}
	}

	/**
	 * @param string $table to perform queries on
	 * @return Model instance
	**/
	public static function setTable(string $table): self
	{
		static::$table = $table;
		return new static();
	}

	/** Checks if a string contains !
	 * @param string $value to test
	 * @return bool
	**/
	public static function negator(string $value): bool
	{
		if ( $value[0] === "!"){
			return true;
		}
		return false;
	}
}