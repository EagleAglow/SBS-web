@extends('layouts.app')

@section('title', '| Roles')

@section('content')
<div class="container">
	<div class="row justify-content-start mt-3 mb-3">
		<div class="col-md">
			<h3>Roles</h3>
		</div>
		<div class="col-md-4 d-flex justify-content-end">
			<a href="{{ route('roles.create') }}"><button type="button" class="btn btn-success">Add Role</button></a>
		</div>
	</div>
</div>

@include('flash::message')
 
<div class="container shadow">
	<div class="row justify-content-center">
		<div class="col-md-12"> 
			<div class="table-responsive-sm">      
				<table class="table">
					<thead>
						<tr>
						<th class="text-center" scope="col">Role</th>
						<th class="text-center" scope="col">Permissions</th>
						<th class="text-center" scope="col">Action</th>
						</tr>
					</thead>

					<tbody>
						@foreach ($roles as $role)
						<tr>
						<td class="text-center">{{ $role->name }}</td>
                        {{-- Retrieve array of permissions associated to a role and convert to string --}}
						<td class="text-center">{{ implode(', ', $role->permissions()->get()->pluck('name')->toArray()) }}</td>
						<td>
							<div class="row">
								<div style="margin-left:auto;margin-right:auto;">
									<a href="{{ route('roles.edit', $role->id) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Edit</button></a>
                                </div>
                                <div style="margin-left:auto;margin-right:auto;">
									<form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="delete">
										<input type="hidden" name="_method" value="DELETE">
											@csrf
											{{ method_field('DELETE') }}
										<button type="submit" onclick="return confirm('Delete {{$role->name}}?')" class="btn btn-danger">Delete</button>
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
@endsection