<?php

use auth\OpenIdUser;
use oauth2\OAuth2Protocol;
use utils\services\IAuthService;

/**
 * Class OAuth2TokenEndpointTest
 */
class OAuth2TokenEndpointTest extends TestCase
{

    /**
     * Get Auth Code Test
     */
    public function testAuthCode()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => 'https://www.test.com/users/activities.read'
        );

        $user = OpenIdUser::where('external_id', '=', 'smarcet@gmail.com')->first();

        Auth::login($user);

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $status = $response->getStatusCode();
        $url = $response->getTargetUrl();
        $content = $response->getContent();
    }

    /**
     * Get Token Test
     */
    public function testToken()
    {


        try {

            $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
            $client_secret = 'ITc/6Y5N7kOtGKhg';

            $params = array(
                'client_id' => $client_id,
                'redirect_uri' => 'https://www.test.com/oauth2',
                'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Code,
                'scope' => 'https://www.test.com/users/activities.read'
            );

            $user = OpenIdUser::where('external_id', '=', 'smarcet@gmail.com')->first();

            Auth::login($user);

            Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

            $response = $this->action("POST", "OAuth2ProviderController@authorize",
                $params,
                array(),
                array(),
                array());

            $status = $response->getStatusCode();
            $url = $response->getTargetUrl();
            $content = $response->getContent();

            $comps = @parse_url($url);
            $query = $comps['query'];
            $output = array();
            parse_str($query, $output);

            $params = array(
                'code' => $output['code'],
                'redirect_uri' => 'https://www.test.com/oauth2',
                'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
            );


            $response = $this->action("POST", "OAuth2ProviderController@token",
                $params,
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $status  = $response->getStatusCode();

            $this->assertResponseStatus(200);

            $content = $response->getContent();

            $response      = json_decode($content);
            $access_token  = $response->access_token;
            $refresh_token = $response->refresh_token;

            $this->assertTrue(!empty($access_token));
            $this->assertTrue(!empty($refresh_token));

            $params = array(
                'token'      => $access_token,
                'grant_type' =>  oauth2\grant_types\ValidateBearerTokenGrantType::OAuth2Protocol_GrantType_Extension_ValidateBearerToken,
            );

            $response = $this->action("POST", "OAuth2ProviderController@token",
                $params,
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(200);

            $content = $response->getContent();

            $response = json_decode($content);
            $test_access_token = $response->access_token;

            $this->assertTrue(!empty($test_access_token));
            $this->assertTrue($test_access_token === $access_token);

            $params = array(
                'refresh_token'  => $refresh_token,
                'grant_type'     =>  OAuth2Protocol::OAuth2Protocol_GrantType_RefreshToken,
            );

            $response = $this->action("POST", "OAuth2ProviderController@token",
                $params,
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(200);

            $response = $this->action("POST", "OAuth2ProviderController@token",
                $params,
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(400);


        } catch (Exception $ex) {
            throw $ex;
        }
    }
} 