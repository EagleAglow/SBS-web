@extends('layouts.app')

@section('title', '| Shifts')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">
                <div class="card-header">
                    <div class="flex row"><div class="col">Admin - Shift Codes</div>
                        <div class="col">
                            <div class="text-right">
								<a href="{{ route('shiftcodes.create') }}"><button type="button" class="btn btn-success">Add Shift Code</button></a>
                            </div>
                        </div>
                    </div>
                </div>

                @include('flash::message')

                <div class="card-body my-squash">
                    <div class="table-responsive-md">
                        <table class="table table-striped">
						<table class="table table-striped">
						<thead>
							<tr>
							<th class="text-center" scope="col">Shift Code</th>
							<th class="text-center" scope="col">From</th>
							<th class="text-center" scope="col">To</th>
							<th class="text-center" scope="col">Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($shift_codes as $shift_code)
							<tr>
							<td class="text-center">{{ $shift_code->name }}</td>
							<td class="text-center">{{ $shift_code->begin_short }}</td>
							<td class="text-center">{{ $shift_code->end_short }}</td>
							<td>
								<div class="row">
									<div style="margin-left:auto;margin-right:auto;">

										<a href="{{ route('shiftcodes.edit', $shift_code->id) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Edit</button></a>
									</div>
									<div style="margin-left:auto;margin-right:auto;">

										<form action="{{ route('shiftcodes.destroy', $shift_code->id) }}" method="POST" class="delete">
											<input type="hidden" name="_method" value="DELETE">
												@csrf
												{{ method_field('DELETE') }}
											<button type="submit" onclick="return confirm('Delete {{$shift_code->name}}?')" class="btn btn-danger">Delete</button>
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