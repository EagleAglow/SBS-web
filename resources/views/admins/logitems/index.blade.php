@extends('layouts.app')
 
@section('title', '| Log Items')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">
                <div class="card-header d-print-none">
                    <div class="flex row">
						<div class="col">Admin - Log</div>
						<div class="col">
							<div class="float-right">
								<a href="{{ route('admins.logitems.purge') }}"><button type="button" class="btn btn-danger" onclick="return confirm('This clears all log entries.\n\nAre you sure you want to DELETE ALL ENTRIES?')">Purge All Log Entries</button></a>
							</div>
						</div>
					</div>
				</div>

				@include('flash::message')

				<div class="card-body my-squash">
					<div class="table-responsive-lg">
						<table class="table table-striped">
						<thead>
							<tr>
							<th class="text-center compact" scope="col">Date/Time</th>
							<th class="text-left compact" scope="col">Log Items</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($log_items as $log_item)
							<tr>
							<td class="text-center compact">{{ $log_item->created_at }}</td>
							<td class="text-left compact">{{ $log_item->note }}</td>
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