@extends('layouts.app')

@section('title', '| Permissions')

@section('content')
<div class="container">
	<div class="row justify-content-start mt-3 mb-3">
		<div class="col-md">
			<h3>Permissions</h3>
		</div>
		<div class="col-md-4 d-flex justify-content-end">
			<a href="{{ route('permissions.create') }}"><button type="button" class="btn btn-success">Add Permission</button></a>
		</div>
	</div>
</div>

@include('flash::message')
 
<div class="container shadow">
	<div class="row justify-content-center">
		<div class="col-md-12"> 
			<div class="table-responsive-sm">      

            <table class="table compact">
					<thead>
						<tr>
						<th class="text-center" scope="col">Permissions</th>
						<th class="text-center" scope="col">Action</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($permissions as $permission)
						<tr>
						<td class="text-center">{{ $permission->name }}</td>
						<td>
							<div class="row">
								<div style="margin-left:auto;margin-right:auto;">
									<a href="{{ route('permissions.edit', $permission->id) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Edit</button></a>
                                </div>
                                <div style="margin-left:auto;margin-right:auto;">
									<form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" class="delete">
										<input type="hidden" name="_method" value="DELETE">
											@csrf
											{{ method_field('DELETE') }}
										<button type="submit" onclick="return confirm('Delete {{$permission->name}}?')" class="btn btn-danger">Delete</button>
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