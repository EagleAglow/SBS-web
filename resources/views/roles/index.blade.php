@extends('layouts.app')

@section('title', '| Roles')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">
                <div class="card-header">
                    <div class="flex row"><div class="col">Roles</div>
                        <div class="col">
                            <div class="text-right">
							<a href="{{ route('roles.create') }}"><button type="button" class="btn btn-success">Add Role</button></a>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="card-body"><span style="color:red;font-weight:bold;">
					Bidding roles are automatically modified (with permissions) when line groups are changed. 
					This page is a "left over" from development. Roles can be inspected, but 
					this page should never actually be used to change anything!</span>
				</div>
                @include('flash::message')
                <div class="card-body my-squash">
                    <div class="table-responsive-md">
                        <table class="table table-striped">
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
	</div>   
</div>
@endsection