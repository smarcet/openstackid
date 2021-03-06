<?php

use oauth2\models\IApiScope;
use utils\model\BaseModelEloquent;

class ApiScope extends BaseModelEloquent implements IApiScope {

    protected $fillable = array('name' ,'short_description', 'description','active','default','system', 'api_id');

	public function getActiveAttribute(){
		return (bool) $this->attributes['active'];
	}

	public function getDefaultAttribute(){
		return (bool) $this->attributes['default'];
	}

	public function getSystemAttribute(){
		return (bool) $this->attributes['system'];
	}

	public function getIdAttribute(){
		return (int) $this->attributes['id'];
	}

	public function getApiIdAttribute(){
		return (int) $this->attributes['api_id'];
	}

    protected $table = 'oauth2_api_scope';

    protected $hidden = array('pivot');

    public function api()
    {
        return $this->belongsTo('Api');
    }

    public function getShortDescription()
    {
        return $this->short_description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function getApiName()
    {
        $api = $this->api()->first();
        return !is_null($api)?$api->name:'';
    }

    public function getApiDescription(){
        $api = $this->api()->first();
        return !is_null($api)? $api->description:'';
    }

    public function getApiLogo(){
        $api = $this->api()->first();
        return !is_null($api)? $api->getLogo():asset('img/apis/server.png');
    }
}