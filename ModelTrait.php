<?php

namespace Seven\Model;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Should be used in a model class that defines the static $config
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
    *       'dbname' => 'mydb',
    *       'user' => 'user',
    *       'password' => 'secret',
    *       'host' => 'localhost',
    *       'driver' => 'pdo_mysql',
    *   ];
    *
    * @var $fulltext refers to fulltext colums that can be searched using complicated match...against sql queries.
    *
    *
    */

    private static function connection()
    {
        return [ DriverManager::getConnection(static::$config), static::$table ];
    }

    /**
    * @param $data
    * @example
    * $data = [
    *   'column' => 'data'
    *   'name' => 'John Well',
    *   'username' => 'john086'
    * ];
    * @return int last Insert Id
    */
    public static function insert($data): int
    {
        [ $conn, $table ] = static::connection();
        $conn->insert($table, $data);
        return (int)$conn->lastInsertId();
    }

    /**
    * @return array of arrays
    */
    public static function all(): array
    {
        [ $conn, $table ] = static::connection();
        return $conn->fetchAll("SELECT * FROM {$table}");
    }

    /**
    * @param string $column
    * @param string $alias
    *
    * @return array of arrays
    */
    public static function max(string $column, string $alias): array
    {
        [ $conn, $table ] = static::connection();
        return $conn->fetchAll("SELECT MAX({$column}) AS {$alias} FROM {$table}");
    }

    /**
    * @param string $column
    * @param string $alias
    *
    * @return array of arrays
    */
    public static function min(string $column, string $alias): array
    {
        [ $conn, $table ] = static::connection();
        return $conn->fetchAll("SELECT MIN({$column}) AS {$alias} FROM {$table}");
    }

    /**
     * @param Array $where clause
     *
     * @return array of arrays containing result set
    **/
    public static function operator(array $where): array
    {
        [ $conn, $table ] = static::connection();
        $cols = (isset(static::$fetchable)) ? implode(', ', static::$fetchable) : "*";
        $sql = "SELECT {$cols} FROM {$table} WHERE";
        $values = [];
        foreach ($where as $key => $value) {
            $operator = static::operation($value);
            $sql .= " {$key} {$operator} ? AND";
            $values[] = ltrim($value, $operator[0]);
        }
        $sql = rtrim($sql, ' AND');
        return $conn->fetchAll($sql, $values);
    }

    /**
    * @param $column
    * @param $range of format [min_value, max_value]
    * @example of range = [0, 9]
    *
    * @return array of arrays
    */
    public static function range(string $column, array $range): array
    {
        [ $conn, $table ] = static::connection();
        return $conn->fetchAll("SELECT * FROM {$table} WHERE {$column} BETWEEN {$range[0]} AND {$range[1]}");
    }

    /**
    * @param $column
    * @param $range of date [min_value, max_value]
    * @example date 07/04/2020 refers to 04th of july, 2020
    *
    * @return array of arrays
    */
    public static function dateRange(string $column, array $range): array
    {
        return static::range($column, [ '#' . $range[0] . '#', '#' . $range[1] . '#' ]);
    }

    /**
     * @param string $values to check e.g. "1,3, 4, 5, 7"
     * @param string $column to use e.g. "id"
     * @return array of arrays containing result set
    **/
    public static function findIn(string $values, string $column_value): array
    {
        [ $conn, $table ] = static::connection();
        $cols = (isset(static::$fetchable)) ? implode(', ', static::$fetchable) : "*";
        $sql = "SELECT {$cols} FROM {$table} WHERE";
        $column = ltrim($column_value, '!');
        $sql .= ( static::negator($column_value) === true ) ?
            " {$column} NOT IN ($values)" : " {$column} IN ($values)";
        return $conn->fetchAll($sql);
    }

    /**
     * @param Array $where clause
     *
     * @return array of arrays containing result set
    **/
    public static function findBy(array $where): array
    {
        [ $conn, $table ] = static::connection();
        $cols = (isset(static::$fetchable)) ? implode(', ', static::$fetchable) : "*";
        $sql = "SELECT {$cols} FROM {$table} WHERE";
        $values = [];
        foreach ($where as $key => $value) {
            $sql .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
            $a = 1;
            $values[] = ltrim($value, '!');
        }
        $sql = rtrim($sql, ' AND');
        return $conn->fetchAll($sql, $values);
    }

    /**
     * @param int $limit is the total that can be retrieved per page
     * @param int $page_number
     *
     * @return array of arrays containing result set
    **/
    public static function paginate(int $limit, int $page_num): array
    {
        [ $conn, $table ] = static::connection();
        $offset = ($page_num > 0) ? (($page_num * $limit) - $limit ) . ", " : 0 . ", ";
        $clause = 'LIMIT ';
        $clause .= $offset . $limit;
        $cols = (isset(static::$fetchable)) ? implode(', ', static::$fetchable) : "*";
        return $conn->fetchall("SELECT {$cols} FROM {$table} {$clause}");
    }

    /**
     * @param string $search is the query to be searched for
     * @param string[] $column(s)
     *
     * @return array of arrays containing result set
    **/
    public static function search($search, $columns)
    {
        [ $conn, $table ] = static::connection();
        $query = "%" . $search . "%";
        $where = '';
        $values = [];
        if (!empty(static::$fulltext)) {
            foreach (static::$fulltext as $key) {
                $where .= "MATCH ({$key}) AGAINST (?) OR ";
                $values[] =  $query;
            }
        }
        if (!empty($columns)) {
            foreach ($columns as $column) {
                $where .= "$column LIKE ? OR ";
                $values[] =  $query;
            }
        }
        $where = rtrim($where, ' OR ');
        $cols = (isset(static::$fetchable)) ? implode(', ', static::$fetchable) : "*";
        return $conn->fetchall("SELECT {$cols} FROM {$table} WHERE {$where}", $values);
    }

    /**
    * @param Array $where clause
    *
    * @return bool
    **/
    public static function exists(array $where): bool
    {
        [ $conn, $table ] = static::connection();
        $sql = "SELECT * FROM {$table} WHERE";
        $values = [];
        foreach ($where as $key => $value) {
            $sql .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
            $values[] = ltrim($value, '!');
        }
        $sql = rtrim($sql, ' AND') . " LIMIT 1";
        return (empty($conn->fetchAll($sql, $values))) ? false : true;
    }

    /**
     * @param Array $where clause
     *
     * @return object OR empty array
    **/
    public static function findFirst($where)
    {
        [ $conn, $table ] = static::connection();
        $cols = (isset(static::$fetchable)) ? implode(', ', static::$fetchable) : "*";
        $sql = "SELECT {$cols} FROM {$table}";
        $where = "";
        $values = [];
        if (!empty($where)) {
            $where .= " WHERE ";
            foreach ($where as $key => $value) {
                $where .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
                $values[] = ltrim($value, '!');
            }
            $where = rtrim($where, ' AND');
        }
        $data = $conn->fetchAll($sql . $where . " LIMIT 1", $values);
        return (!empty($data)) ? (object)$data[0] : [];
    }

    /**
     * @param string column to get, default is *
     *
     * @return value
    **/
    public static function avg(string $column, array $clause)
    {
        [ $conn, $table ] = static::connection();
        $where = "";
        $values = [];
        if (isset($clause)) {
            $where .= "WHERE ";
            foreach ($clause as $column => $value) {
                $where .= ( static::negator($value) === true ) ? " {$column}  != ? AND" : " {$column}  = ? AND";
                $values[] = ltrim($value, '!');
            }
        }
        $where = rtrim($where, ' AND');
        return $conn->fetchall("SELECT AVG({$column}) AS avg FROM {$table} {$where}", $values)[0]['avg'];
    }

    /**
     * @param string column to count, default is *
     *
     * @return int value
    **/
    public static function count(string $column, array $clause = [])
    {
        [ $conn, $table ] = static::connection();
        $where = "";
        $values = [];
        if (isset($clause)) {
            $where .= "WHERE ";
            foreach ($clause as $column => $value) {
                $where .= ( static::negator($value) === true ) ? " {$column}  != ? AND" : " {$column}  = ? AND";
                $values[] = ltrim($value, '!');
            }
        }
        $where = rtrim($where, ' AND');
        return (int)$conn->fetchall("SELECT COUNT({$column}) as total FROM {$table} {$where}", $values)[0]['total'];
    }

    /**
     * @param [] $where clause
     * @param [] $others such as ['groupby'=> , 'orderby'=>, 'limit'=>]
     *
     * @return array of arrays containing result set
    */
    public static function query($clause = [], $filters = [])
    {
        [ $conn, $table ] = static::connection();
        $cols = (isset(static::$fetchable)) ? implode(', ', static::$fetchable) : "*";
        $sql = "SELECT {$cols} FROM {$table}";
        $values = [];
        if (!empty($clause)) {
            $sql .= " WHERE";
            foreach ($clause as $key => $value) {
                $sql .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
                $values[] = ltrim($value, '!');
            }
            $sql = rtrim($sql, ' AND');
        }
        if (array_key_exists('groupby', $filters) && !empty($filters['groupby'])) {
            $sql .= " GROUP BY {$filters['groupby']}";
        }
        if (array_key_exists('orderby', $filters) && !empty($filters['orderby'])) {
            $sql .= " ORDER BY {$filters['orderby']}";
        }
        if (array_key_exists('limit', $filters) && !empty($filters['limit'])) {
            $sql .= " LIMIT {$filters['limit']}";
        }
        return $conn->fetchAll($sql, $values);
    }

    /**
     * @param Array $where clause OR $where clause
     *
     * @return array of arrays containing result set
    **/
    public static function findOr(array $clause, array $alt): array
    {
        [ $conn, $table ] = static::connection();
        $cols = (isset(static::$fetchable)) ? implode(', ', static::$fetchable) : "*";
        $sql = "SELECT {$cols} FROM {$table} WHERE";
        $values = [];
        $where1 = "";
        foreach ($clause as $key => $value) {
            $where1 .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
            $values[] = ltrim($value, '!');
        }
        $where2 = "";
        foreach ($alt as $key => $value) {
            $where2 .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
            $values[] = ltrim($value, '!');
        }
        $sql = $sql . ' (' . rtrim($where1, ' AND') . ') OR (' . rtrim($where2, ' AND') . ')';
        return $conn->fetchAll($sql, $values);
    }

    /**
    * @param string[] $columns
    * @param optional [] $clause
    * @return array
    */
    public static function distinct(array $columns, $clause = []): array
    {
        [ $conn, $table ] = static::connection();
        $columns = implode(', ', $columns);
        $sql = "SELECT DISTINCT {$columns} FROM {$table}";
        $where = "";
        $values = [];
        if (!empty($clause)) {
            $where .= " WHERE ";
            foreach ($clause as $key => $value) {
                $where .= ( static::negator($value) === true ) ? " {$key}  != ? AND" : " {$key}  = ? AND";
                $values[] = ltrim($value, '!');
            }
            $where = rtrim($where, ' AND');
        }
        return $conn->fetchAll("{$sql} {$where}", $values);
    }

    /**
     * @return columns
    **/
    public static function showColumns()
    {
        [ $conn, $table ] = static::connection();
        $t = $conn->fetchall("SHOW COLUMNS FROM {$table}");
        $columns = [];
        foreach ($t as $key => $value) {
            $columns[$value['Field']] = true;
        }
        return (object)$columns;
    }

    /**
     * @return Doctrine\DBAL fluent $queryBuilder instance
    **/
    public static function fluent()
    {
        [ $conn, $table ] = static::connection();
        return  $conn->createQueryBuilder();
    }

    /**
     * @param string $column to be incremented
     * @param $inc value to increment with
     * @param where[] clause
     * @return number of rows affected
    **/
    public static function add($column, $inc, $where): int
    {
        $sql = "";
        $value  = "";
        foreach ($where as $key => $value) {
            $sql .= "$key = ? ";
            $value = $value;
            break;
        }
        return (int) static::fluent()->update(static::$table)->set($column, "{$column} + {$inc}")
        ->where("{$sql}")
        ->setParameter(0, $value)
        ->execute();
    }

    /**
     * @param string $column to be incremented
     * @param value to use in decrementing
     * @param where[] clause
     * @return number of rows affected
    **/
    public static function minus(string $column, $dec, array $where): int
    {
        $sql = "";
        foreach ($where as $key => $value) {
            $sql .= "$key = ? ";
            $value = $value;
            break;
        }
        return (int) static::fluent()->update(static::$table)
        ->set($column, "{$column} - {$dec}")
        ->where("{$sql}")->setParameter(0, $value)
        ->execute();
    }

    /**
     * @param string $column to be incremented
     * @param where[] clause
     * @return number of rows affected
    **/
    public static function addOne(string $column, array $where): int
    {
        $sql = "";
        foreach ($where as $key => $value) {
            $sql .= "$key = ? ";
            $value = $value;
            break;
        }
        return (int) static::fluent()->update(static::$table)
        ->set($column, "{$column} + 1")
        ->where("{$sql}")
        ->setParameter(0, $value)
        ->execute();
    }

    /**
     * @param string $column to be decremented
     * @param where[] clause
     * @return number of rows affected
    **/
    public static function minusOne(string $column, array $where): int
    {
        $sql = "";
        foreach ($where as $key => $value) {
            $sql .= "$key = ? ";
            $value = $value;
            break;
        }
        return (int) static::fluent()->update(static::$table)
        ->set($column, "{$column} - 1")
        ->where("{$sql}")
        ->setParameter(0, $value)
        ->execute();
    }

    /**
     * @param array $updates to be updated
     * @param array $clause as conditional statement
     * @return number of affected columns
    **/
    public static function update($updates, $clause): int
    {
        [ $conn, $table ] = static::connection();
        return (int)$conn->update($table, $updates, $clause);
    }

    /**
     * @param array $updates
     * @param string $column value to check
     * @param array $rows_identifier values to check column against
     * @return array of arrays containing result set
    **/
    public static function updateMany(array $updates, string $column, array $rows_identifier): int
    {
        [ $conn, $table ] = static::connection();
        $sql = "";
        $update = [];
        foreach ($updates as $key => $value) {
            $sql .= "$key = ? ,";
            $update[] = $value;
            break;
        }
        $sql = trim($sql, ' ,');
        $values = implode(', ', $rows_identifier);
        return (int)$conn->executeUpdate("UPDATE {$table} SET {$sql} WHERE {$column} IN ({$values})", $update);
    }

    /**
     * @param array $where clause
     *
     * @return number of affected columns
    **/
    public static function softDelete($where): int
    {
        [ $conn, $table ] = static::connection();
        return (int)$conn->update($table, [ 'deleted' => 'true' ], $where);
    }

    /**
     * @param array $where clause
     *
     * @return number of affected columns
    **/
    public static function delete($where): int
    {
        [ $conn, $table ] = static::connection();
        return (int)$conn->delete($table, $where);
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

    /**
    * Checks if a string contains !
    * @param string $value to test
    * @return bool
    **/
    private static function negator(string $value): bool
    {
        if ($value[0] === "!") {
            return true;
        }
        return false;
    }

    /**
    * Checks if a string contains special operators
    * @param string $value to test
    * @return string
    **/
    private static function operation(string $value): string
    {
        $operators = ['>', '<', '!'];
        foreach ($operators as $operator) {
            if ($value[0] === $operator) {
                if ($operator === '!') {
                    return '!=';
                }
                return $operator;
            }
        }
        return "";
    }
}