<?php

use CodeIgniter\Router\RouteCollection;
/** @var RouteCollection $routes */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// $routes->setAutoRoute(false); // disarankan pakai false untuk keamanan

// ======================
// 1) RUTE ADMIN (DULUAN)
// ======================
$routes->group('admin', ['filter' => 'adminauth'], static function($routes){
    $routes->get('/',                       'Admin\Dashboard::index');

    // Settings
    $routes->get('settings',                'Admin\Settings::index');
    $routes->post('settings',               'Admin\Settings::save');

    // Announcements
    $routes->get('announcements',             'Admin\Announcements::index');
    $routes->get('announcements/list',        'Admin\Announcements::list');   // DataTables
    $routes->get('announcements/get/(:num)',  'Admin\Announcements::get/$1'); // optional (load ulang by id)
    $routes->post('announcements/save',       'Admin\Announcements::save');   // create & update
    $routes->post('announcements/toggle/(:num)','Admin\Announcements::toggle/$1');
    $routes->post('announcements/delete/(:num)','Admin\Announcements::delete/$1');

    // Services
    $routes->get('services',                'Admin\Dashboard::services');
    $routes->get('services/list',           'Admin\Dashboard::servicesList');
    $routes->post('services/store',         'Admin\Dashboard::serviceStore');
    $routes->post('services/update/(:num)', 'Admin\Dashboard::serviceUpdate/$1');
    $routes->post('services/delete/(:num)', 'Admin\Dashboard::serviceDelete/$1');
    $routes->post('services/toggle/(:num)', 'Admin\Dashboard::serviceToggle/$1');

 $routes->get('posts',        'Admin\Dashboard::posts');
    $routes->get('posts/list',   'Admin\Dashboard::postsList'); // <= ini wajib ADA
    $routes->get('posts/get/(:num)', 'Admin\Dashboard::postGet/$1');
    $routes->post('posts/save',  'Admin\Dashboard::postSave');
    $routes->post('posts/delete/(:num)', 'Admin\Dashboard::postDelete/$1');
    $routes->post('posts/toggle/(:num)', 'Admin\Dashboard::postToggle/$1');


    // FAQs (yang baru)
    $routes->get('faqs',                    'Admin\Dashboard::faqs');
    $routes->get('faqs/list',               'Admin\Dashboard::faqsList');
    $routes->post('faqs/store',             'Admin\Dashboard::faqStore');
    $routes->post('faqs/update/(:num)',     'Admin\Dashboard::faqUpdate/$1');
    $routes->post('faqs/delete/(:num)',     'Admin\Dashboard::faqDelete/$1');
    $routes->post('faqs/toggle/(:num)',     'Admin\Dashboard::faqToggle/$1');
    
    // Documents
    $routes->get('documents',                    'Admin\Dashboard::documents');
    $routes->get('documents/list',               'Admin\Dashboard::documentsList');
    $routes->post('documents/save',              'Admin\Dashboard::documentSave');      // create & update disatukan
    $routes->post('documents/delete/(:num)',     'Admin\Dashboard::documentDelete/$1');
    $routes->post('documents/toggle/(:num)',     'Admin\Dashboard::documentToggle/$1');
    
    // Menu Items
    $routes->get('menu',             'Admin\Dashboard::menu');
    $routes->get('menu/list',        'Admin\Dashboard::menuList');
    $routes->get('menu/parents',     'Admin\Dashboard::menuParents'); // dropdown parent
    $routes->post('menu/save',       'Admin\Dashboard::menuSave');    // insert & update disatukan
    $routes->post('menu/delete/(:num)', 'Admin\Dashboard::menuDelete/$1');
    $routes->post('menu/toggle/(:num)', 'Admin\Dashboard::menuToggle/$1');

    // Complaints (Pengaduan)
    $routes->get('complaints',                 'Admin\Dashboard::complaints');
    $routes->get('complaints/list',            'Admin\Dashboard::complaintsList');
    $routes->get('complaints/get/(:num)',      'Admin\Dashboard::complaintGet/$1');   // detail untuk modal
    $routes->post('complaints/update/(:num)',  'Admin\Dashboard::complaintUpdate/$1'); // ubah kategori/status
    $routes->post('complaints/delete/(:num)',  'Admin\Dashboard::complaintDelete/$1');
    // Subscribers (Buletin)
    $routes->get('subscribers',                 'Admin\Dashboard::subscribers');
    $routes->get('subscribers/list',            'Admin\Dashboard::subscribersList');
    $routes->get('subscribers/get/(:num)',      'Admin\Dashboard::subscriberGet/$1');
    $routes->post('subscribers/save',           'Admin\Dashboard::subscriberSave');   // create & update disatukan
    $routes->post('subscribers/toggle/(:num)',  'Admin\Dashboard::subscriberToggle/$1');
    $routes->post('subscribers/delete/(:num)',  'Admin\Dashboard::subscriberDelete/$1');
    // Pages (CMS statis per menu)
    $routes->get('pages',                 'Admin\Dashboard::pages');
    $routes->get('pages/list',            'Admin\Dashboard::pagesList');
    $routes->post('pages/save',           'Admin\Dashboard::pageSave');      // create & update disatukan
    $routes->post('pages/delete/(:num)',  'Admin\Dashboard::pageDelete/$1');
    $routes->post('pages/toggle/(:num)',  'Admin\Dashboard::pageToggle/$1'); // publish <-> draft


});

// Login admin (di luar filter)
$routes->get('admin/login',  'Admin\Auth::login');
$routes->post('admin/login', 'Admin\Auth::doLogin');
$routes->get('admin/logout', 'Admin\Auth::logout');
$routes->get('admin/ping',   static function(){ return 'pong'; });

// ======================
// 2) RUTE PUBLIK
// ======================
$routes->get('/',           'Home::index');
$routes->get('search',      'Search::index');                 

// Berita
$routes->get('berita',              'Posts::index');
$routes->get('berita/(:segment)',   'Posts::detail/$1');

// AJAX publik
$routes->post('pengaduan', 'Action::pengaduan');
$routes->post('subscribe', 'Action::subscribe');


$routes->get('(:segment)/(:segment)', 'Page::show/$1/$2');
$routes->get('(:segment)',            'Single::show/$1');


