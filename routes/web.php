<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Support\Str;

$router->get("/", function () use ($router) {
    return view('app');
});

$router->post("confirm", "ConfirmationController@confirm");
$router->post("reconcile", "ReconciliationController");

foreach (["payments", "bookings", "bus-bookings", "flight-bookings", "car-bookings", "train-bookings"] as $resource) {
    $router->group(["prefix" => $resource], function () use ($router, $resource) {
        $resource = str_replace("-", "", $resource);
        $base = ucfirst(Str::singular($resource));
        $router->get("/", "{$base}Controller@index");
        $router->post("/", "{$base}Controller@store");
        $router->get("{id}", "{$base}Controller@show");
        $router->put("{id}", "{$base}Controller@update");
        $router->delete("{id}", "{$base}Controller@delete");
    });
}
