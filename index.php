<?php
require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();

if ( '127.0.0.1' == $_SERVER['REMOTE_ADDR'] ) {
  $app['debug'] = true;
}

spl_autoload_register(function( $className ) {
  // Namespace mapping
  $namespaces = array(
    "Art" => __DIR__ . "/vendor/Art",
    "Model" => __DIR__ . "/model",
    "Pagerfanta" => __DIR__ . "/vendor/Pagerfanta"
  );

  foreach ( $namespaces as $ns => $path ) {
    if ( 0 === strpos( $className, "{$ns}\\" ) ) {
      $pathArr = explode( "\\", $className );
      $pathArr[0] = $path;

      $class = implode(DIRECTORY_SEPARATOR, $pathArr);

      require_once "{$class}.php";
    }
  }
});

// Services
// Art\View
$app['view'] = $app->share(function () use ($app) {
  return new Art\View($app);
});

// Config
$app['conf'] = $app->share(function () use ($app) {
  $data = parse_ini_file( __DIR__ . '/conf/app.ini', true );
  return $data;
});

// UrlGenerator
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Form
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());

// PHPActiveRecord
require_once __DIR__ . '/vendor/AR/ActiveRecord.php';
ActiveRecord\Config::initialize(function($cfg) use ($app) {
  $cfg->set_model_directory( __DIR__ . '/model');
  $cfg->set_connections(array(
    'production' => $app['conf']['db']['dsn']
  ));

  $cfg->set_default_connection('production');
});

// HTML_QuickForm2
set_include_path(
  get_include_path() . PATH_SEPARATOR .
  __DIR__ . "/vendor/QuickForm2"
);
require_once __DIR__ . '/vendor/QuickForm2/HTML/QuickForm2.php';
require_once __DIR__ . '/vendor/QuickForm2/HTML/QuickForm2/Renderer.php';


// Controlelrs
foreach ( glob(__DIR__."/controller/*.php") as $filename ) {
  require_once $filename;
}

$app->error(function (\Exception $e, $code) use ($app) {
  if ( $app['debug'] ) {
    return;
  }

  return $app['view']->render('layout.phtml', 'error.phtml', array(
    'msg' => $e->getMessage(),
    'code' => $code
  ));
});

$app->run();