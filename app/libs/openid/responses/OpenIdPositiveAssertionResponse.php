<?php

namespace openid\responses;

use openid\OpenIdProtocol;

class OpenIdPositiveAssertionResponse extends OpenIdIndirectResponse
{

    public function __construct($op_endpoint, $claimed_id, $identity, $return_to, $nonce, $realm)
    {
        parent::__construct();
        $this->setMode(OpenIdProtocol::IdMode);
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)] = $op_endpoint;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)] = $claimed_id;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)] = $identity;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)] = $return_to;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Nonce)] = $nonce;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)] = $realm;
    }

    public function setAssocHandle($assoc_handle)
    {
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocHandle)] = $assoc_handle;
    }

    public function setSigned($signed)
    {
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)] = $signed;
    }

    public function getSig()
    {
        return $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)];
    }

    public function setSig($sig)
    {
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)] = $sig;
    }

    public function setInvalidateHandle($invalidate_handle)
    {
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_InvalidateHandle)] = $invalidate_handle;
    }

}
