<?php
$g = fn($p, $h) => Router::add('GET', $p, $h); $p = fn($p, $h) => Router::add('POST', $p, $h);
$g('/me', 'AuthController@me'); $p('/register', 'AuthController@register'); $p('/login', 'AuthController@login'); $p('/logout', 'AuthController@logout');
$g('/categories', 'PublicController@categories'); $g('/businesses', 'PublicController@businesses'); $g('/businesses/{id}', 'PublicController@business');
$g('/products', 'PublicController@products'); $g('/products/{id}', 'PublicController@product');
$g('/favorites', 'PublicController@favorites'); $p('/favorites/toggle', 'PublicController@toggleFav');
$p('/reviews', 'PublicController@review'); $p('/inquiries', 'PublicController@inquiry'); $p('/reports', 'PublicController@report');
$g('/notifications', 'PublicController@notifications'); $p('/notifications/read', 'PublicController@readNotes');
$g('/owner/dashboard', 'OwnerController@dashboard'); $p('/owner/business', 'OwnerController@save'); $p('/owner/products', 'OwnerController@saveProduct');
$p('/owner/products/{id}/delete', 'OwnerController@deleteProduct'); $p('/owner/upload', 'OwnerController@upload');
$g('/admin/stats', 'AdminController@stats'); $g('/admin/businesses', 'AdminController@businesses'); $p('/admin/businesses/{id}', 'AdminController@act');
$g('/admin/users', 'AdminController@users'); $p('/admin/users/{id}/toggle', 'AdminController@toggleUser');
$g('/admin/reports', 'AdminController@reports'); $p('/admin/categories', 'AdminController@addCategory'); $p('/admin/products/{id}/delete', 'AdminController@deleteProduct');
