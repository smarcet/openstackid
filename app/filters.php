<?php
use openid\exceptions\InvalidOpenIdMessageException;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\OpenIdServiceCatalog;
use utils\services\ServiceLocator;
use utils\services\UtilsServiceCatalog;
use oauth2\services\OAuth2ServiceCatalog;
use oauth2\exceptions\InvalidAuthorizationRequestException;
/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

//SAP (single access point)
App::before(function($request){
    try {
        //checkpoint security pattern entry point
        $checkpoint_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::CheckPointService);
        if (!$checkpoint_service->check()) {
            return View::make('404');
        }
    } catch (Exception $ex) {
        Log::error($ex);
        return View::make('404');
    }

    $cors = ServiceLocator::getInstance()->getService('CORSMiddleware');
    if($response = $cors->verifyRequest($request))
        return $response;
});

App::after(function($request, $response){
    // https://www.owasp.org/index.php/List_of_useful_HTTP_headers
    $response->headers->set('X-content-type-options','nosniff');
    $response->headers->set('X-xss-protection','1; mode=block');
    // http://tools.ietf.org/html/rfc6797
    /**
     * The HSTS header field below stipulates that the HSTS Policy is to
     * remain in effect for one year (there are approximately 31536000
     * seconds in a year)
     * applies to the domain of the issuing HSTS Host and all of its
     * subdomains:
     */
    $response->headers->set('Strict-Transport-Security','max-age=31536000; includeSubDomains');
    //cache
    $response->headers->set('pragma','no-cache');
    $response->headers->set('Expires','-1');
    $response->headers->set('cache-control','no-store, must-revalidate, no-cache');
    $cors = ServiceLocator::getInstance()->getService('CORSMiddleware');
    $cors->modifyResponse($request, $response);
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function () {
    if (Auth::guest()) {
        Session::put('url.intended', URL::full());
        return Redirect::action('HomeController@index');
    }
    $redirect = Session::get('url.intended');
    if (!empty($redirect)) {
        Session::forget('url.intended');
        return Redirect::to($redirect);
    }
});

Route::filter('auth.basic', function () {
    return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function () {
    if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function () {
    if (Session::token() != Input::get('_token')) {
        throw new Illuminate\Session\TokenMismatchException;
    }
});

Route::filter('ajax', function()
{
    if (!Request::ajax()) App::abort(404);
});


Route::filter("openid.needs.auth.request", function () {

    $memento_service = ServiceLocator::getInstance()->getService(OpenIdServiceCatalog::MementoService);
    $openid_message = $memento_service->getCurrentRequest();

    if ($openid_message == null || !$openid_message->isValid())
        throw new InvalidOpenIdMessageException();
    $configuration_service = ServiceLocator::getInstance()->getService(OpenIdServiceCatalog::ServerConfigurationService);
    $auth_request          = new OpenIdAuthenticationRequest($openid_message, $configuration_service->getUserIdentityEndpointURL('@identifier'));
    if (!$auth_request->isValid())
        throw new InvalidOpenIdMessageException();
});

Route::filter("openid.save.request", function () {

    $memento_service = ServiceLocator::getInstance()->getService(OpenIdServiceCatalog::MementoService);
    $memento_service->saveCurrentRequest();

});

Route::filter("oauth2.save.request", function () {

    $memento_service = ServiceLocator::getInstance()->getService(OAuth2ServiceCatalog::MementoService);
    $memento_service->saveCurrentAuthorizationRequest();
});

Route::filter("oauth2.needs.auth.request", function () {

    $memento_service = ServiceLocator::getInstance()->getService(OAuth2ServiceCatalog::MementoService);
    $oauth2_message  = $memento_service->getCurrentAuthorizationRequest();

    if ($oauth2_message == null || !$oauth2_message->isValid())
        throw new InvalidAuthorizationRequestException();
});

Route::filter("ssl", function () {
    if ((!Request::secure()) && (ServerConfigurationService::getConfigValue("SSL.Enable"))) {
        $openid_memento_service = ServiceLocator::getInstance()->getService(OpenIdServiceCatalog::MementoService);
        $openid_memento_service->saveCurrentRequest();

        $oauth2_memento_service = ServiceLocator::getInstance()->getService(OAuth2ServiceCatalog::MementoService);
        $oauth2_memento_service->saveCurrentAuthorizationRequest();

        return Redirect::secure(Request::getRequestUri());
    }
});

Route::filter("oauth2.enabled",function(){
    if(!ServerConfigurationService::getConfigValue("OAuth2.Enable")){
        return View::make('404');
    }
});

Route::filter('user.owns.client.policy',function($route, $request){
    try{
        $authentication_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::AuthenticationService);
        $client_service         = ServiceLocator::getInstance()->getService(OAuth2ServiceCatalog::ClientService);
        $client_id              = $route->getParameter('id');
        $client                 = $client_service->getClientByIdentifier($client_id);
        $user                   = $authentication_service->getCurrentUser();
        if (is_null($client) || intval($client->getUserId()) !== intval($user->getId()))
            throw new Exception('invalid client id for current user');

    } catch (Exception $ex) {
        Log::error($ex);
        return Response::json(array('error' => 'operation not allowed.'), 400);
    }
});

Route::filter('is.current.user',function($route, $request){
    try{
        $authentication_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::AuthenticationService);
        $used_id                = Input::get('user_id',null);

        if(is_null($used_id))
            $used_id            = Input::get('id',null);

        if(is_null($used_id))
            $used_id =  $route->getParameter('user_id');

        if(is_null($used_id))
            $used_id =  $route->getParameter('id');

        $user                   = $authentication_service->getCurrentUser();
        if (is_null($used_id) || intval($used_id) !== intval($user->getId()))
            throw new Exception(sprintf('user id %s does not match with current user id %s',$used_id,$user->getId()));

    } catch (Exception $ex) {
        Log::error($ex);
        return Response::json(array('error' => 'operation not allowed.'), 400);
    }
});




// filter to protect an api endpoint with oauth2

Route::filter('oauth2.protected.endpoint','OAuth2BearerAccessTokenRequestValidator');

//oauth2 server admin filter

Route::filter('oauth2.server.admin.json',function(){
    if (Auth::guest()) {
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
    }
    if(!Auth::user()->isOAuth2ServerAdmin()){
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
    }
});


Route::filter('oauth2.server.admin',function(){
    if (Auth::guest()) {
        return View::make('404');
    }
    if(!Auth::user()->isOAuth2ServerAdmin()){
        return View::make('404');
    }
});


//openstackid server admin

Route::filter('openstackid.server.admin.json',function(){
    if (Auth::guest()) {
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
    }
    if(!Auth::user()->isOpenstackIdAdmin()){
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
    }
});


Route::filter('openstackid.server.admin',function(){
    if (Auth::guest()) {
        return View::make('404');
    }
    if(!Auth::user()->isOpenstackIdAdmin()){
        return View::make('404');
    }
});
