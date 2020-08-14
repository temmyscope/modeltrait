A Model Traits Package built on top of Doctrine's DBAL library, for extending and easing up the use of Doctrine's DBAL.

The ModelTrait should be used inside the Model Class of your project. It requires Doctrine's DBAL package.


## Installation

```bash
composer require sevens/vars
```


#The methods available are :

```php
// returns all rows in the table 
=> all()

=> query(['columns' => 'values'], ['groupby' => '', 'orderby' => '', 'limit' => 10]);

=> findby( [ 'column' => 'value' ] )

=> findor([ clause ], [ alternative clause ])

=> findin(["values"], "column")

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

Version 1.5.0 upward brings :

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


```php

##An Example use case of this trait and how to fuse it right into your model classes is shown below:

//import the library into your model class namespace
use Seven\Model\ModelTrait;


//setup your model class and the variables (with these names) necessary for the trait

class Model
{
	use ModelTrait;

	/**
	* This variable is extremely essential to the proper functioning of the trait due to the underlying Doctrine DBAL package  
	*/
	protected static $config = [
		'dbname' => 'sample',
		'user' => 'root',
		'password' => 'password',
		'host' => 'localhost',
	    'driver' => 'pdo_mysql'
	];

}

//set up individual model child classes with their static table name set
//The ModelTrait uses late static binding.

class User extends Model
{
	//the table variable
	protected static $table = 'user';

	//the fulltext columns in the above table, for optimized complicated Match...Against Queries.
	protected static $fulltext = [];

	//queries will only return columns in the fetchable array
	protected static $fetchable = ['id', 'name', 'created_at']; // new feature and backwards compatible
}


//You can call the methods like these:

User::all();

User::query(['deleted' => 'false'], ['groupby' => 'year', 'orderby' => 'id', 'limit' => 10]);

User::distinct(['id', 'first_name', 'age'], ['deleted' => 'false']);

User::count('id', ['verified' => 'true']);

User::Avg('balance', ['verified' => 'true']  );

User::Max('balance', 'max_balance');

User::Min('balance', 'min_balance');

User::range('balance', [ 10.80, 89.50 ]);

User::dateRange('created_at', [ '9/27/2018', '9/27/2020' ]);

User::insert([
	"first_name" => "Elisha", 
	"other_names" => "Temiloluwa", 
	"last_name" => "Oyawale", 
	"timestamp" => "2019-11-02 15:28:56"
]);

User::findby([ "other_names" => "Aminat" ]);

User::findOr(["name" => "Elisha"], ["name" => "scope"]);

User::findin("1, 2, 3", "id"); //also supports negator User::findin("1, 2, 3", "!id") => means where id NOT IN (1, 2, 3)

User::updateMany(['verified'=> 'true'], 'id', [1, 9, 10]);//returns number of affected rows

User::update([ "other_names" => "Aminat" ], [ "id" => 1 ]);//returns number of affected rows

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