<?php
  //Variales de errores
  ini_set('display_errors', 1);
  ini_set('display_starup_error', 1);
  error_reporting(E_ALL); //Todos los errores.

  require_once '../vendor/autoload.php';

  session_start();

  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
  $dotenv->load();

  use Illuminate\Database\Capsule\Manager as Capsule;
  use Aura\Router\RouterContainer;

  $capsule = new Capsule;
  $capsule->addConnection([
      'driver'    => 'mysql',
      'host'      => getenv('DB_HOST'),
      'database'  => getenv('DB_NAME'),
      'username'  => getenv('DB_USER'),
      'password'  => getenv('DB_PASS'),
      'charset'   => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'prefix'    => '',
  ]);
  // Make this Capsule instance available globally via static methods... (optional)
  $capsule->setAsGlobal();
  // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
  $capsule->bootEloquent();

  $request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$routerContainer = new RouterContainer();
$map = $routerContainer->getMap();

//$baseRoute = '/cursophp';

//$map->get('index', $baseRoute.'/', [
$map->get('index', '/', [
  'controller' => 'App\Controllers\IndexController',
  'action' => 'indexAction'
]);
//$map->get('addJob', $baseRoute.'/add/job', [
$map->get('addJob', '/add/job', [
  'controller' => 'App\Controllers\JobsController',
  'action' => 'getAddJobAction',
  'auth' => true
]);
//$map->post('saveJob', $baseRoute.'/add/job', [
$map->post('saveJob', 'add/job', [
  'controller' => 'App\Controllers\JobsController',
  'action' => 'getAddJobAction',
  'auth' => true
]);
$map->get('addProject', '/add/project', [
//$map->get('addProject', $baseRoute.'/add/project', [
  'controller' => 'App\Controllers\ProjectsController',
  'action' => 'getAddProjectAction',
  'auth' => true
]);
$map->post('saveProject', '/add/project', [
//$map->post('saveProject', $baseRoute.'/add/project', [
  'controller' => 'App\Controllers\ProjectsController',
  'action' => 'getAddProjectAction',
  'auth' => true
]);
$map->get('addUser', '/add/user', [
//$map->get('addUser', $baseRoute.'/add/user', [
  'controller' => 'App\Controllers\UserController',
  'action' => 'getAddUserAction',
  'auth' => true
]);
//$map->post('saveUser', $baseRoute.'/add/user', [
$map->post('saveUser', '/add/user', [
  'controller' => 'App\Controllers\UserController',
  'action' => 'getAddUserAction',
  'auth' => true
]);
//$map->get('loginForm', $baseRoute.'/login', [
$map->get('loginForm', '/login', [
  'controller' => 'App\Controllers\AuthController',
  'action' => 'getLogin'
]);
//$map->post('auth', $baseRoute.'/auth', [
$map->post('auth', '/auth', [
  'controller' => 'App\Controllers\AuthController',
  'action' => 'postLogin'
]);
//$map->get('admin', $baseRoute.'/admin', [
$map->get('admin', '/admin', [
  'controller' => 'App\Controllers\AdminController',
  'action' => 'getIndex',
  'auth' => true
]);
//$map->get('logout', $baseRoute.'/logout', [
$map->get('logout', '/logout', [
  'controller' => 'App\Controllers\AuthController',
  'action' => 'getLogout'
]);

$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);


function printElement($job) {
  // if($job->visible == false) {
  //   return;
  // }

  echo '<li class="work-position">';
  echo '<h5>' . $job->Title . '</h5>';
  echo '<p>' . $job->Description . '</p>';
  echo '<strong>Achievements:</strong>';
  echo '<ul>';
  echo '<li>Lorem ipsum dolor sit amet, 80% consectetuer adipiscing elit.</li>';
  echo '<li>Lorem ipsum dolor sit amet, 80% consectetuer adipiscing elit.</li>';
  echo '<li>Lorem ipsum dolor sit amet, 80% consectetuer adipiscing elit.</li>';
  echo '</ul>';
  echo '</li>';
}


if(!$route){
  echo 'No route';
}else{
  $handlerData = $route->handler;
  $controllerName = $handlerData['controller'];
  $actionName = $handlerData['action'];
  $needsAuth = $handlerData['auth'] ?? false;

  $sessionUserId = $_SESSION['userId'] ?? null;
  if($needsAuth && !$sessionUserId){
    echo 'Protected route';
    die;
  }

  $controller = new $controllerName;
  $response = $controller->$actionName($request); 

  foreach($response->getHeaders() as $name=>$values){
    foreach($values as $value){
      header(sprintf('%s: %s', $name, $value), false);
    }
  }
  http_response_code($response->getStatusCode());
  echo $response->getBody();
}  