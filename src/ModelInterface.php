<?php

namespace Seven\Model;

/**
 *
 * @interface ModelInterface
 * @author Elisha Temiloluwa a.k.a TemmyScope (temmyscope@protonmail.com)
 *
**/

interface ModelInterface{

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

}