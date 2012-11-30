<?php
namespace Model;

class Author extends \ActiveRecord\Model {
	static $table_name = 'author';

	static $has_many = array(
    array('books', 'foreign_key' => 'authorId', 'class_name' => 'Model\Book'),
  );
}