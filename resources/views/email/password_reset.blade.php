
<h3>{{__('Hello')}}, {{isset($user) ? $user->name : ''}}</h3>
<p>
    {{ __('You are receiving this email because we received a password reset request for your account.') }}
    {{__('Please use the code below to reset your password.')}}
</p>
<p style="text-align:center;font-size:30px;font-weight:bold">
    {{$token}}
</p>


