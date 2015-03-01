@extends('layout.login')

@section('content')
<?php Former::framework('TwitterBootstrap3') ?>
{{ Former::open_vertical('login')->class('form-signin')->role('form') }}

        <h2>Welcome to {{ Config::get('site.name')}}</h2>
        <h6>
            Please sign in to get started!
        </h6>
            @if (Session::get('loginError'))
                <div class="alert alert-danger">{{ Session::get('loginError') }}</div>
                     <button type="button" class="close" data-dismiss="alert"></button>
            @endif

            {{ Former::text('email','Email')->placeholder('Your email')->id('username') }}

            {{ Former::password('password','Password')->placeholder('Your password')->id('password') }}

            <label class="checkbox">
                <input type="checkbox" value="remember-me" name="remember"> Remember me
            </label>

            <button style="border-color:#AAB2BD;" class="btn btn-normal btn-block" type="submit">Sign in</button>

{{ Form::close() }}

@stop