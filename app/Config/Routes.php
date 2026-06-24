<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =============================================
// PUBLIC & DEFAULT ROUTES
// =============================================
// Langsung arahkan halaman depan (root) ke Login agar aman
$routes->get('/', 'Auth::login'); 
$routes->get('plans', 'Home::plans');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// =============================================
// AUTHENTICATION ROUTES
// =============================================
$routes->group('auth', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::doLogin');
    $routes->get('register', 'Auth::register');
    $routes->post('register', 'Auth::doRegister');
    $routes->get('logout', 'Auth::logout');
    $routes->get('forgot-password', 'Auth::forgotPassword');
    $routes->post('forgot-password', 'Auth::doForgotPassword');
    $routes->get('reset-password/(:any)', 'Auth::resetPassword/$1');
    $routes->post('reset-password', 'Auth::doResetPassword');
});

// =============================================
// USER DASHBOARD ROUTES (Protected)
// =============================================
$routes->group('dashboard', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function($routes) {
    $routes->get('/', 'Dashboard::index');
    
    // Server Management
    $routes->get('servers', 'ServerController::index');
    $routes->get('servers/create', 'ServerController::create');
    $routes->post('servers/store', 'ServerController::store');
    $routes->get('servers/(:num)', 'ServerController::show/$1');
    $routes->post('servers/(:num)/start', 'ServerController::start/$1');
    $routes->post('servers/(:num)/stop', 'ServerController::stop/$1');
    $routes->post('servers/(:num)/restart', 'ServerController::restart/$1');
    $routes->delete('servers/(:num)', 'ServerController::delete/$1');
    
    // Monitoring & Logs
    $routes->get('servers/(:num)/monitoring', 'ServerController::monitoring/$1');
    $routes->get('servers/(:num)/logs', 'ServerController::logs/$1');
    
    // Billing & Topup
    $routes->get('billing', 'BillingController::index');
    $routes->get('billing/topup', 'BillingController::topup');
    $routes->post('billing/topup', 'BillingController::doTopup');
    $routes->get('billing/history', 'BillingController::history');
    $routes->get('billing/invoice/(:num)', 'BillingController::invoice/$1');
    $routes->get('billing/export-csv', 'BillingController::exportCSV');
    
    // Profile & Security
    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile/update', 'ProfileController::update');
    $routes->post('profile/change-password', 'ProfileController::changePassword');
});

// =============================================
// ADMIN PANEL ROUTES (Protected + Admin Filter)
// =============================================
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'admin'], function($routes) {
    $routes->get('/', 'AdminDashboard::index');
    
    // User Management
    $routes->get('users', 'UserManagement::index');
    $routes->get('users/create', 'UserManagement::create');
    $routes->post('users/store', 'UserManagement::store');
    $routes->get('users/(:num)/edit', 'UserManagement::edit/$1');
    $routes->post('users/(:num)/update', 'UserManagement::update/$1');
    $routes->delete('users/(:num)', 'UserManagement::delete/$1'); 
    $routes->post('users/(:num)/suspend', 'UserManagement::suspend/$1');
    $routes->post('users/(:num)/activate', 'UserManagement::activate/$1');
    $routes->post('users/(:num)/adjust-balance', 'UserManagement::adjustBalance/$1');
    
    // Server Plans Management
    $routes->get('plans', 'PlanManagement::index');
    $routes->get('plans/create', 'PlanManagement::create');
    $routes->post('plans/store', 'PlanManagement::store');
    $routes->get('plans/(:num)/edit', 'PlanManagement::edit/$1');
    $routes->post('plans/(:num)/update', 'PlanManagement::update/$1');
    $routes->delete('plans/(:num)', 'PlanManagement::delete/$1');
    
    // Infrastructure & Billing (Unified)
    $routes->get('billing', 'BillingManagement::index');
    $routes->get('billing/transactions', 'BillingManagement::transactions');
    $routes->post('billing/approve/(:num)', 'BillingManagement::approve/$1');
    $routes->post('billing/reject/(:num)', 'BillingManagement::reject/$1');
    
    // Reports & System Logs
    $routes->get('reports', 'Reports::index');
    $routes->get('logs', 'ActivityLogs::index'); // Pastikan Controller ActivityLogs ada

    $routes->get('servers', 'ServerManagement::index');
    $routes->get('servers/(:num)', 'ServerManagement::show/$1');

    
});


$routes->group('admin/domains', ['namespace' => 'App\Controllers\Admin', 'filter' => 'adminAuth'], function($routes) {
    // Manajemen Domain User
    $routes->get('/', 'DomainController::index');
    $routes->post('update/(:num)', 'DomainController::updateDomain/$1');
    $routes->post('suspend/(:num)', 'DomainController::suspend/$1');
    $routes->post('activate/(:num)', 'DomainController::activate/$1');

    // Manajemen Harga TLD
    $routes->get('pricing', 'DomainController::pricing');
    $routes->post('pricing/store', 'DomainController::storeTld');
    $routes->post('pricing/update/(:num)', 'DomainController::updateTld/$1');
    $routes->post('pricing/delete/(:num)', 'DomainController::deleteTld/$1');
});


// =============================================
// API ROUTES (For External Integration & AJAX)
// =============================================
$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'cors'], function($routes) {
    $routes->get('plans', 'ApiController::getPlans');
    $routes->get('ping', 'ApiController::ping');
    
    $routes->group('v1', ['filter' => 'apiauth'], function($routes) {
        $routes->get('servers', 'ApiController::listServers');
        $routes->post('servers', 'ApiController::createServer');
        $routes->get('servers/(:num)', 'ApiController::getServer/$1');
        $routes->post('servers/(:num)/start', 'ApiController::startServer/$1');
        $routes->post('servers/(:num)/stop', 'ApiController::stopServer/$1');
        $routes->delete('servers/(:num)', 'ApiController::deleteServer/$1');
        $routes->get('servers/(:num)/metrics', 'ApiController::getMetrics/$1');
        $routes->get('billing/balance', 'ApiController::getBalance');
        $routes->get('billing/transactions', 'ApiController::getTransactions');
    });
});

// =============================================
// ERROR HANDLING
// =============================================
$routes->set404Override(function($message = null) {
    $data = [
        'message' => $message ?? 'The page you are looking for was not found.'
    ];
    return view('errors/html/error_404', $data);
});

$routes->group('dashboard/domains', ['namespace' => 'App\Controllers\Dashboard', 'filter' => 'auth'], static function ($routes) {
    
    $routes->get('/', 'DomainController::index');
    $routes->get('search', 'DomainController::search');
    $routes->post('purchase', 'DomainController::purchase');
    $routes->get('manage/(:num)', 'DomainController::manage/$1');
    $routes->post('update-settings/(:num)', 'DomainController::updateSettings/$1');
    $routes->post('manage/(:num)/dns', 'DomainController::storeDnsRecord/$1');
    $routes->post('manage/(:num)/dns/(:num)/delete', 'DomainController::deleteDnsRecord/$1/$2');
    $routes->post('manage/(:num)/child-ns', 'DomainController::storeChildNameserver/$1');
    $routes->post('manage/(:num)/child-ns/(:num)/delete', 'DomainController::deleteChildNameserver/$1/$2');
    $routes->post('manage/(:num)/epp/reveal', 'DomainController::revealEpp/$1');
    $routes->post('manage/(:num)/epp/regenerate', 'DomainController::regenerateEpp/$1');
    
});
