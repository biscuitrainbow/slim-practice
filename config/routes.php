<?php

use Slim\Routing\RouteCollectorProxy;
use App\Controller\ProductController;
use App\Controller\LoginController;
use App\Middleware\AuthorizationMiddleware;

/**
 * We use class and method instead of closure function.
 * The method setName is set name to route
 * that we use with url_for function in template.
 */

$productGroup = $app->group('/product', function (RouteCollectorProxy $group) {
    $adminGroup = $group->group('', function (RouteCollectorProxy $group) {
        $group->get(
            '/add',
            ProductController::class . ':addFormAction'
        )->setName('product-add-form');

        $group->post(
            '/add',
            ProductController::class . ':addProduct'
        )->setName('product-add');

        $group->get(
            '/{id}/update',
            ProductController::class . ':updateFormAction'
        )->setName('product-update-form');
    });

    $adminGroup->add(new AuthorizationMiddleware(
        $group->getResponseFactory(),
        ['ADMIN'],
    ));

    $group->get(
        '',
        ProductController::class . ':listAction'
    )->setName('product-list');

    $group->get(
        '/{id}',
        ProductController::class . ':viewAction'
    )->setName('product-view');
});

$productGroup->add(new AuthorizationMiddleware(
    $app->getResponseFactory(),
    ['USER', 'ADMIN']
));

$app->get(
    '/login-form',
    LoginController::class . ':loginFormAction'
)->setName('login-form');

$app->post(
    '/login',
    LoginController::class . ':loginAction'
)->setName('login');

$app->get(
    '/logout',
    LoginController::class . ':logoutAction'
)->setName('logout');
