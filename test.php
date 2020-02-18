<?php
/*
use Seven\Model\ModelTrait;


class Model
{
	use ModelTrait;

	protected static 
	$config = [
		'dbname' => 'ratemylecturer',
		'user' => 'root',
		'password' => '',
		'host' => 'localhost',
	    'driver' => 'pdo_mysql'
	];

}

class User extends Model
{
	protected static $fulltext = [];
	protected static $table = 'userd';
}

User::all();
User::insert([
	"first_name" => "Elisha", "other_names" => "Temiloluwa", "last_name" => "Oyawale", "timestamp" => "2019-11-02 15:28:56",
]);
User::findby([ "other_names" => "Aminat" ]);
User::update([ "other_names" => "Aminat" ], [ "id" => 1 ]);
User::exists(['id' => 2]);
User::search(['Elisha'], ['firstname', 'lastname']);
User::add();

User::delete(['id' => 2])

User::fluent();

*/