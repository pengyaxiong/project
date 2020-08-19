@extends('layouts.app')

@section('content')
    <div class="container">

        @if (session('status'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <strong>错误!</strong>{{ session('status') }}
            </div>
        @endif
        @if ($errors->has('account'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <strong>错误!</strong>{{ $errors->first('account') }}
            </div>
        @endif

        @if ($errors->has('password'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <strong>错误!</strong>{{ $errors->first('password') }}
            </div>
        @endif

        <form class="form-signin" method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
            @csrf
            <h2 class="form-signin-heading">{{ __('Login') }}</h2>
            <label for="inputEmail" class="sr-only">{{ __('E-Mail Address') }}</label>
            <input type="text" id="account" class="form-control" placeholder="{{ __('用户名/手机号') }}" name="account"
                   value="{{ old('account') }}" required="" autofocus="">
            <br>
            <label for="inputPassword" class="sr-only">{{ __('Password') }}</label>
            <input type="password" id="password" class="form-control" placeholder="{{ __('Password') }}" name="password"
                   required="">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="remember"
                           id="remember" {{ old('remember') ? 'checked' : '' }}> {{ __('Remember Me') }}
                </label>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit">{{ __('Login') }}</button>
        </form>

    </div>

@endsection
