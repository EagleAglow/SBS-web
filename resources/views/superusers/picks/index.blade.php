@extends('layouts.app')

@section('title', '| Bidder Tagged Lines - Need to limit list to just bidders')

@section('content')

<div class="container shadow">
	<div class="row justify-content-center">
		<div class="col-md-12"> 
			<div class="table-responsive-md">      
				<table class="table table-striped">
					<thead>
						<tr>
						<th class="text-center" scope="col">Bidder / Group</th>
						<th class="text-center" scope="col">Tagged Lines</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($users as $user)
						<tr>
						<td class="text-center">{{ $user->name }} / {{ $user->bidder_group->code }}</td>
						<td class="text-center">{{ implode(', ', $user->schedule_Lines()->orderBy('rank')->get()->pluck('line')->toArray()) }}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>   
</div>
@endsection                                                       