A Model Traits Package built on top of Doctrine's DBAL library, for extending and easing up the use of Doctrine's DBAL.

The ModelTrait should be used inside the Model Class of your project. It requires Doctrine's DBAL package.

The only methods available are :

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

=> delete([ where column => value ])

=> softdelete([ where column => value ]) //Constrain: the table must have a deleted column 

=> fluent() returns an instance of the Doctrine's DBAL query builder