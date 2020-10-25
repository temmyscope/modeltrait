<?php

namespace Seven\Model;

use Seven\Model\{ModelTrait, ModelInterface};

/**
 *
 * @package ModelTrait
 * @author Elisha Temiloluwa a.k.a TemmyScope (temmyscope@protonmail.com)
 *
**/

class Model implements ModelInterface{
	
		use ModelTrait;

		protected static $table;

		protected static $fulltext;

		protected static $fetchable;

}