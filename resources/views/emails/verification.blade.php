@extends('emails.layouts.default')

@section('content')
<h1>Verify Your Email Address</h1>

<p>Hello {{ $user->name }},</p>

<div class="content-box">
    <p>Thank you for registering with JunkHop. Please verify your email address to gain full access to our platform.</p>
    <p>This verification link will expire in 60 minutes.</p>
</div>

<p>By verifying your email, you'll be able to:</p>

<ul>
    <li>Schedule waste collections</li>
    <li>Connect with local junkshops</li>
    <li>Track your recycling impact</li>
    <li>Receive important notifications</li>
</ul>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <a href="{{ $url }}" class="button button-green" target="_blank">Verify Email Address</a>
        </td>
    </tr>
</table>

<div class="divider"></div>

<p>If you did not create an account, no further action is required.</p>

<p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
<p style="word-break: break-all; font-size: 14px; color: #718096;">{{ $url }}</p>

<p>Best regards,<br>The JunkHop Team</p>
@endsection
