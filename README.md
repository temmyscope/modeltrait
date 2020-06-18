A Model Traits Package built on top of Doctrine's DBAL library, for extending and easing up the use of Doctrine's DBAL.

The ModelTrait should be used inside the Model Class of your project. It requires Doctrine's DBAL package.

#The only methods available are :

```bash
=> all() // returns all rows in the table 

=> query(['columns' => 'values'], ['groupby' => '', 'orderby' => '', 'limit' => 10]);

=> findby( [ 'column' => 'value' ] )

=> findor

=> findin("values", "column")

=> update([ "column" => "new value" ], $where = [ "id" => 1 ]);

=> exists(['column' => 'value']);

=> search('search for this string', [columns to check]);

=> count(column to count,  [ where column => value]);

=> paginate('number of items to return per page', page number); 

=> delete([ where column => value ])

=> softdelete([ where column => value ]) //Constrain: the table must have a deleted column , which will be set to true; else it will return a fatal error.

=> fluent() returns an instance of the Doctrine's DBAL query builder
```

```
Note: the where clause only loads a single column condition i.e. "column = ?"

=> add('column to increment', value to add, ['where column' => value]) 

=> minus('column to decreas', value to deduct, ['where column' => value]) the where clause only loads a single "column = ?"

=> addOne('column to increment', ['where column' => value]) the where clause only loads a sing "column = ?"

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
		'dbname' => 'ratemylecturer',
		'user' => 'root',
		'password' => '',
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
}


//You can call the methods like these:

User::all();

User::query(['deleted' => 'false'], ['groupby' => 'year', 'orderby' => 'id', 'limit' => 10]);

User::insert([
	"first_name" => "Elisha", 
	"other_names" => "Temiloluwa", 
	"last_name" => "Oyawale", 
	"timestamp" => "2019-11-02 15:28:56"
]);

User::findby([ "other_names" => "Aminat" ]);

User::findor(["name" => "Elisha"], ["name" => "scope"]);

User::findin("1, 2, 3", "id");

User::updateMany(['verified'=> 'true'], 'id', [1, 9, 10]);

User::update([ "other_names" => "Aminat" ], [ "id" => 1 ]);

User::exists(['id' => 2]);

User::search('Elisha', ['firstname', 'lastname']);

User::addOne('views', ['id' => 2]);

User::paginate(10, (int)$_GET['page']);

User::delete(['id' => 2])

User::showColumns() //showns all the columns in this table as properties of the stdClass object

User::fluent(); //returns an instance of Doctrines's DBAL QueryBuilder
```