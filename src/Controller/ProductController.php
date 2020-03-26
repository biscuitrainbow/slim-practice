<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class ProductController
{
    // This is just a mock up data, the real data come from database
    private static $PRODUCT_DATA = [
        ['id' => 101, 'name' => 'Product A', 'price' => 1234.56, 'category' => 'Phone', 'quantity' => 100],
        ['id' => 250, 'name' => 'Product B', 'price' => 2345.67, 'category' => 'Phone', 'quantity' => 100],
        ['id' => 400, 'name' => 'Product C', 'price' => 3456.78, 'category' => 'Tablet', 'quantity' => 300],
    ];

    public static function  getAll($link): array
    {
        $result = mysqli_query(
            $link,
            <<<EOT
            SELECT product.*,category.name as category_name FROM product
            LEFT JOIN category on product.id_category = category.id
            ORDER BY product.name
        EOT
        );

        $items = [];
        while ($item = mysqli_fetch_assoc($result)) {
            $items[] = $item;
        }

        return $items;
    }

    public static function  getItem($link, $id): ?array
    {
        $result = mysqli_query(
            $link,
            <<<EOT
            SELECT product.*,category.name as category_name FROM product
            LEFT JOIN category on product.id_category = category.id
            WHERE product.id = '{$id}'
        EOT
        );

        return mysqli_fetch_assoc($result);
    }

    public function listAction(
        Request $request,
        Response $response,
        $args
    ): Response {
        $view = Twig::fromRequest($request);
        $link = $request->getAttribute('mysqli')->connect();

        return $view->render($response, 'product-list.html', [
            'data' => self::getAll($link),
        ]);
    }

    public function viewAction(
        Request $request,
        Response $response,
        $args
    ): Response {
        // $args store value from placeholder that we want {id}
        $id = $args['id'];
        $link = $request->getAttribute('mysqli')->connect();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'product-view.html', [
            'data' => self::getItem($link, $id),
        ]);
    }

    public function addFormAction(
        Request $request,
        Response $response,
        $args
    ): Response {
        $view = Twig::fromRequest($request);
        $link = $request->getAttribute('mysqli')->connect();

        // Use product-view.html template
        return $view->render($response, 'product-add-form.html', [
            'categoryList' => CategoryController::getAll($link),
        ]);
    }

    public function addProduct(
        Request $request,
        Response $response,
        $args
    ): Response {
        $post = $request->getParsedBody();
        $link = $request->getAttribute('mysqli')->connect();

        mysqli_query(
            $link,
            <<<EOT
                INSERT INTO product(id_category,name,price,qty) 
                VALUES(
                    '{$post['id_category']}',
                    '{$post['name']}',
                    '{$post['price']}',
                    '{$post['qty']}'
                )
            EOT
        );

        $request->getAttribute('session')
            ->getSegment(self::class)
            ->setFlash('message', "Adding is sucessful.");

        $routeContext = RouteContext::fromRequest($request);
        return $response->withHeader(
            'Location',
            $routeContext->getRouteParser()->urlFor('product-list')
        )->withStatus(302);
    }


    public function updateFormAction(
        Request $request,
        Response $response,
        $args
    ): Response {


        $view = Twig::fromRequest($request);
        // Use product-view.html template
        return $view->render($response, 'product-update-form.html');
    }
}
