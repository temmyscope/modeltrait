A Model Traits Package built on top of Doctrine's DBAL library, for extending and easing up the use of Doctrine's DBAL.

The ModelTrait should be used inside the Model Class of your project. It requires Doctrine's DBAL package.

#The only methods available are :

```bash
=> all() // returns all rows in the table 

=> findby( [ 'column' => 'value' ] )

=> update([ "column" => "new value" ], $where = [ "id" => 1 ]);

=> exists(['column' => 'value']);

=> search('search for this string', [columns to check]);

=> add('column to increment', value to add, ['where column' => value]) the where clause only loads a sing "column = ?"

=> minus('column to decreas', value to deduct, ['where column' => value]) the where clause only loads a sing "column = ?"

=> addOne('column to increment', ['where column' => value]) the where clause only loads a sing "column = ?"

=> minusOne('column to decrease', ['where column' => value]) the where clause only loads a sing "column = ?"

=> count(column to count,  [ where column => value]);

=> paginate('number of items to return per page', page number); 

=> delete([ where column => value ])

=> softdelete([ where column => value ]) //Constrain: the table must have a deleted column 

=> fluent() returns an instance of the Doctrine's DBAL query builder
```

##An Example use case of this trait and how to fuse it right into your model classes is shown below:

```bash
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

User::insert([
	"first_name" => "Elisha", "other_names" => "Temiloluwa", "last_name" => "Oyawale", "timestamp" => "2019-11-02 15:28:56",
]);

User::findby([ "other_names" => "Aminat" ]);

User::update([ "other_names" => "Aminat" ], [ "id" => 1 ]);

User::exists(['id' => 2]);

User::search('Elisha', ['firstname', 'lastname']);

User::addOne('views', ['id' => 2]);

User::paginate(10, (int)$_GET['page']);

User::delete(['id' => 2])

User::fluent(); //returns an instance of Doctrines's DBAL QueryBuilder
```