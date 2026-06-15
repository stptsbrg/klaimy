<?php
/**
 * Routes de l'application Klaimy
 */

use App\Core\App;

$router = App::getInstance()->router();

// ============================================================
// Public Routes
// ============================================================
$router->get('/', 'HomeController@index');
$router->get('/pay/{uuid}', 'PaymentController@paymentPage');
$router->post('/api/payment/init', 'PaymentController@initCinetPay');
$router->post('/webhooks/cinetpay', 'PaymentController@cinetPayWebhook');

// ============================================================
// Auth Routes
// ============================================================
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->get('/verify-2fa', 'AuthController@showVerify2FA');
$router->post('/verify-2fa', 'AuthController@verify2FA');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');

// ============================================================
// Authenticated Routes
// ============================================================
$router->get('/dashboard', 'DashboardController@index', ['AuthMiddleware']);

// Clients
$router->get('/clients', 'ClientController@index', ['AuthMiddleware']);
$router->get('/clients/create', 'ClientController@create', ['AuthMiddleware']);
$router->post('/clients', 'ClientController@store', ['AuthMiddleware']);
$router->get('/clients/{id}', 'ClientController@show', ['AuthMiddleware']);
$router->get('/clients/{id}/edit', 'ClientController@edit', ['AuthMiddleware']);
$router->post('/clients/{id}', 'ClientController@update', ['AuthMiddleware']);
$router->post('/clients/{id}/delete', 'ClientController@delete', ['AuthMiddleware']);
$router->get('/api/clients/search', 'ClientController@search', ['AuthMiddleware']);

// Products
$router->get('/products', 'ProductController@index', ['AuthMiddleware']);
$router->get('/products/create', 'ProductController@create', ['AuthMiddleware']);
$router->post('/products', 'ProductController@store', ['AuthMiddleware']);
$router->get('/products/{id}/edit', 'ProductController@edit', ['AuthMiddleware']);
$router->post('/products/{id}', 'ProductController@update', ['AuthMiddleware']);
$router->post('/products/{id}/delete', 'ProductController@delete', ['AuthMiddleware']);
$router->get('/api/products/search', 'ProductController@search', ['AuthMiddleware']);

// Quotes
$router->get('/quotes', 'QuoteController@index', ['AuthMiddleware']);
$router->get('/quotes/create', 'QuoteController@create', ['AuthMiddleware']);
$router->post('/quotes', 'QuoteController@store', ['AuthMiddleware']);
$router->get('/quotes/{id}', 'QuoteController@show', ['AuthMiddleware']);
$router->post('/quotes/{id}/send', 'QuoteController@send', ['AuthMiddleware']);
$router->post('/quotes/{id}/convert', 'QuoteController@convertToInvoice', ['AuthMiddleware']);

// Invoices
$router->get('/invoices', 'InvoiceController@index', ['AuthMiddleware']);
$router->get('/invoices/create', 'InvoiceController@create', ['AuthMiddleware']);
$router->post('/invoices', 'InvoiceController@store', ['AuthMiddleware']);
$router->get('/invoices/{id}', 'InvoiceController@show', ['AuthMiddleware']);
$router->get('/invoices/{id}/edit', 'InvoiceController@edit', ['AuthMiddleware']);
$router->post('/invoices/{id}', 'InvoiceController@update', ['AuthMiddleware']);
$router->post('/invoices/{id}/send', 'InvoiceController@send', ['AuthMiddleware']);
$router->post('/invoices/{id}/cancel', 'InvoiceController@cancel', ['AuthMiddleware']);
$router->get('/invoices/{id}/pdf', 'InvoiceController@pdf', ['AuthMiddleware']);

// Payments
$router->get('/payments', 'PaymentController@index', ['AuthMiddleware']);
$router->post('/payments/{invoiceId}', 'PaymentController@record', ['AuthMiddleware']);

// Messages
$router->get('/messages', 'MessageController@index', ['AuthMiddleware']);
$router->get('/messages/{id}', 'MessageController@show', ['AuthMiddleware']);
$router->post('/messages/{id}/send', 'MessageController@send', ['AuthMiddleware']);
$router->post('/messages/create', 'MessageController@create', ['AuthMiddleware']);
$router->get('/api/messages/{id}', 'MessageController@getMessages', ['AuthMiddleware']);
$router->post('/api/messages/{id}', 'MessageController@sendAjax', ['AuthMiddleware']);

// Notifications
$router->get('/notifications', 'NotificationController@index', ['AuthMiddleware']);
$router->post('/notifications/{id}/read', 'NotificationController@markRead', ['AuthMiddleware']);
$router->post('/notifications/read-all', 'NotificationController@markAllRead', ['AuthMiddleware']);
$router->get('/api/notifications/count', 'NotificationController@getUnreadCount', ['AuthMiddleware']);

// Settings
$router->get('/settings/company', 'SettingsController@company', ['AuthMiddleware']);
$router->post('/settings/company', 'SettingsController@updateCompany', ['AuthMiddleware']);
$router->get('/settings/users', 'SettingsController@users', ['AuthMiddleware']);
$router->post('/settings/users', 'SettingsController@createUser', ['AuthMiddleware']);
$router->get('/settings/profile', 'SettingsController@profile', ['AuthMiddleware']);
$router->post('/settings/profile', 'SettingsController@updateProfile', ['AuthMiddleware']);
$router->get('/settings/subscription', 'SettingsController@subscription', ['AuthMiddleware']);

// ============================================================
// Admin Routes
// ============================================================
$router->get('/admin/login', 'AdminController@showLogin');
$router->post('/admin/login', 'AdminController@login');
$router->get('/admin/dashboard', 'AdminController@dashboard', ['AdminMiddleware']);
$router->get('/admin/companies', 'AdminController@companies', ['AdminMiddleware']);
$router->get('/admin/logout', 'AdminController@logout');
