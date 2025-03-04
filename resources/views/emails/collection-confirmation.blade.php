@extends('emails.layouts.default')

@section('content')
<h1>Waste Collection Confirmed!</h1>

<p>Hello {{ $user->name }},</p>

<div class="content-box">
    <p>Your waste collection has been scheduled successfully.</p>
    <p><strong>Collection Details:</strong></p>
    <ul>
        <li>Date: {{ $collection->date }}</li>
        <li>Time: {{ $collection->time_slot }}</li>
        <li>Address: {{ $collection->address }}</li>
        <li>Waste Type: {{ $collection->waste_type }}</li>
    </ul>
</div>

<p>A JunkHop collector will arrive at the scheduled time. Please ensure:</p>

<ul>
    <li>Waste is properly segregated</li>
    <li>Someone is available to hand over the materials</li>
    <li>Access to the property is clear</li>
</ul>

<p>You can track your collection status by clicking the button below:</p>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <a href="{{ url('/collections/' . $collection->id) }}" class="button button-green" target="_blank">Track Collection</a>
        </td>
    </tr>
</table>

<div class="divider"></div>

<p>If you need to reschedule or cancel, please do so at least 24 hours before the scheduled time.</p>

<p>Best regards,<br>The JunkHop Team</p>
@endsection
