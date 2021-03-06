<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use App\Services\OParlVersions;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

$specificationVersions = sprintf(
    '(%s)',
    implode(
        '|',
        array_keys((new OParlVersions())->getModule('specification'))
    )
);

/**
 * Route group for dev.oparl.org.
 *
 * This route group contains all the endpoints necessary to navigate through
 * dev.oparl.org except the api/ section which is loaded in via the
 * OParl\Server\ServerServiceProvider.
 */
Route::group(
    [
        'domain'     => 'dev.'.config('app.url'),
        'middleware' => 'web',
    ],
    function (Router $router) use ($specificationVersions) {
        $router->get('/favicon.ico')
            ->uses('MiscController@favicon');

        $router->get('/')
            ->name('developers.index')
            ->uses('DevelopersController@index');

        $router->get('/contact')
            ->name('contact.index')
            ->uses('DevelopersController@contact');

        // Dummy file controller for API demo
        $router->pattern('filename', '[a-z0-9]{3,12}');
        $router->get('/demo/{filename}.pdf')
            ->name('dummyfile.show')
            ->uses('DummyFileController@show');

        $router->get('/demo/f/{filename}.pdf')
            ->name('dummyfile.serve')
            ->uses('DummyFileController@serve');

        // Downloads
        $router->get('/downloads/')
            ->name('downloads.index')
            ->uses('DownloadsController@index');

        $router->post('/downloads')
            ->name('downloads.request')
            ->uses('DownloadsController@downloadRequest');


        $router->get('/downloads/spezifikation-{version}.{format}')
            ->name('downloads.specification')
            ->uses('DownloadsController@specification')
            ->where('version', $specificationVersions)
            ->where('format', '(pdf|txt|odt|docx|html|epub|zip|tar.gz|tar.bz2)')
            ->middleware('track');

        // Endpoint listing

        $router->get('/endpunkt')
            ->uses('RedirectController@fuzzy');

        $router->get('/endpunkte')
            ->name('endpoints.index')
            ->uses('EndpointsController@index');

        // Specification
        $router->get('/spezifikation/{version?}')
            ->uses('SpecificationController@index')
            ->name('specification.index');

        $router->get('/spezifikation-{version}.md')
            ->uses('SpecificationController@raw')
            ->name('specification.raw')
            ->where('version', $specificationVersions);

        $router->get('/spezifikation/{version}/images/')
            ->uses('SpecificationController@imageIndex')
            ->name('specification.images');

        $router->get('/spezifikation/{version}/images/{image}.png')
            ->name('specification.image')
            ->where('version', $specificationVersions)
            ->where('image', '[a-zA-Z0-9-._]+')
            ->uses('SpecificationController@image');

        // Validator

        $router->get('/validator')
            ->name('validator.index')
            ->uses('ValidatorController@validationForm');

        $router->post('/validator')
            ->name('validator.schedule')
            ->uses('ValidatorController@scheduleValidation');

        $router->get('/validator/in-bearbeitung')
            ->name('validator.schedule.success')
            ->uses('ValidatorController@validationScheduleSuccess');

        $router->get('/validator/{endpoint}')
            ->name('validator.result')
            ->uses('ValidatorController@result');

        // GitHub Hooks

        $router->get('/_/gh/')
            ->name('hooks.gh.index')
            ->uses('Hooks\GitHubHooksController@index');

        $router->post('/_/gh/')
            ->name('hooks.gh.index.post')
            ->uses('Hooks\GitHubHooksController@index');

        $router->get('/_/gh/push/[a-zA-Z.]+')
            ->name('hooks.gh.push.get')
            ->uses('Hooks\GitHubHooksController@index');

        $router->post('/_/gh/push/{repository}')
            ->name('hooks.gh.push')
            ->where('repository', '[a-z-]+')
            ->uses('Hooks\GitHubHooksController@push');

        // Locale switching

        $router->get('/_/language/{language}')->name('locale.set')->uses('MiscController@setLocale')->where(
            'language',
            '(de|en)'
        );
    }
);

/*
 * Route group for spec.oparl.org
 *
 * This route group provides an easy to remember redirect to the
 * latest specification version as spec.oparl.org
 *
 * Additionally, short links to downloads of the stable and
 * the latest unstable specification versions are provided.
 */
Route::group(
    ['domain' => 'spec.'.config('app.url')],
    function (Router $router) {
        $router->any('/')
            ->uses('SpecificationController@redirectToIndex');

        $router->pattern(
            'version',
            sprintf('(%s)', implode('|', array_keys(config('oparl.versions.specification'))))
        );

        $router->get('/{version}')
            ->uses('SpecificationController@redirectToVersion');
        $router->get('/{version}.{format}')
            ->uses('DownloadsController@specification');

        $router->get('/latest.{format}')
            ->uses('DownloadsController@latestSpecification');
    }
);

/*
 * Route group for schema.oparl.org
 *
 * This route group defines access to the versioned JSONSchema of the OParl Specification
 * which is accessible at schema.oparl.org/{version}/{entity}.json
 *
 * Direct access to schema.oparl.org is redirected to dev.oparl.org
 */
Route::group(
    [
        'domain'     => 'schema.'.config('app.url'),
        'middleware' => ['throttle:60,1', 'track'],
    ],
    function (Router $router) use ($specificationVersions) {
        $router->pattern('version', $specificationVersions);

        $router->any('/')
            ->uses('SchemaController@index');

        $router->get('/{version}')
            ->name('schema.list')
            ->uses('SchemaController@listSchemaVersion');

        $router->get('/{version}/{entity}')
            ->name('schema.get')
            ->where('entity', '[A-Za-z]+')
            ->uses('SchemaController@getSchema');
    }
);

unset($specificationVersions);

/**
 * Route group for the Metadata API
 */
Route::group(
    [
        'namespace'  => 'API',
        'as'         => 'api.',
        'domain'     => 'dev.'.config('app.url'),
        'prefix'     => '/api/',
        'middleware' => ['api'],
    ], function (Router $router) {
    $router->get('/')
        ->name('index')
        ->uses('ApiController@index');

    $router->get('/openapi.json')
        ->uses('ApiController@openApiJson');

    $router->get('/endpoints')
        ->name('endpoints.index')
        ->uses('EndpointApiController@index');

    $router->get('/endpoints/{id}')
        ->name('endpoints.get')
        ->where('id', '\d+')
        ->uses('EndpointApiController@endpoint');

    $router->get('/endpoints/statistics')
        ->uses('EndpointApiController@statistics');

    $router->get('/endpoints/areas')
        ->uses('EndpointApiController@areas');
});
