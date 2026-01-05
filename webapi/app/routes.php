<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!!!!');
        return $response;
    });

    require_once __DIR__ . '/../src/Routes/webshop/HomeDetails.php';
    require_once __DIR__ . '/../src/Routes/webshop/Blog.php';
	require_once __DIR__ . '/../src/Routes/Commonfeature.php';
	require_once __DIR__ . '/../src/Routes/fbcuser/Fbcusers.php';
    require_once __DIR__ . '/../src/Routes/webshop/Gettheme.php';
    require_once __DIR__ . '/../src/Routes/webshop/Shop_status.php';
    require_once __DIR__ . '/../src/Routes/webshop/Register.php';
    require_once __DIR__ . '/../src/Routes/webshop/Login.php';
    require_once __DIR__ . '/../src/Routes/webshop/Product.php';
    require_once __DIR__ . '/../src/Routes/webshop/CmsFiles.php';
    require_once __DIR__ . '/../src/Routes/webshop/ContactUs.php';
    require_once __DIR__ . '/../src/Routes/webshop/CustomVariable.php';
    require_once __DIR__ . '/../src/Routes/webshop/Customer.php';
    require_once __DIR__ . '/../src/Routes/webshop/ProductReview.php';
    require_once __DIR__ . '/../src/Routes/webshop/Common.php';
    require_once __DIR__ . '/../src/Routes/webshop/Search.php';
    require_once __DIR__ . '/../src/Routes/webshop/Cart.php';
    require_once __DIR__ . '/../src/Routes/webshop/Wishlist.php';
	require_once __DIR__ . '/../src/Routes/webshop/Checkout.php';
	require_once __DIR__ . '/../src/Routes/webshop/Orders.php';
    require_once __DIR__ . '/../src/Routes/webshop/PreLaunchProduct.php';
    require_once __DIR__ . '/../src/Routes/webshop/FeaturedProduct.php';
    require_once __DIR__ . '/../src/Routes/webshop/SpecialFeatures.php';
	require_once __DIR__ . '/../src/Routes/webshop/Currency.php';
	require_once __DIR__ . '/../src/Routes/wholesale_platform/WholeSale.php';

    //Testimonials
    require_once __DIR__ . '/../src/Routes/webshop/Testimonials.php';

};
