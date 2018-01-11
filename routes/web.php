<?php

/*
|--------------------------------------------------------------------------
| Web
|--------------------------------------------------------------------------
|
*/

Route::get('/', [
	'as'   => 'web',
	'uses' => 'WebController@home',
]);

/*
|--------------------------------------------------------------------------
| VAdmin
|--------------------------------------------------------------------------
*/

Auth::routes();
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

    // Route::get('/home', 'VadminController@index');
    // Route::get('/vadmin', 'VadminController@index');

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/
    Route::get('tienda', ['as' => 'store', 'uses' => 'Store\StoreController@index']);
    Route::post('/cart', 'Store\CartDetailController@store');    
    
    Route::group(['prefix'=> 'tienda'], function() {        
        // Cart
        
        // Sections    
        Route::get('articulo/{id}', 'Store\StoreController@show')->middleware('customer');
        Route::get('cuenta', ['as' => 'store.client-account', 'uses' => 'Store\StoreController@clientProfile'])->middleware('customer');
        Route::get('favoritos', ['as' => 'store.client-wishlist', 'uses' => 'Store\StoreController@clientWishlist'])->middleware('customer');
        Route::post('addArticleToFavs', ['as' => 'customer.addArticleToFavs', 'uses' => 'Store\StoreController@addArticleToFavs']);
        Route::post('removeArticleFromFavs', ['as' => 'customer.removeArticleFromFavs', 'uses' => 'Store\StoreController@removeArticleFromFavs']);
        Route::post('removeAllArticlesFromFavs', ['as' => 'customer.removeAllArticlesFromFavs', 'uses' => 'Store\StoreController@removeAllArticlesFromFavs']);

        // Login Routes...
        Route::get('login', ['as' => 'customer.login', 'uses' => 'CustomerAuth\LoginController@showLoginForm']);
        Route::post('login', ['uses' => 'CustomerAuth\LoginController@login']);
        Route::post('logout', ['as' => 'customer.logout', 'uses' => 'CustomerAuth\LoginController@logout']);
        
        // Registration Routes...
        Route::get('register', ['as' => 'customer.register', 'uses' => 'CustomerAuth\RegisterController@showRegistrationForm']);
        Route::post('register', ['uses' => 'CustomerAuth\RegisterController@register']);
        
        // Password Reset Routes...
        Route::get('password/reset', ['as' => 'customer.password.reset', 'uses' => 'CustomerAuth\ForgotPasswordController@showLinkRequestForm']);
        Route::post('password/email', ['as' => 'customer.password.email', 'uses' => 'CustomerAuth\ForgotPasswordController@sendResetLinkEmail']);
        Route::get('password/reset/{token}', ['as' => 'customer.password.reset.token', 'uses' => 'CustomerAuth\ResetPasswordController@showResetForm']);
        Route::post('password/reset', ['uses' => 'CustomerAuth\ResetPasswordController@reset']);
        
            
    });

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
| VADMIN / SECTIONS
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'vadmin', 'middleware' => 'admin'], function(){

    //Route::get('/home', 'VadminController@index');
    Route::get('/', 'VadminController@index');
    
    // -- USERS --
    Route::resource('users', 'UserController');
    Route::post('updateAvatar', 'UserController@updateAvatar');	
    Route::get('/mensajes_recibidos', 'VadminController@storedContacts');
    Route::get('mensajes_recibidos/{id}', 'VadminController@showStoredContact');
    Route::post('message_status/{id}', 'VadminController@updateMessageStatus');
        
    // -- PORTFOLIO --
    Route::resource('portfolio', 'Portfolio\ArticlesController');
    Route::resource('categories', 'Portfolio\CategoriesController');
    Route::resource('tags', 'Portfolio\TagsController');
    Route::post('updateStatus/{id}', 'Portfolio\ArticlesController@updateStatus');
    Route::post('deleteArticleImg/{id}', 'Portfolio\ArticlesController@deleteArticleImg');

    // -- CATALOG --
    Route::resource('catalogo', 'Catalog\ArticlesController');
    Route::post('update_catalog_stock/{id}', 'Catalog\ArticlesController@updateStock');
    Route::post('update_catalog_price/{id}', 'Catalog\ArticlesController@updatePrice');
    Route::post('update_catalog_offer/{id}', 'Catalog\ArticlesController@updateOffer');
    
    Route::resource('cat_categorias', 'Catalog\CategoriesController');
    Route::resource('cat_tags', 'Catalog\TagsController');
    Route::post('cat_article_status/{id}', 'Catalog\ArticlesController@updateStatus');
    Route::post('deleteArticleImg/{id}', 'Portfolio\ArticlesController@deleteArticleImg');
    // Atribute 1
    Route::resource('cat_atribute1', 'Catalog\CatalogAtribute1Controller');
    Route::post('catalog_make_thumb/{id}', 'Catalog\ArticlesController@makeThumb');

    // -- DOCS --
    Route::get('docs', function () {
        return view('vadmin.docs');
    });

});
    
/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

Route::prefix('vadmin')->middleware('admin')->group(function () {
    Route::post('destroy_users', 'UserController@destroy');
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
});


/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
*/
Route::get('404', ['as' => '404', 'uses' => 'ErrorController@notfound']);
Route::get('500', ['as' => '500', 'uses' => 'ErrorController@fatal']);