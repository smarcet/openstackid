<?php

namespace oauth2\models;

/**
 * Interface IOAuth2User
 * @package oauth2\models
 */
interface IOAuth2User {

    /**
     *  OAUTH2 Server Admin Group Code (SS DB)
     *  Users that belongs to this group are allowed to enter on
     *  Server Admin Area
     */
    const OAuth2ServerAdminGroup      = 'oauth2-server-admin';
    /**
       OAUTH2 System Scope Admin Group Code (SS DB)
     * Users that belongs to this group are allowed to use
     * System Scopes for their OAUTH2 applications
     */
    const OAuth2SystemScopeAdminGroup = 'oauth2-system-scope-admin';

    public function getClients();

    /**
     * Could use system scopes on registered clients
     * @return bool
     */
    public function canUseSystemScopes();


    /**
     * Is Server Administrator
     * @return bool
     */
    public function isOAuth2ServerAdmin();
} 