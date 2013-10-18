<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 5:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\services;


interface IServerConfigurationService {
    public function getOPEndpointURL();
    public function getPrivateAssociationLifetime();
    public function getSessionAssociationLifetime();
}