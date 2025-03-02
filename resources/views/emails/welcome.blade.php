@extends('emails.layouts.default')

@section('content')
<h1>Welcome to JunkHop!</h1>

<p>Hello {{ $user->name }},</p>

<div class="content-box">
    <p>Thank you for joining JunkHop, your partner in sustainable waste management. Your account has been successfully created.</p>
</div>

<p>You can now login to your account and start using our platform to:</p>

<ul>
    <li>Schedule waste collection</li>
    <li>Track recycling efforts</li>
    <li>Connect with local junkshops</li>
    <li>Monitor your environmental impact</li>
</ul>

<p>Get started by clicking the button below:</p>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <a href="{{ url('/dashboard') }}" class="button button-green" target="_blank">Access Dashboard</a>
        </td>
    </tr>
</table>

<div class="divider"></div>

<p>If you have any questions or need assistance, our support team is here to help.</p>

<p>Best regards,<br>The JunkHop Team</p>
@endsection
