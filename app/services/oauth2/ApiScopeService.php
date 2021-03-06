<?php

namespace services\oauth2;

use oauth2\exceptions\InvalidApi;
use oauth2\exceptions\InvalidApiScope;
use oauth2\models\IApiScope;
use oauth2\services\IApiScopeService;
use ApiScope;
use Api;
use DB;
use utils\db\ITransactionService;
/**
 * Class ApiScopeService
 * @package services\oauth2
 */
class ApiScopeService implements IApiScopeService {

	private $tx_service;

	/**
	 * @param ITransactionService $tx_service
	 */
	public function __construct(ITransactionService $tx_service){
		$this->tx_service = $tx_service;
	}

    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getScopesByName(array $scopes_names)
    {
        return ApiScope::where('active','=',true)->whereIn('name',$scopes_names)->get();
    }

    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getFriendlyScopesByName(array $scopes_names){
       return DB::table('oauth2_api_scope')->where('active','=',true)->whereIn('name',$scopes_names)->lists('short_description');
    }

    /**
     * @param bool $system
     * @return array|mixed
     */
    public function getAvailableScopes($system=false){
         $scopes = ApiScope
             ::with('api')
            ->with('api.resource_server')
            ->where('active','=',true)
            ->orderBy('api_id')->get();

        $res = array();

        foreach($scopes as $scope){
            $api = $scope->api()->first();
            if(!is_null($api) && $api->resource_server()->first()->active && $api->active){
                if($scope->system && !$system) continue;
                array_push($res,$scope);
            }
        }

        return $res;
    }

    /**
     * @param array $scopes_names
     * @return array|mixed
     */
    public function getAudienceByScopeNames(array $scopes_names){
        $scopes = $this->getScopesByName($scopes_names);
        $audience = array();
        foreach($scopes as $scope){
            $api = $scope->api()->first();
            $resource_server = !is_null($api)? $api->resource_server()->first():null;
            if(!is_null($resource_server) && !array_key_exists($resource_server->host, $audience)){
                $audience[$resource_server->host] = $resource_server->ip;
            }
        }
        return $audience;
    }

    /**
     * @param array $scopes_names
     * @return string
     */
    public function getStrAudienceByScopeNames(array $scopes_names){
        $audiences = $this->getAudienceByScopeNames($scopes_names);
        $audience  = '';
        foreach($audiences as $resource_server_host => $ip){
            $audience = $audience . $resource_server_host .' ';
        }
        $audience  = trim($audience);
        return $audience;
    }

    /**
     * gets an api scope by id
     * @param $id id of api scope
     * @return IApiScope
     */
    public function get($id)
    {
        return ApiScope::find($id);
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr=1,$page_size=10, array $filters=array(), array $fields=array('*'))
    {
        DB::getPaginator()->setCurrentPage($page_nbr);
        return ApiScope::Filter($filters)->paginate($page_size,$fields);
    }

    /**
     * @param IApiScope $scope
     * @return bool
     */
    public function save(IApiScope $scope)
    {
        if(!$scope->exists() || count($scope->getDirty())>0){
            return $scope->Save();
        }
        return true;
    }

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidApiScope
     */
    public function update($id, array $params)
    {
        $res      = false;
	    $this_var = $this;

	    $this->tx_service->transaction(function () use ($id,$params,&$res,&$this_var) {

            //check that scope exists...
            $scope = ApiScope::find($id);
            if(is_null($scope))
                throw new InvalidApiScope(sprintf('scope id %s does not exists!',$id));

            $allowed_update_params = array('name','description','short_description','active','system','default');

            foreach($allowed_update_params as $param){
                if(array_key_exists($param,$params)){

                    if($param=='name'){
                        //check if we have a former scope with selected name
                        if(ApiScope::where('name','=',$params[$param])->where('id','<>',$id)->count()>0)
                            throw new InvalidApiScope(sprintf('scope name %s already exists!',$params[$param]));
                    }

                    $scope->{$param} = $params[$param];
                }
            }
            $res = $this_var->save($scope);
        });
        return $res;
    }


    /**
     * sets api scope status (active/deactivated)
     * @param \oauth2\services\id $id
     * @param bool $status
     * @throws \oauth2\exceptions\InvalidApiScope
     */
    public function setStatus($id, $active)
    {
	    $scope = ApiScope::find($id);
        if(is_null($scope))
            throw new InvalidApiScope(sprintf('scope id %s does not exists!',$id));

        return $scope->update(array('active'=>$active));
    }

    /**
     * deletes an api scope
     * @param $id id of api scope
     * @return bool
     */
    public function delete($id)
    {
        $res = false;
	    $this->tx_service->transaction(function () use ($id,&$res) {

            $scope = ApiScope::find($id);
            if(is_null($scope))
                throw new InvalidApiScope(sprintf('scope id %s does not exists!',$id));

            $res = $scope->delete();
        });
        return $res;
    }

    /**
     * Creates a new api scope instance
     * @param $name
     * @param $short_description
     * @param $description
     * @param $active
     * @param $default
     * @param $system
     * @param $api_id
     * @throws \oauth2\exceptions\InvalidApi
     * @return IApiScope
     */
    public function add($name, $short_description, $description, $active, $default, $system, $api_id)
    {
        $instance = null;
	    $this->tx_service->transaction(function () use ($name, $short_description, $description, $active, $default, $system, $api_id, &$instance) {

            // check if api exists...
            if(is_null(Api::find($api_id)))
                throw new InvalidApi(sprintf('api id %s does not exists!.',$api_id));

            //check if we have a former scope with selected name
            if(ApiScope::where('name','=',$name)->count()>0)
                throw new InvalidApiScope(sprintf('scope name %s not allowed.',$name));

            $instance = new ApiScope(
                array(
                    'name'              => $name,
                    'description'       => $description,
                    'short_description' => $short_description,
                    'active'            => $active,
                    'default'           => $default,
                    'system'            => $system,
                    'api_id'            => $api_id
                )
            );

            $instance->Save();
        });
        return $instance;
    }

    /**
     * @return mixed
     */
    public function getDefaultScopes(){
        return $scopes = ApiScope::where('default','=',true)->where('active','=',true)->get(array('id'));
    }
}