<?php
namespace Model;

class Book extends \ActiveRecord\Model {
	static $table_name = 'book';

	static $belongs_to = array(
      array('author', 'class_name' => 'Model\Author', 'foreign_key' => 'authorId'),
  );
}