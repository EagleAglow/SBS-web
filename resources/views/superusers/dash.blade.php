@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-10">
			<div class="card shadow">
                <div class="card-header">Superuser Dashboard</div>
                @include('flash::message')
                <div class="card-body">This role supports future development.  It is not intended for routine use.
                </div>
                <div class="card-body my-squash">
                    <a href="{{ url('permissions') }}" class="btn btn-primary">Permissions</a>
                </div>                
                <div class="card-body my-squash">
                    <a href="{{ url('roles') }}" class="btn btn-primary">Roles</a>
                </div>                
                <div class="card-body my-squash">
                    <a href="{{ url('users') }}" class="btn btn-primary">Users</a>
                </div>

                <div class="card-body my-squash">
                    <a href="{{ url('superusers/picks') }}" class="btn btn-primary">Bidder Tagged Lines</a>
                </div>

            </div>
        </div>
    </div>
    @php
//     echo phpinfo();
    @endphp
</div>
@endsection