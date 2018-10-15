<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Add Configuration
|--------------------------------------------------------------------------
|
| Add configuration to the application
|
*/
$app->configure('cookies');
$app->configure('resources');
$app->configure('services');
$app->configure('mail');

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    \App\Http\JsonApi::class,
    \App\Http\JsonApi::class
);

$app->singleton(\Neomerx\JsonApi\Contracts\Encoder\EncoderInterface::class, function ($app) {
    $request = $app->request;

    $prettyPrintQueryString = $request->query('pretty');
    ($prettyPrintQueryString === 'false') ? $prettyPrintInt = 0 : $prettyPrintInt = JSON_PRETTY_PRINT;

    $schemas = [
        \App\Models\Category::class => \App\Schemas\CategorySchema::class,
        \App\Models\Image::class    => \App\Schemas\ImageSchema::class,
        \App\Models\User::class     => \App\Schemas\UserSchema::class,
        \App\Models\Contact::class  => \App\Schemas\ContactSchema::class,
        \App\Models\WebPage::class  => \App\Schemas\WebPageSchema::class,
        \App\Models\Tag::class      => \App\Schemas\TagSchema::class
    ];

    $encoder = \Neomerx\JsonApi\Encoder\Encoder::instance(
        $schemas,
        new \Neomerx\JsonApi\Encoder\EncoderOptions(JSON_UNESCAPED_SLASHES | $prettyPrintInt, env('API_URI') . '/v1'))
        ->withJsonApiVersion();

    return $encoder;
});

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    \Nord\Lumen\Cors\CorsMiddleware::class
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(\App\Providers\AppServiceProvider::class);
$app->register(\App\Providers\AuthServiceProvider::class);
$app->register(\App\Providers\ValidationRulesServiceProvider::class);
$app->register(\App\Providers\MongoServiceProvider::class);
$app->register(\Nord\Lumen\Cors\CorsServiceProvider::class);
$app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
$app->register(\Illuminate\Redis\RedisServiceProvider::class);
$app->register(\Illuminate\Mail\MailServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group(['namespace' => 'App\Http\Controllers'], function ($router) {
    require __DIR__ . '/../routes/api.php';
});

return $app;
