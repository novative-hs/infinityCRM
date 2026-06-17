<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'AuthController::index');
$routes->get('/login', 'AuthController::index');
$routes->post('/auth/login', 'AuthController::login');
$routes->get('/dashboard', 'AuthController::dashboard');
$routes->get('/auth/logout', 'AuthController::logout');

// API route for Postman (no auth middleware)
$routes->post('/api/admin/create', 'UserController::createAdmin');

// Dashboard user management
$routes->get('/users',        'UserController::index');
$routes->get('/users/create', 'UserController::create');
$routes->post('/users/store', 'UserController::store');
$routes->get('/lablist', 'UserController::labList');
$routes->get('/registerform', 'UserController::registerForm');

// Booking
$routes->get('/booking/new',        'BookingController::index');
$routes->post('booking/add', 'BookingController::add_booking');

$routes->get('/dbadmin/dashboard', 'AdminController::dashboard');

// Lab Dashboard Routes
$routes->get('/labDashboard/dashboard', 'LabController::dashboard');
$routes->get('/labDashboard/sample_collected/(:num)', 'LabController::sampleCollected/$1');
$routes->get('/labDashboard/sample_collected', 'LabController::sampleCollected');