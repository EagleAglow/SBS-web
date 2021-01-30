@extends('layouts.app')
 
@section('title', '| Settings')

@section('content')
<div class="container">
	<div class="row justify-content-center"> 
		<div class="col-md-12">
			<div class="card shadow">
                <div class="card-header">Admin Dashboard - Settings</div>

				@include('flash::message')

				@php
					$param_name_or_taken = App\Param::where('param_name','name-or-taken')->first()->string_value;
					$param_next_bidder_email_on_or_off = App\Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
					$bid_accepted_email_on_or_off = App\Param::where('param_name','bid-accepted-email-on-or-off')->first()->string_value;
				@endphp

				<div class="card-body squash row">
					@if($param_name_or_taken == 'taken')
						<div class="col">
							Lines already taken by bidders show "TAKEN" instead of bidder name. Click to show bidder name.
						</div>
						<div class="col-md-4">
							<a href="{{ url('admins/settings/name') }}" class="btn btn-primary float-right" >Name</a>
						</div>
					@else
						<div class="col">
							Lines already taken by bidders show bidder name. Click to show "TAKEN".
						</div>
						<div class="col-md-4">
							<a href="{{ url('admins/settings/taken') }}" class="btn btn-primary float-right" >Taken</a>
						</div>
					@endif
				</div>

				<hr>

				<div class="card-body my-squash row">
					@if($param_next_bidder_email_on_or_off == 'on')
						<div class="col">
							Email will be sent to notify the next bidder. Click to turn this off.
						</div>
						<div class="col-md-4">
							<a href="{{ url('admins/settings/nextbidderemailoff') }}" class="btn btn-primary float-right" >OFF</a>
						</div>
					@else
						<div class="col">
							Email will not be sent to notify the next bidder. Click to turn this on.
						</div>
						<div class="col-md-4">
							<a href="{{ url('admins/settings/nextbidderemailon') }}" class="btn btn-primary float-right" >ON</a>
						</div>
					@endif
				</div>
				<hr>

				<div class="card-body my-squash row">
					@if($bid_accepted_email_on_or_off == 'on')
						<div class="col">
							Email will be sent to notify bid accepted. Click to turn this off.
						</div>
						<div class="col-md-4">
							<a href="{{ url('admins/settings/bidacceptedemailoff') }}" class="btn btn-primary float-right" >OFF</a>
						</div>
					@else
						<div class="col">
							Email will not be sent to notify bid accepted. Click to turn this on.
						</div>
						<div class="col-md-4">
							<a href="{{ url('admins/settings/bidacceptedemailon') }}" class="btn btn-primary float-right" >ON</a>
						</div>
					@endif
				</div>


			</div>
		</div>
	</div>   
</div>
@endsection