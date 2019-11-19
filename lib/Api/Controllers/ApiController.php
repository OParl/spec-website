<?php

namespace OParl\Website\Api\Controllers;

use OpenApi\Annotations as OA;
use function OpenApi\scan;

/**
 * @OA\OpenApi(
 *     openapi="3.0.0",
 *     @OA\Info(
 *         title="OParl Developer Platform API",
 *         description="Meta information concerning the OParl ecosystem",
 *         version="0",
 *         @OA\License(
 *             name="CC-4.0-BY",
 *             url="https://creativecommons.org/licenses/by/4.0/"
 *         )
 *     )
 * )
 */
class ApiController
{
    /**
     * Return the dynamically updated swagger.json for the meta endpoints.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function swaggerJson()
    {
        define('SWAGGER_API_HOST', 'dev.' . config('app.url'));
        $swagger = scan([base_path('lib/Api/Controllers'), app_path('Model')]);

        return response($swagger, 200, [
            'Content-Type'                => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Index page for the api.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return view('api.index');
    }
}
