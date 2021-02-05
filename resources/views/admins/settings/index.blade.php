@extends('layouts.app')
 
@section('title', '| Settings')

@section('content')
<div class="container">
	<div class="row justify-content-center"> 
		<div class="col-md-12">
			<div class="card shadow">
                <div class="card-header">Admin - Settings</div>

				@include('flash::message')

				@php
					$param_name_or_taken = App\Param::where('param_name','name-or-taken')->first()->string_value;
					$param_next_bidder_email_on_or_off = App\Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
					$param_bid_accepted_email_on_or_off = App\Param::where('param_name','bid-accepted-email-on-or-off')->first()->string_value;

					$param_all_email_to_test_address_on_or_off = App\Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
					$email_test_address = App\Param::where('param_name','email-test-address')->first()->string_value;

					$param_next_bidder_text_on_or_off = App\Param::where('param_name','next-bidder-text-on-or-off')->first()->string_value;
					$param_all_text_to_test_phone_on_or_off = App\Param::where('param_name','all-text-to-test-phone-on-or-off')->first()->string_value;
					$text_test_phone = App\Param::where('param_name','text-test-phone')->first()->string_value;

					$param_auto_bid_on_or_off = App\Param::where('param_name','autobid-on-or-off')->first()->string_value;
				@endphp

				<div class="card-body setting-squash row">
					@if($param_next_bidder_email_on_or_off == 'on')
						<div class="col-sm-9">
							<b>Next bidder email is on.</b> Click to turn this off.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/nextbidderemailoff') }}" class="btn btn-primary float-right" >OFF</a>
						</div>
					@else
						<div class="col-sm-9">
							Next bidder will not be notified by email. Click to turn this on.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/nextbidderemailon') }}" class="btn btn-primary float-right" >ON</a>
						</div>
					@endif
				</div>

				<div class="card-body setting-squash2 row">
					@if($param_bid_accepted_email_on_or_off == 'on')
						<div class="col-sm-9">
							<b>Bidder will get schedule by email</b>. Click to turn this off.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/bidacceptedemailoff') }}" class="btn btn-primary float-right" >OFF</a>
						</div>
					@else
						<div class="col-sm-9">
							Bidder will not get schedule by email. Click to turn this on.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/bidacceptedemailon') }}" class="btn btn-primary float-right" >ON</a>
						</div>
					@endif
				</div>

				<div class="card-body setting-squash2 row">
					@if($param_all_email_to_test_address_on_or_off == 'on')
						<div class="col-sm-9">
							Bid emails (if enabled above) are <b>sent <u>ONLY</u> to the test address</b>. Click to send to users.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/testmailoff') }}" class="btn btn-primary float-right" >SEND TO USERS</a>
						</div>
					@else
						<div class="col-sm-9">
							Bid emails (if enabled above) are <span style="color:red;"><b>sent to users</b></span>. Click to send bid emails to a test address (below).
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/testmailon') }}" class="btn btn-primary float-right" >SEND TO TEST</a>
						</div>
					@endif
				</div>

				<div class="card-body setting-squash2">
					<form method="POST" action="{{ route('admins.settings.testmailsetaddress') }}" accept-charset="UTF-8">
                        @csrf
                        @method('POST')
						<div class="form-group row">
							<label for="email" class="col-md-3 col-form-label text-md-right">{{ __('Test Address') }}</label>
							<div class="col-sm-6 float-right">
								<input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') ? old('email') : $email_test_address }}" required autocomplete="email">
								@error('email')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
								@enderror
							</div>
							<div class="col-sm-3">
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

				<hr>
				<div class="card-body setting-squash2 row">
					@if($param_next_bidder_text_on_or_off == 'on')
						<div class="col-sm-9">
							<b>Next bidder text is on.</b> Click to turn this off.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/nextbiddertextoff') }}" class="btn btn-primary float-right" >OFF</a>
						</div>
					@else
						<div class="col-sm-9">
							Next bidder will not be notified by text. Click to turn this on.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/nextbiddertexton') }}" class="btn btn-primary float-right" >ON</a>
						</div>
					@endif
				</div>

				<div class="card-body setting-squash2 row">
					@if($param_all_text_to_test_phone_on_or_off == 'on')
						<div class="col-sm-9">
							Text messages (if enabled above) are <b>sent <u>ONLY</u> to the test phone</b>. Click to send to users.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/testtextoff') }}" class="btn btn-primary float-right" >SEND TO USERS</a>
						</div>
					@else
						<div class="col-sm-9">
							Text messages (if enabled above) are <span style="color:red;"><b>sent to users</b></span>. Click to send texts to a test phone (below).
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/testtexton') }}" class="btn btn-primary float-right" >SEND TO TEST</a>
						</div>
					@endif
				</div>

				<div class="card-body setting-squash2">
					<form method="POST" action="{{ route('admins.settings.testtextsetphone') }}" accept-charset="UTF-8">
                        @csrf
                        @method('POST')
						<div class="form-group row">
							<label for="phone" class="col-md-3 col-form-label text-md-right">{{ __('Test Phone') }}</label>
							<div class="col-sm-6 float-right">
								<input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') ? old('phone') : $text_test_phone }}" required autocomplete="phone">
								@error('phone')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
								@enderror
							</div>
							<div class="col-sm-3">
								@if(strlen($text_test_phone) > 0)
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

				<hr>
				<div class="card-body setting-squash2 row">
					@if($param_name_or_taken == 'taken')
						<div class="col-sm-9">
							When not filtered, bidding page <b>lines show TAKEN</b> (by bidders). Click to show bidder name.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/name') }}" class="btn btn-primary float-right" >SHOW NAME</a>
						</div>
					@else
						<div class="col-sm-9">
							When not filtered, bidding page <b>lines show bidder name</b>. Click to show "TAKEN".
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/taken') }}" class="btn btn-primary float-right" >SHOW TAKEN</a>
						</div>
					@endif
				</div>

{{--  suppress this until I figure out how to actually do it!
				<hr>
				<div class="card-body setting-squash2 row">
					@if($param_auto_bid_on_or_off == 'on')
						<div class="col-sm-9">
							<b>Auto-bidding is on.</b> A bidder automatically gets an available line, with their lowest number tag.
							 If none of the available lines are tagged, bidding is not automatic. Click to turn off.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/autobidoff') }}" class="btn btn-primary float-right" >OFF</a>
						</div>
					@else
						<div class="col-sm-9">
							Auto-bidding is off, bidding is not automatic. Click to turn on.
						</div>
						<div class="col-sm-3">
							<a href="{{ url('admins/settings/autobidon') }}" class="btn btn-primary float-right" >ON</a>
						</div>
					@endif
				</div>
--}}
			</div>
		</div>
	</div>   
</div>
@endsection