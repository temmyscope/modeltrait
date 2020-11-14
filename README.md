## Seven SQL DataBase Model
	
	- It is part of the libraries used on the altvel framework project but can be used in any applicable project

	- Model & ModelTrait a.k.a model-trait is developed by Elisha Temiloluwa a.k.a TemmyScope	

	- Developed to make easier the routine of database querying for small to medium-scale projects

	- It is a lightweight wrapper trait around the Doctrine DBAL's Library, maintaining a sane level of simplicity.

	- It is a lighter ORM Trait for developers that would prefer a light, capable and easy-to-use library rather than an Omnipotent Library.


## Installation

```bash
composer require sevens/model-trait
```

## Usage Properties

```php
/**
* The following protected properties must be implemented, defined and declared in the Model Child class using this trait
*
* @property string $table that is defined and declared in the child class of this model
* @example protected static $table = 'user';
*
*/

  protected static $table;

/**
  * If none, set to empty array
  *
  * @property [] $fulltext refers to fulltext columns that can be searched using complicated match...against sql queries.
  *
  */

protected static $fulltext;

  /**
  * If you want to retrieve all, leave as empty 
  *
  * @property array $fetchable
  * @example  = [ 'id', 'username', 'email'];
  */

protected static $fetchable;

```

## The methods available are :

```php
#returns all rows in the table 
=> all()

=> query(['columns' => 'values'], ['groupby' => '', 'orderby' => '', 'limit' => 10]);

=> findby( [ 'column' => 'value' ] )

=> findor([ clause ], [ alternative clause ])

=> findin(["values", ...], "column")

=> update([ "column" => "new value" ], $where = [ "id" => 1 ]);

=> exists(['column' => 'value']);

=> search('search for this string', [columns to check]);

=> count(column to count,  [ where column => value]);

=> paginate('number of items to return per page', page number);

=> updateMany([column=> new_value], column, [$identifiers])

=> delete([ where column => value ])

//Constraint: the table must have a deleted column , which will be set to true; else it will return a fatal error.
=> softdelete([ where column => value ]) 

=> fluent() returns an instance of Doctrines DBAL QueryBuilder

Note: the where clause of the methods below will only load a single column condition i.e. "column = ?"

=> add('column to increment', value to add, ['where column' => value]) 

=> minus('column to decreas', value to deduct, ['where column' => value]) the where clause only loads a single "column = ?"

=> addOne('column to increment', ['where column' => value)] the where clause only loads a sing "column = ?"

=> minusOne('column to decrease', ['where column' => value]) the where clause only loads a sing "column = ?"

```

=> addition of query method that accepts group, order and limits conditions.

=>support for negators to all selector method that accept clause(s) including exists(), findOr, findIn and count() queries.

e.g Users::findby([ 'deleted' => '!false' ]) is equivalent to 

```sql 
SELECT * FROM users WHERE deleted != 'false';
```

=> addition of findor for select this OR that statements
e.g. Users::findor(["name" => "Elisha"], ["name" => "Temiloluwa"]);
```sql 
SELECT * FROM users WHERE name = "Elisha" OR name = "Temiloluwa";
```

=> addition of findin for select in statements
e.g. Users::findin("1, 2, 5, 7", "id");
```sql 
SELECT * FROM users WHERE id IN (1, 2, 3, 5, 7);
```

=> addition of distinct for select distinct statements
e.g. Users::distinct("name");
```sql 
SELECT DISTINCT name FROM users;
```

=> addition of updateMany for multiple updates at once 
e.g. User::updateMany(['verified'=> 'true', 'active' => 'false'], 'id', [1, 9, 10]);
```sql 
UPDATE user SET verified = 'true', active = 'false' WHERE id IN (1, 9, 10);
```

### Usage
##

```php
#import the library into your model class namespace

use Seven\Model\Model;

class User extends Model
{
	//the table variable
	protected static $table = 'user';

	//the fulltext columns in the above table, for optimized complicated Match...Against Queries.
	protected static $fulltext = [];

	//queries will only return columns in the fetchable array
	protected static $fetchable = ['id', 'name', 'created_at'];
}
```

### Example Usage
##

```php
User::all();

User::query(['deleted' => 'false'], ['groupby' => 'year', 'orderby' => 'id', 'limit' => 10]);

User::distinct(['id', 'first_name', 'age'], ['deleted' => 'false']);

User::count('id', ['verified' => 'true']);

User::avg('balance', ['verified' => 'true']  );

User::max('balance', 'max_balance');

User::min('balance', 'min_balance');

User::range('balance', [ 10.80, 89.50 ]);

#returns users where the condition is passed or true
//support for >, < operators
User::operator([ 'balance' => '>12.00' ])

User::dateRange('created_at', [ '9/27/2018', '9/27/2020' ]);

User::insert([
	"first_name" => "Elisha", 
	"other_names" => "Temiloluwa", 
	"last_name" => "Oyawale", 
	"timestamp" => "2019-11-02 15:28:56"
]);

User::findBy([ "other_names" => "Aminat" ]);

User::findOr(["name" => "Elisha"], ["name" => "scope"]);

User::findIn([1, 2, 3], "id"); 

//also supports negator  => means where id NOT IN (1, 2, 3)
User::findin([1, 2, 3], "!id");

#returns number of affected rows
User::updateMany(['verified'=> 'true'], 'id', [1, 9, 10]);

#returns number of affected rows
User::update([ "other_names" => "Aminat" ], [ "id" => 1 ]);

User::exists(['id' => 2]);

User::search('Elisha', ['firstname', 'lastname']);

User::add('views', 1, ['id' => 2]); //returns number of affected rows

User::addOne('views', ['id' => 2]); //returns number of affected rows

User::minus('balance', 1, ['id' => 2]); //returns number of affected rows

User::minusOne('balance', ['id' => 2]); //returns number of affected rows

User::paginate(10, (int)$_GET['page']);

User::setTable('posts')->all(); //changes the table to 

User::softDelete(['id' => 2]); //sets deleted column to true

User::delete(['id' => 2]) //returns number of affected rows

User::showColumns() //showns all the columns in this table as properties of the stdClass object

User::fluent(); //returns an instance of Doctrines's DBAL QueryBuilder
```