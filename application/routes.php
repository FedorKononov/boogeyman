<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Simply tell Laravel the HTTP verbs and URIs it should respond to. It is a
| breeze to setup your application using Laravel's RESTful routing and it
| is perfectly suited for building large applications and simple APIs.
|
| Let's respond to a simple GET request to http://example.com/hello:
|
|		Route::get('hello', function()
|		{
|			return 'Hello World!';
|		});
|
| You can even respond to more than one URI:
|
|		Route::post(array('hello', 'world'), function()
|		{
|			return 'Hello World!';
|		});
|
| It's easy to allow URI wildcards using (:num) or (:any):
|
|		Route::put('hello/(:any)', function($name)
|		{
|			return "Welcome, $name.";
|		});
|
*/

/**
 * Это временный но удобный способ для определения роутинга.
 * Очевидный минус что идет доступ к файловой системе, 
 * для продакшен окружения нужно будет задавать статический массив.
 */

foreach (Controller::detect() as $controller)
{
	if ($controller != 'system' && $controller != 'moderator' && $controller != 'statistic')
		Route::controller($controller);
}

// системный контроллер регистрируем отдельно в конце чтобы не перекрыл контроллеры папки system
Route::any('system/(:any?)', array('defaults' => 'index', 'uses' => 'system@(:1)'));

// аналогично системному контроллеру регистрируем контроллер модератор
Route::any('moderator/(:any?)', array('defaults' => 'index', 'uses' => 'moderator@(:1)'));

// Синоним для авторизации
Route::any('login', 'account@login');

// Синоним для деавторизации
Route::any('logout', 'account@logout');

// Синоним для регистрации
Route::any('register', 'account@register');

/*
|--------------------------------------------------------------------------
| Application 404 & 500 Error Handlers
|--------------------------------------------------------------------------
|
| To centralize and simplify 404 handling, Laravel uses an awesome event
| system to retrieve the response. Feel free to modify this function to
| your tastes and the needs of your application.
|
| Similarly, we use an event to handle the display of 500 level errors
| within the application. These errors are fired when there is an
| uncaught exception thrown in the application.
|
*/

Event::listen('404', function()
{
	return Response::error('404');
});

Event::listen('500', function()
{
	return Response::error('500');
});

Event::listen('403', function()
{
	return Response::error('403');
});

Event::listen('system.status_shift', function($model, $from, $to)
{
	DB::query('INSERT INTO '. $from::$table .'_moves (`model`, `from`, `to`, `account`, `time`) VALUES (?, ?, ?, ?, ?)', array(
		$model->id,
		$from->id,
		$to->id,
		Auth::user()->id,
		time(),
	));
});

/*
|--------------------------------------------------------------------------
| Route Filters
|--------------------------------------------------------------------------
|
| Filters provide a convenient method for attaching functionality to your
| routes. The built-in before and after filters are called before and
| after every request to your application, and you may even create
| other filters that can be attached to individual routes.
|
| Let's walk through an example...
|
| First, define a filter:
|
|		Route::filter('filter', function()
|		{
|			return 'Filtered!';
|		});
|
| Next, attach the filter to a route:
|
|		Router::register('GET /', array('before' => 'filter', function()
|		{
|			return 'Hello World!';
|		}));
|
*/

Route::filter('before', function()
{
	// Do stuff before every request to your application...
});

Route::filter('after', function($response)
{
	// Do stuff after every request to your application...
});

Route::filter('csrf', function()
{
	if (Request::forged()) return Response::error('500');
});

Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::to('account/login');
});

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('account');
});

Route::filter('single_cache_flush', function($response, $model)
{
	if(is_string($model)) return $model::cflush_all();
});

Route::filter('cache_header_flush', function($response, $key)
{
	if(is_string($key)) return Cache::forget($key);
});

Route::filter('access', function()
{
	if(!Auth::route(Request::$route)) return Response::error('403');
});