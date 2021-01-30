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

					$all_email_to_test_address_on_or_off = App\Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
					$email_test_address = App\Param::where('param_name','email-test-address')->first()->string_value;

				@endphp

				<div class="card-body setting-squash row">
					@if($param_name_or_taken == 'taken')
						<div class="col">
							Lines already taken by bidders show "TAKEN" instead of bidder name. Click to show bidder name.
						</div>
						<div class="col-sm-2">
							<a href="{{ url('admins/settings/name') }}" class="btn btn-primary float-right" >Name</a>
						</div>
					@else
						<div class="col">
							Lines already taken by bidders show bidder name. Click to show "TAKEN".
						</div>
						<div class="col-sm-2">
							<a href="{{ url('admins/settings/taken') }}" class="btn btn-primary float-right" >Taken</a>
						</div>
					@endif
				</div>

				<hr>
				<div class="card-body setting-squash2 row">
					@if($param_next_bidder_email_on_or_off == 'on')
						<div class="col">
							Email will be sent to notify the next bidder. Click to turn this off.
						</div>
						<div class="col-sm-2">
							<a href="{{ url('admins/settings/nextbidderemailoff') }}" class="btn btn-primary float-right" >OFF</a>
						</div>
					@else
						<div class="col">
							Email will not be sent to notify the next bidder. Click to turn this on.
						</div>
						<div class="col-sm-2">
							<a href="{{ url('admins/settings/nextbidderemailon') }}" class="btn btn-primary float-right" >ON</a>
						</div>
					@endif
				</div>

				<hr>
				<div class="card-body setting-squash2 row">
					@if($bid_accepted_email_on_or_off == 'on')
						<div class="col">
							Email will be sent to notify bid accepted. Click to turn this off.
						</div>
						<div class="col-sm-2">
							<a href="{{ url('admins/settings/bidacceptedemailoff') }}" class="btn btn-primary float-right" >OFF</a>
						</div>
					@else
						<div class="col">
							Email will not be sent to notify bid accepted. Click to turn this on.
						</div>
						<div class="col-sm-2">
							<a href="{{ url('admins/settings/bidacceptedemailon') }}" class="btn btn-primary float-right" >ON</a>
						</div>
					@endif
				</div>

				<hr>
				<div class="card-body setting-squash2 row">
					@if($all_email_to_test_address_on_or_off == 'on')
						<div class="col">
							Bidding emails (if they are turned on, above) will be sent ONLY to the test address. Click to turn this off.
						</div>
						<div class="col-sm-2">
							<a href="{{ url('admins/settings/testmailoff') }}" class="btn btn-primary float-right" >OFF</a>
						</div>
					@else
						<div class="col">
							Bidding emails (if they are turned on, above) will be sent to users. After you set a test address, click to turn this on.
						</div>
						<div class="col-sm-2">
							<a href="{{ url('admins/settings/testmailon') }}" class="btn btn-primary float-right" >ON</a>
						</div>
					@endif
				</div>

				<div class="card-body setting-squash2">
					<form method="POST" action="{{ route('admins.settings.testmailsetaddress') }}" accept-charset="UTF-8">
                        @csrf
                        @method('PUT')
						<div class="form-group row">
							<label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Test Address') }}</label>
							<div class="col-sm-6 float-right">
								<input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') ? old('email') : $email_test_address }}" required autocomplete="email">
								@error('email')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
								@enderror
							</div>
							<div class="col-sm-2">
								@if(strlen($email_test_address) > 0)
									<input class="btn btn-primary float-right" type="submit" value="CLEAR">
									<input type="hidden" name="action" value="clear">
								@else
									<input class="btn btn-primary float-right" type="submit" value="SET">
									<input type="hidden" name="action" value="set">
								@endif
							</div>
						</div>
                    </form>
				</div>
			</div>
		</div>
	</div>   
</div>
@endsection