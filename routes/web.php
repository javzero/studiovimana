<?php

/*
|--------------------------------------------------------------------------
| Web
|--------------------------------------------------------------------------
|
*/

Route::get('/', ['as' => 'web', 'uses' => 'WebController@home']);
Route::get('vt-001', function(){ return view('store.proceso'); });


/*
|--------------------------------------------------------------------------
| Web / Portfolio 
|--------------------------------------------------------------------------
*/

Route::get('portfolio', ['as'   => 'web.portfolio',	'uses' => 'WebController@portfolio']);
// Show Article / Catalogue
Route::get('article/{slug}', ['uses' => 'WebController@showWithSlug', 'as'   => 'web.portfolio.article'])->where('slug', '[\w\d\-\_]+');
// Article Searcher
Route::get('categories/{name}', ['uses' => 'WebController@searchCategory', 'as'   => 'web.search.category']);
Route::get('tag/{name}', ['uses' => 'WebController@searchTag', 'as'   => 'web.search.tag']);
Route::post('mail_sender', 'WebController@mail_sender');

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

Route::get('tienda', ['as' => 'store', 'uses' => 'Store\StoreController@index'])->middleware('active-customer');
Route::get('store-register-hold', ['as' => 'store-register-hold', 'uses' => 'CustomerAuth\RegisterController@holdRegisterLogin'])->middleware('active-customer');


Route::get('tienda/proceso', function(){ return view('store.proceso'); })->middleware('active-customer');


Route::group(['prefix'=> 'tienda', 'middleware' => 'active-customer'], function() {    

    // Customer Actions
    Route::get('articulo/{id}', 'Store\StoreController@show');
    

    Route::group(['middleware'=> 'customer'], function() {
        Route::post('updateCustomerAvatar', 'CustomerController@updateCustomerAvatar');
        
        // Cart
        // Route::post('/cart', 'Store\CartDetailController@store');
        Route::post('addtocart', ['as' => 'store.addtocart', 'uses' => 'Store\CartDetailController@store']);
        Route::post('removefromcart', ['as' => 'store.removefromcart', 'uses' => 'Store\CartDetailController@destroy']);
        Route::get('checkout', ['as' => 'store.checkout', 'uses' => 'Store\StoreController@checkout'])->middleware('verifyOrderStatus');
        Route::post('envio', ['as' => 'store.checkoutCustomerData', 'uses' => 'Store\StoreController@checkoutCustomerData'])->middleware('verifyOrderStatus');
        Route::get('envio', ['as' => 'store.checkoutShippingGet', 'uses' => 'Store\StoreController@checkoutShippingGet'])->middleware('verifyOrderStatus');
        Route::post('pago', ['as' => 'store.checkoutShipping', 'uses' => 'Store\StoreController@checkoutShipping'])->middleware('verifyOrderStatus');
        Route::get('pago', ['as' => 'store.checkoutPaymentGet', 'uses' => 'Store\StoreController@checkoutPaymentGet'])->middleware('verifyOrderStatus');
        Route::post('resumen', ['as' => 'store.checkoutPayment', 'uses' => 'Store\StoreController@checkoutPayment'])->middleware('verifyOrderStatus');
        Route::get('resumen', ['as' => 'store.checkoutReview', 'uses' => 'Store\StoreController@checkoutReview'])->middleware('verifyOrderStatus');
        Route::get('comprobante/{id}', ['as' => 'store.finishCheckOut', 'uses' => 'Store\StoreController@finishCheckOut'])->middleware('verifyOrderStatus');
        Route::get('descargar-comprobante/{id}', ['as' => 'store.orderInvoice', 'uses' => 'Store\StoreController@downloadInvoice']);
        
        

        
        //Route::post('mp-connect', ['as' => 'store.getCreatePreference', 'uses' => 'MercadoPagoController@getCreatePreference']);
        Route::post('mp-connect', ['as' => 'store.getCreatePreference', 'uses' => 'Store\StoreController@mpConnect']);
        
        // Sections    
        Route::get('cuenta', ['as' => 'store.customer-account', 'uses' => 'Store\StoreController@customerAccount']);
        Route::get('favoritos', ['as' => 'store.customer-wishlist', 'uses' => 'Store\StoreController@customerWishlist']);
        Route::get('pedidos', ['as' => 'store.customer-orders', 'uses' => 'Store\StoreController@customerOrders']);
        Route::get('pedido', ['as' => 'store.cartdetail', 'uses' => 'Store\StoreController@customerCartDetail']);
        Route::get('carroactivo', ['as' => 'store.activecart', 'uses' => 'Store\StoreController@customerActiveCartDetail']);

        // Customers
        Route::post('updateCustomer', ['as' => 'store.updateCustomer', 'uses' => 'Store\CustomerController@update']);
        Route::get('updatePassword', ['as' => 'store.updatePassword', 'uses' => 'Store\StoreController@updatePassword']);
        Route::post('updatePassword', ['as' => 'store.updatePassword', 'uses' => 'Store\CustomerController@updatePassword']);
        
    });

    Route::post('addArticleToFavs', ['as' => 'customer.addArticleToFavs', 'uses' => 'Store\StoreController@addArticleToFavs']);
    Route::post('removeArticleFromFavs', ['as' => 'customer.removeArticleFromFavs', 'uses' => 'Store\StoreController@removeArticleFromFavs']);
    Route::post('removeAllArticlesFromFavs', ['as' => 'customer.removeAllArticlesFromFavs', 'uses' => 'Store\StoreController@removeAllArticlesFromFavs']);

    // Store Login Routes
    Route::get('login', ['as' => 'customer.login', 'uses' => 'CustomerAuth\LoginController@showLoginForm']);
    Route::post('login', ['uses' => 'CustomerAuth\LoginController@login']);
    Route::post('logout', ['as' => 'customer.logout', 'uses' => 'CustomerAuth\LoginController@logout']);
    
    // Store Registration Routes
    Route::get('register', ['as' => 'customer.register', 'uses' => 'CustomerAuth\RegisterController@showRegistrationForm']);
    Route::post('register', ['uses' => 'CustomerAuth\RegisterController@register']);
    
    // Store Password Reset Routes
    Route::get('password/reset', ['as' => 'customer.password.reset', 'uses' => 'CustomerAuth\ForgotPasswordController@showLinkRequestForm']);
    Route::post('password/email', ['as' => 'customer.password.email', 'uses' => 'CustomerAuth\ForgotPasswordController@sendResetLinkEmail']);
    Route::get('password/reset/{token}', ['as' => 'customer.password.reset.token', 'uses' => 'CustomerAuth\ResetPasswordController@showResetForm']);
    Route::post('password/reset', ['uses' => 'CustomerAuth\ResetPasswordController@reset']);
        
});

/*
|--------------------------------------------------------------------------
| VADMIN
|--------------------------------------------------------------------------
*/

Auth::routes();
// AUTH ROUTES
Route::group(['prefix'=> 'vadmin'], function() {
    // Login Routes...
    Route::get('login', ['as' => 'vadmin.login', 'uses' => 'Auth\LoginController@showLoginForm']);
    Route::post('login', ['uses' => 'Auth\LoginController@login']);
    Route::post('logout', ['as' => 'vadmin.logout', 'uses' => 'Auth\LoginController@logout']);

    // Registration Routes...
    Route::get('register', ['as' => 'vadmin.register', 'uses' => 'Auth\RegisterController@showRegistrationForm']);
    Route::post('register', ['uses' => 'Auth\RegisterController@register']);

    // Password Reset Routes...
    Route::get('password/reset', ['as' => 'vadmin.password.reset', 'uses' => 'Auth\ForgotPasswordController@showLinkRequestForm']);
    Route::post('password/email', ['as' => 'vadmin.password.email', 'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail']);
    Route::get('password/reset/{token}', ['as' => 'vadmin.password.reset.token', 'uses' => 'Auth\ResetPasswordController@showResetForm']);
    Route::post('password/reset', ['uses' => 'Auth\ResetPasswordController@reset']);
});

/*
|--------------------------------------------------------------------------
| Vadmin - Sections
|--------------------------------------------------------------------------
*/

Route::get('sendmail', 'VadminController@sendMail');

// Functions that all users can access
Route::group(['prefix' => 'vadmin'], function(){
    Route::post('updateAvatar', 'UserController@updateAvatar');
    // -- SUPPORT --
    Route::get('docs', function(){ return view('vadmin.support.docs'); });
    Route::get('help', function(){ return view('vadmin.support.help'); });
    // Exports
    Route::get('exportViewPdf/{view}/{params}/{model}/{filename}', ['as' => 'vadmin.exportViewPdf', 'uses' => 'invoiceController@exportViewPdf']);
    
    // Export Users
    Route::get('exportUsersListPdf/{params}', ['as' => 'vadmin.exportUsersListPdf', 'uses' => 'UserController@exportPdf']);
    Route::get('exportUsersListXls/{params}', ['as' => 'vadmin.exportUsersListXls', 'uses' => 'UserController@exportXls']);
    // Export Customers
    Route::get('exportCustomersListPdf/{params}', ['as' => 'vadmin.exportCustomersListPdf', 'uses' => 'CustomerController@exportPdf']);
    Route::get('exportCustomersListXls/{params}', ['as' => 'vadmin.exportCustomersListXls', 'uses' => 'CustomerController@exportXls']);
    
    Route::get('exportCatalogListPdf/{params}', ['as' => 'vadmin.exportCatalogListPdf', 'uses' => 'Catalog\ArticlesController@exportPdf']);
    Route::get('exportCatalogListXls/{params}', ['as' => 'vadmin.exportCatalogListXls', 'uses' => 'Catalog\ArticlesController@exportExcel']);
    Route::get('mailChecker', ['as' => 'vadmin.mailChecker', 'uses' => 'ToolsController@mailChecker']);
    Route::get('configs', ['as' => 'vadmin.configs', 'uses' => 'VadminController@configs']);
    // Route::get('infophp', ['as' => 'vadmin.infophp', 'uses' => 'VadminController@infophp'])->middleware('admin');

});

Route::group(['prefix' => 'vadmin', 'middleware' => 'admin'], function(){

    //Route::get('/home', 'VadminController@index');
    Route::get('/', 'VadminController@index');
    
    Route::post('updateStatus/{model}/{id}', 'VadminController@updateStatus');

    // -- USERS --
    Route::resource('users', 'UserController');
    Route::post('message_status/{id}', 'VadminController@updateMessageStatus');

    // -- CUSTOMERS --
    Route::resource('customers', 'CustomerController');
    Route::post('updateCustomerGroup', 'CustomerController@updateCustomerGroup');
    
    // -- MESSAGES --
    Route::get('/mensajes_recibidos/{status}', 'VadminController@storedContacts');
    Route::post('buscar_mensajes_recibidos', ['as' => 'searchStoredContact', 'uses' => 'VadminController@searchStoredContact']);
    Route::get('mensajes_recibidos/{id}', 'VadminController@showStoredContact');
    Route::post('setMessageAsReaded', 'VadminController@setMessageAsReaded');
    
    // -- PORTFOLIO --
    Route::resource('portfolio', 'Portfolio\ArticlesController');
    Route::resource('categories', 'Portfolio\CategoriesController');
    Route::resource('tags', 'Portfolio\TagsController');
    // Route::post('updateStatus/{id}', 'Portfolio\ArticlesController@updateStatus');
    Route::post('deleteArticleImg/{id}', 'Portfolio\ArticlesController@deleteArticleImg');

    // -- CATALOG --
    Route::resource('catalogo', 'Catalog\ArticlesController');
    Route::post('update_catalog_stock/{id}', 'Catalog\ArticlesController@updateStock');
    Route::post('update_catalog_price/{id}', 'Catalog\ArticlesController@updatePrice');
    Route::post('update_catalog_discount/{id}', 'Catalog\ArticlesController@updateDiscount');
    Route::get('panel-de-control', ['as' => 'storeControlPanel', 'uses' => 'VadminController@storeControlPanel']);
    
    Route::resource('cat_categorias', 'Catalog\CategoriesController');
    Route::resource('cat_tags', 'Catalog\TagsController');
    Route::post('cat_article_status/{id}', 'Catalog\ArticlesController@updateStatus');
    Route::post('deleteArticleImg/{id}', 'Portfolio\ArticlesController@deleteArticleImg');
    // Atribute 1
    Route::resource('cat_atribute1', 'Catalog\CatalogAtribute1Controller');
    Route::post('catalog_make_thumb/{id}', 'Catalog\ArticlesController@makeThumb');
    // Shipping
    Route::resource('shippings', 'Catalog\ShippingsController');
    // Payments Methods
    Route::resource('payments', 'Catalog\PaymentsController');

    // Carts (Orders) Management
    Route::resource('carts', 'Store\CartsController');
    Route::post('updateCartStatus', 'Store\CartsController@updateStatus');

});

/*
|--------------------------------------------------------------------------
| Shared Functionalities
|--------------------------------------------------------------------------
*/

// Get Localities
Route::get('getGeoLocs/{id}', 'SharedController@getGeoLocs');
    
/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

Route::prefix('vadmin')->middleware('admin')->group(function () {
    Route::post('destroy_users', 'UserController@destroy');
    Route::post('destroy_customers', 'CustomerController@destroy');
    Route::post('destroy_portfolio', 'Portfolio\ArticlesController@destroy');
    Route::post('destroy_categories', 'Portfolio\CategoriesController@destroy');
    Route::post('destroy_tags', 'Portfolio\TagsController@destroy');
    Route::post('destroy_catalogo', 'Catalog\ArticlesController@destroy');
    Route::post('destroy_cat_categorias', 'Catalog\CategoriesController@destroy');
    Route::post('destroy_cat_tags', 'Catalog\TagsController@destroy');
    Route::post('destroy_stored_contacts', 'VadminController@destroyStoredContacts');
    Route::post('destroy_cat_atribute1', 'Catalog\CatalogAtribute1Controller@destroy');
    Route::post('destroy_product_image', 'Catalog\ImagesController@destroy');
    Route::post('destroy_portfolio_image', 'Portfolio\ImagesController@destroy');
    Route::post('destroy_shippings', 'Catalog\ShippingsController@destroy');
    Route::post('destroy_payments', 'Catalog\PaymentsController@destroy');
    Route::post('destroy_carts', 'Store\CartsController@destroy');
});

/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
*/
Route::get('404', ['as' => '404', 'uses' => 'ErrorController@notfound']);
Route::get('500', ['as' => '500', 'uses' => 'ErrorController@fatal']);

