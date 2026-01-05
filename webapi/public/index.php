<?php
declare(strict_types=1);

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use App\Application\ResponseEmitter\ResponseEmitter;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Model/Config/Config.php';


// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);

//Required Files

require_once '../src/Model/DbCommonFeature.php';
require_once '../src/Model/DbBlogFeature.php';
require_once '../src/Model/DbFbcuser.php';
require_once '../src/Model/DbHomeFeature.php';
require_once '../src/Model/DbProductFeature.php';
require_once '../src/Model/DbCMSFeature.php';
require_once '../src/Model/DbEmailFeature.php';
require_once '../src/Model/DbGlobalFeature.php';
require_once '../src/Model/DbSearchFeature.php';
require_once '../src/Model/DbProductReviewFeature.php';
require_once '../src/Model/DbCart.php';
require_once '../src/Model/DbWishlistFeature.php';
require_once '../src/Model/DbCheckout.php';
require_once '../src/Model/DbOrders.php';
require_once '../src/Model/DbProductPreLauch.php';
require_once '../src/Model/DbSpecialFeature.php';
require_once '../src/Model/DbWholeSale.php';

//Testimonials
require_once '../src/Model/DbTestimonialsFeature.php';

$app = AppFactory::create();
$app->setBasePath("/webapi");


$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);
/** @var bool $displayErrorDetails */
$displayErrorDetails = $container->get('settings')['displayErrorDetails'];

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();


// Add Error Middleware
//$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
