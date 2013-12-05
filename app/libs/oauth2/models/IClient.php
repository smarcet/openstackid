<?php

namespace oauth2\models;


interface IClient {

    const ClientType_Public       = 1;
    const ClientType_Confidential = 2;

    public function getClientId();
    public function getClientSecret();
    public function getClientType();
    public function getClientAuthorizedRealms();
    public function getClientScopes();
    public function getClientRegisteredUris();
    public function isScopeAllowed($scope);
    public function isRealmAllowed($realm);
    public function isUriAllowed($uri);
    public function getApplicationName();
    public function getApplicationLogo();
    public function getApplicationDescription();
    public function getDeveloperEmail();
} 