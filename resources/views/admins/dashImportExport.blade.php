@extends('layouts.app')

@section('content')
<!--   REMOVE ME - no longer used -->
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-10">
			<div class="card shadow">
				<div class="card-header">DELETE ME - Admin Dashboard - Import/Export</div>
				@include('flash::message')
				<div class="card-body"><b>Explain things...</b></br><br>
				</div>
				<div class="card-body my-squash">
                    <a href="{{ url('admins/excel-csv-file-schedules') }}" class="btn btn-primary">Schedules Import/Export</a>
                </div>
                <div class="card-body my-squash">
                    <a href="{{ url('admins/excel-csv-file-users') }}" class="btn btn-primary">Users Import/Export</a>
                </div>

                <div class="card-body my-squash">
                    <a href="{{ url('admins/userpurge') }}" class="btn btn-danger">Delete (Almost) All Users</a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection