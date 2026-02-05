<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/books', 'BooksController::index');
$routes->post('/books/datatables', 'BooksController::datatables');
$routes->post('books/store', 'BooksController::store');
$routes->get('genres/list', 'GenreController::list');
$routes->post('genres/store', 'GenreController::store');
$routes->get('genres/search', 'GenreController::search');
$routes->get('books/show/(:num)', 'BooksController::show/$1');
$routes->post('books/update', 'BooksController::update');
$routes->post('books/delete', 'BooksController::delete');

$routes->get('testpdf', 'TestPdf::index');
$routes->get('books/export-pdf', 'BooksController::exportPdf');
$routes->get('books/export-csv', 'BooksController::exportCsv');
$routes->get('books/export-excel', 'BooksController::exportExcel');
$routes->post('books/export-init', 'BooksController::exportInit');
$routes->post('books/export-chunk', 'BooksController::exportChunk');
$routes->get('books/export-download', 'BooksController::exportDownload');

$routes->get('books/chunks', 'BooksController::testChunks');
$routes->match(['get', 'post'], 'books/import-csv', 'BooksController::importCsv');

$routes->post('books/export-reset', 'BooksController::exportReset');    
