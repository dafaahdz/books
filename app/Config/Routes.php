<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/login', 'AuthController::login');
$routes->post('/login/process', 'AuthController::loginProcess');
$routes->get('/login', 'AuthController::logout');

$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/dashboard', function () {
        return redirect()->to('/');
    });
    $routes->get('/', 'BooksController::index');
    $routes->post('/books/datatables', 'BooksController::datatables');
    $routes->post('books/store', 'BooksController::store');
    $routes->get('genres/list', 'GenreController::list');
    $routes->post('genres/store', 'GenreController::store');
    $routes->get('genres/search', 'GenreController::search');
    $routes->get('books/show/(:num)', 'BooksController::show/$1');
    $routes->post('books/update', 'BooksController::update');
    $routes->post('books/delete', 'BooksController::delete');


    $routes->get('/genres', 'GenreController::index');
    $routes->post('/genres/datatables', 'GenreController::datatables');
    $routes->get('/genres/show/(:num)', 'GenreController::show/$1');
    $routes->post('/genres/update', 'GenreController::update');
    $routes->post('/genres/delete', 'GenreController::delete');
    $routes->get('/genres/books/(:num)', 'GenreController::books/$1');

    $routes->get('files', 'FileController::index');
    $routes->post('files/list', 'FileController::list');
    $routes->get('files/show/(:num)', 'FileController::show/$1');
    $routes->post('files/update', 'FileController::update');
    $routes->post('files/delete', 'FileController::delete');
    $routes->get('files/download/(:num)', 'FileController::download/$1');
    $routes->post('files/chunk-upload', 'FileController::chunkUpload');
});

$routes->get('testpdf', 'TestPdf::index');
$routes->get('books/export-pdf', 'BooksController::exportPdf');
$routes->get('books/export-csv', 'BooksController::exportCsv');
$routes->post('books/export-init', 'BooksController::exportInit');
$routes->post('books/export-chunk', 'BooksController::exportChunk');
$routes->get('books/export-download', 'BooksController::exportDownload');

$routes->get('books/chunks', 'BooksController::testChunks');
$routes->match(['get', 'post'], 'books/import-csv', 'BooksController::importCsv');

$routes->post('books/export-reset', 'BooksController::exportReset');
$routes->post('books/import-init', 'BooksController::importInit');
$routes->post('books/import-chunk', 'BooksController::importChunk');
$routes->post('books/import-reset', 'BooksController::importReset');
$routes->get('books/download-template', 'BooksController::downloadTemplate');
