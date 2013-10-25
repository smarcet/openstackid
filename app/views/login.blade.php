@extends('layout')
@section('content')

<div class="span4" id="sidebar">
    <h4>Welcome to OpenstackId!!!</h4>
    <div class="well">
        {{ Form::open(array('url' => '/accounts/user/login', 'method' => 'post',  "autocomplete" => "off")) }}
        <fieldset>
            <legend>Login</legend>
            {{ Form::text('username',null, array('placeholder' => 'Username','class'=>'input-block-level')) }}
            {{ Form::password('password', array('placeholder' => 'Password','class'=>'input-block-level')) }}
            <label class="checkbox">
                {{ Form::checkbox('remember', '1', false) }}Remember me
            </label>
            <div class="pull-right">
                {{ Form::submit('Login',array('id'=>'login','class'=>'btn btn-primary')) }}
            </div>
        </fieldset>
        {{ Form::close() }}
    </div>

    @if(Session::has('flash_notice'))
    <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ Session::get('flash_notice')  }}
    </div>
    @else
    @foreach($errors->all() as $message)
    <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ $message }}
    </div>
    @endforeach
    @endif

</div>
<div class="span8">

</div>

@stop