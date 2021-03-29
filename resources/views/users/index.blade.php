@extends('layouts.app')

@section('title', '| Users')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">
                <div class="card-header">
                    <div class="flex row">
						<div class="col-md-5">Admin - Users</div>

						@if($my_selection == 'alpha')
							<div class="col">
								<div class="row">
									<form action="{{ url('users' ) }}" method="GET">
										<input type="hidden" name="my_selection" value="seniority">
										@csrf
										<button type="submit" class="btn btn-secondary btn-shift float-right">&nbsp;Show Seniority Order</button>
									</form>
								</div>
								<div class="row" style="font-size:0.8rem;font-weight:500;">Showing alphabetic order</div>
							</div>
						@else
							@if($my_selection == 'seniority')
							<div class="col">
								<div class="row">
									<form action="{{ url('users' ) }}" method="GET">
										<input type="hidden" name="my_selection" value="bid_order">
										@csrf
										<button type="submit" class="btn btn-secondary btn-shift float-right">&nbsp;Show Bidding Order</button>
									</form>
								</div>
								<div class="row" style="font-size:0.8rem;font-weight:500;">&nbsp;Showing seniority order</div>
							</div>
							@else
								@if($my_selection == 'bid_order')
								<div class="col">
									<div class="row">
										<form action="{{ url('users' ) }}" method="GET">
											<input type="hidden" name="my_selection" value="s/t">
											@csrf
											<button type="submit" class="btn btn-secondary btn-shift float-right">&nbsp;Show Seniority/Tie-breaker Order</button>
										</form>
									</div>
									<div class="row" style="font-size:0.8rem;font-weight:500;">&nbsp;&nbsp;Showing bidding order</div>
								</div>
								@else
								<div class="col">
									<div class="row">
										<form action="{{ url('users' ) }}" method="GET">
											<input type="hidden" name="my_selection" value="alpha">
											@csrf
											<button type="submit" class="btn btn-secondary btn-shift float-right">&nbsp;Show Alphabetic Order</button>
										</form>
									</div>
									<div class="row" style="font-size:0.8rem;font-weight:500;">&nbsp;Showing seniority/tie-breaker order</div>
								</div>
								@endif
							@endif
						@endif

						<div class="col">
                            <div class="text-right">
								<a href="{{ route('users.create') }}"><button type="button" class="btn btn-success">Add User</button></a>
                            </div>
                        </div>
					</div>
				</div>

                @include('flash::message')

                <div class="card-body my-squash">
                    <div class="table-responsive-md">
                        <table class="table table-striped">
						<thead>
							<tr>
							<th class="text-center" scope="col"><span style="padding:0 0.5rem;border:1px solid #ccc;border-radius:0.25rem;">Name</span><br>Email / Number</th>
							<th class="text-center" scope="col"><span style="padding:0 0.5rem;border:1px solid #ccc;border-radius:0.25rem;">Bid Group</span><br>Roles</th>
							<th class="text-center" scope="col"><span style="padding:0 0.5rem;border:1px solid #ccc;border-radius:0.25rem;">Seniority&nbsp;Date</span><br>Tie&nbsp;Breaker</th>
							<th class="text-center" scope="col">Bid Order</th>
							<th class="text-center" scope="col">Has Bid</th>
							<th class="text-center" scope="col">Active Bidder</th>
							<th class="text-center" scope="col">Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($users as $user)
							<tr>
							<td class="text-center"><span style="padding:0 0.5rem;border:1px solid #ccc;border-radius:0.25rem;">{{ $user->name }}</span><br>{{ $user->email }} {{ $user->phone_number }}</td>
							<td class="text-center">
								@if(isset($user->bidder_group)) 						
								<span style="padding:0 0.5rem;border:1px solid #ccc;border-radius:0.25rem;">{{ $user->bidder_group->code }}</span><br>
								@endif
								{{ implode(', ', $user->roles()->get()->pluck('name')->toArray()) }}
							</td>
							<td class="text-center"><span style="padding:0 0.5rem;border:1px solid #ccc;border-radius:0.25rem;">{{ $user->seniority_date }}</span><br>{{ $user->bidder_tie_breaker }}</td>
							<td class="text-center">{{ $user->bid_order }}</td>

							@php
							echo '<td class="text-center">';
								if ($user->has_bid){
									echo '<b>YES</b></td>';
								} else {
									echo 'No</td>';
								}

								echo '<td class="text-center">';
								if ($user->hasRole('bidder-active')){
									echo '<b>YES</b></td>';
								} else {
									echo 'No</td>';
								}
							@endphp

							<td>
								<div class="row">
									<div style="margin-left:auto;margin-right:auto;">
										<a href="{{ route('users.edit', $user->id) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Edit</button></a>
									</div>
									<div style="margin-left:auto;margin-right:auto;">
										<form action="{{ route('users.destroy', $user->id) }}" method="POST" class="delete">
											<input type="hidden" name="_method" value="DELETE">
												@csrf
												{{ method_field('DELETE') }}
											<button type="submit" onclick="return confirm('Delete {{$user->name}}?')" class="btn btn-danger">Delete</button>
										</form>
									</div>
								</div>
							</td>
							</tr>
							@endforeach
						</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection                                                       