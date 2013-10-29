<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 4:36 PM
 * To change this template use File | Settings | File Templates.
 */
namespace strategies;
use openid\strategies\IOpenIdResponseStrategy;
use \Response;
use \Redirect;

class OpenIdIndirectResponseStrategy implements IOpenIdResponseStrategy {

    public function handle($response)
    {
        $query_string = $response->getContent();
        $return_to    = $response->getReturnTo();
        if(is_null($return_to) || empty($return_to)){
            return \View::make('404');
        }
        $return_to    = (strpos($return_to,"?")===false)?$return_to."?".$query_string:$return_to."&".$query_string;
        return Redirect::to($return_to);
    }
}