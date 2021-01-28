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
				@endphp

				<div class="card-body squash row">
					@if($param_name_or_taken == 'taken')
						<div class="col">
							Lines already taken by bidders show "TAKEN" instead of bidder name. Click to show bidder name.
						</div>
						<div class="col-md-4">
							<a href="{{ url('admins/settings/name') }}" class="btn btn-primary float-right" >Name</a>
							<a href="#" class="btn btn-outline-primary btn-my-setting disabled float-right">Taken</button></a>
						</div>
					@else
						<div class="col">
							Lines already taken by bidders show bidder name. Click to show "TAKEN".
						</div>
						<div class="col-md-4">
							<a href="#" class="btn btn-outline-primary btn-my-setting disabled float-right">Name</button></a>
							<a href="{{ url('admins/settings/taken') }}" class="btn btn-primary float-right" >Taken</a>
						</div>
					@endif
				</div>

				<hr>

				<div class="card-body my-squash row">
					@if($param_name_or_taken == 'taken')
						<div class="col">
							Lines already taken by bidders show "TAKEN" instead of bidder name. Click to show bidder name.
						</div>
						<div class="col-md-4">
							<a href="{{ url('admins/settings/name') }}" class="btn btn-primary float-right" >Name</a>
							<a href="#" class="btn btn-outline-primary btn-my-setting disabled float-right">Taken</button></a>
						</div>
					@else
						<div class="col">
							Lines already taken by bidders show bidder name. Click to show "TAKEN".
						</div>
						<div class="col-md-4">
							<a href="#" class="btn btn-outline-primary btn-my-setting disabled float-right">Name</button></a>
							<a href="{{ url('admins/settings/taken') }}" class="btn btn-primary float-right" >Taken</a>
						</div>
					@endif
				</div>

			</div>
		</div>
	</div>   
</div>
@endsection