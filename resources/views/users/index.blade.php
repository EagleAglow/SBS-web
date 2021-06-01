@extends('layouts.app')

@section('title', '| Users')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">
                <div class="card-header">
                    <div class="flex row">
						<div class="col-sm-3">Admin - Users</div>

						<div class="col-sm-6">
								<form action="{{ url('users' ) }}" method="GET">
									@if($my_selection == 'alpha')
										<input type="hidden" name="my_selection" value="seniority">
										@csrf
										<button type="submit" class="btn btn-primary btn-shift float-right">
											<span style="border:1px solid white; border-radius:0.15rem; padding:0 0.4rem;">Alphabetic</span>
											<span style="padding: 0 0.4rem;">Seniority</span>
											<span style="padding: 0 0.4rem;">Bid Order</span><br>
											<span style="padding: 0 0.4rem;">Group-Seniority-Tie-Breaker Order</span>
										</button>
									@else
										@if($my_selection == 'seniority')
											<input type="hidden" name="my_selection" value="bid_order">
											@csrf
											<button type="submit" class="btn btn-primary btn-shift float-right" style="margin-right:1rem;">
												<span style="padding: 0 0.4rem;">Alphabetic</span>
												<span style="border:1px solid white; border-radius:0.15rem; padding:0 0.4rem;">Seniority</span>
												<span style="padding: 0 0.4rem;">Bid Order</span><br>
												<span style="padding: 0 0.4rem;">Group-Seniority-Tie-Breaker Order</span>
											</button>
										@else
											@if($my_selection == 'bid_order')
												<input type="hidden" name="my_selection" value="s/t">
												@csrf
												<button type="submit" class="btn btn-primary btn-shift float-right" style="margin-right:1rem;">
													<span style="padding: 0 0.4rem;">Alphabetic</span>
													<span style="padding: 0 0.4rem;">Seniority</span>
													<span style="border:1px solid white; border-radius:0.15rem; padding:0 0.4rem;">Bid Order</span><br>
													<span style="padding: 0 0.4rem;">Group-Seniority-Tie-Breaker Order</span>
												</button>
											@else
												<input type="hidden" name="my_selection" value="alpha">
												@csrf
												<button type="submit" class="btn btn-primary btn-shift float-right" style="margin-right:1rem;">
													<span style="padding: 0 0.4rem;">Alphabetic</span>
													<span style="padding: 0 0.4rem;">Seniority</span>
													<span style="padding: 0 0.4rem;">Bid Order</span><br>
													<span style="border:1px solid white; border-radius:0.15rem; padding:0 0.4rem;">Group-Seniority-Tie-Breaker Order</span>
												</button>
											@endif
										@endif
									@endif
								</form>

						</div>

						<div class="col">
                            <div class="text-right">
								<a href="{{url('admins/export-excel-bid-order/xlsx')}}"><button type="button" class="btn btn-primary">Export Bid Order</button></a>
                            </div>
                        </div>

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
							@php
								// things to include with pagination 
								$params = array('my_selection'=>$my_selection  );
							@endphp
							{{$users->appends($params)->links() }}    

						</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection                                                       