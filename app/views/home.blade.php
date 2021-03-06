@extends('layout')
@section('title')
<title>Welcome to openstackId</title>
@stop
@section('content')
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span6 offset3">

            <h1>OpenstackId Identity Provider</h1>
            <div class="panel">
                <div class="panel-heading strong">Log in to OpenStack</div>
	            <div style="text-align: center">
                <a href="{{ URL::action("UserController@getLogin")}}" class="btn">Sign in to your account</a>
	            <a href="{{ ServerConfigurationService::getConfigValue("Assets.Url") }}join/register" class="btn">Register for an OpenStack ID</a>
		        </div>
                <p class="text-info margin-top-20">Once you're signed in, you can manage your trusted sites, change your settings and more.</p>
            </div>
        </div>
    </div>
</div>
@stop