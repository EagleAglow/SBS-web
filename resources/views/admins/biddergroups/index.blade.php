@extends('layouts.app')

@section('title', '| Bidder Groups')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">
                <div class="card-header">
                    <div class="flex row"><div class="col">Admin - Bidder Groups</div>
                        <div class="col">
                            <div class="text-right">
							<a href="{{ route('biddergroups.create') }}"><button type="button" class="btn btn-success">Add Bidder Group</button></a>
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
							<th class="text-center" scope="col">Code</th>
							<th class="text-center" scope="col">Bidding<br>Order</th>
							<th class="text-center" scope="col">Name</th>
							<th class="text-center" scope="col">Can Bid<br>Line(s)</th>
							<th class="text-center" scope="col">Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($bidder_groups as $bidder_group)
							<tr>
							<td class="text-center">{{ $bidder_group->code }}</td>
							<td class="text-center">{{ $bidder_group->order }}</td>
							<td class="text-center">{{ $bidder_group->name }}</td>
							<td class="text-center">
								@foreach ($bidder_group->roles as $role)
								<div>{{ strtoupper(str_replace('bid-for-','',$role->name)) }}</div>
								@endforeach
							</td>
							<td>
								<div class="row">
									<div style="margin-left:auto;margin-right:auto;">

										<a href="{{ route('biddergroups.edit', $bidder_group->id) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Edit</button></a>
									</div>
									<div style="margin-left:auto;margin-right:auto;">

										<form action="{{ route('biddergroups.destroy', $bidder_group->id) }}" method="POST" class="delete">
											<input type="hidden" name="_method" value="DELETE">
												@csrf
												{{ method_field('DELETE') }}
											<button type="submit" onclick="return confirm('Delete {{$bidder_group->name}}?')" class="btn btn-danger">Delete</button>&nbsp;
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