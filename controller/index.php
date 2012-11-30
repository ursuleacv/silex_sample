<?php

$app->get('/', function () use ($app) {
  $name = "Habr";
  return $app['view']->render('layout.phtml', 'index/hello.phtml', array(
    'name' => $name
  ));
});

$app->get('/test/', function () use ($app) {
  $test = "Test";
  return $app['view']->render(null, 'index/test.phtml', array(
    'test' => $test
  ));
});

$app->get('/authors/', function () use ($app) {
  $ipp = 3;
  $p = $app['request']->get('p', 1);

  $adapter = new Art\PfAdapter('Model\Author', array(
    'conditions' => 'id < 1000',
    'order' => 'id DESC'
  ));

  $pagerfanta = new Pagerfanta\Pagerfanta($adapter);
  $pagerfanta->setMaxPerPage($ipp);
  $pagerfanta->setCurrentPage($p);

  $view = new Pagerfanta\View\DefaultView;
  $html = $view->render($pagerfanta, function($p) use ($app) {
    return $app['url_generator']->generate('authors', array('p' => $p));
  }, array(
    'proximity'         => 3,
    'previous_message'  => '« Предыдущая',
    'next_message'      => 'Следующая »'
  ));

  return $app['view']->render('layout.phtml', 'index/authors.phtml', array(
    'pagerfanta' => $pagerfanta,
    'html' => $html
  ));
})->bind('authors');

$app->get('/book/{id}.html', function ($id) use ($app) {
	$book = Model\Book::find_by_id($id);
  if ( !$book ) {
    $app->abort(404, "Book {$id} does not exist.");
  }

  return $app['view']->render('layout.phtml', 'index/book.phtml', array(
    'book' => $book
  ));
})->bind('book');

$app->match('/form/', function () use ($app) {
  $form = new HTML_QuickForm2('search', 'get', array('action' => ""));
  $form->addElement('text', 'name')
    ->setlabel('Имя автора')
    ->addRule('required', 'Поле обязательно для заполнения');

  $form->addElement('button', null, array('type' => 'submit'))
    ->setContent('ОК');

  if ( $form->isSubmitted() && $form->validate() ) {
    $values = $form->getValue();

    $author = new Model\Author;
    $author->name = $values['name'];   
    $author->save();

    // post POST redirect
    return new \Symfony\Component\HttpFoundation\RedirectResponse(
      $app['url_generator']->generate('authors')
    );
  }

  return $app['view']->render('layout.phtml', 'index/form.phtml', array(
    'form' => $form
  ));
});