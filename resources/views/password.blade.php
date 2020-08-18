@extends('layouts.app')

@section('content')
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span>
                </button>
                <strong>成功!</strong>{{ session('success') }}
            </div>
        @endif
        @if (session('notice'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span>
                </button>
                <strong>失败!</strong>{{ session('notice') }}
            </div>
        @endif

        <form class="form-signin" method="POST" action="{{ route('retrieve') }}" aria-label="{{ __('Reset Password') }}">
            @csrf
            <h2 class="form-signin-heading">{{ __('Reset Password') }}</h2>
            <label for="inputOldPassword" class="sr-only">{{ __('原始密码') }}</label>
            <input type="password" id="old_password" class="form-control" placeholder="{{ __('原始密码') }}" name="old_password" required="">
            <br>
            <label for="inputPassword" class="sr-only">{{ __('Password') }}</label>
            <input type="password" id="password" class="form-control" placeholder="{{ __('Password') }}" name="password"
                   required="">
            <br>
            <label for="inputConfirmPassword" class="sr-only">{{ __('Confirm Password') }}</label>
            <input type="password" id="confirm_password" class="form-control" placeholder="{{ __('Confirm Password') }}" name="password_confirmation"
                   required="">
            <br>
            <button class="btn btn-lg btn-primary btn-block" type="submit">{{ __('提交') }}</button>
        </form>

    </div>

@endsection
