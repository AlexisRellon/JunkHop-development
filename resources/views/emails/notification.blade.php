@extends('emails.layouts.default')

@section('content')
<h1>{{ $title }}</h1>

<p>Hello {{ $user->name }},</p>

<div class="content-box">
    <p>{{ $message }}</p>
</div>

@if(isset($actionUrl) && isset($actionText))
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" class="button button-blue" target="_blank">{{ $actionText }}</a>
        </td>
    </tr>
</table>
@endif

<div class="divider"></div>

<p>If you did not request this notification, no further action is required.</p>

<p>Best regards,<br>The JunkHop Team</p>
@endsection
