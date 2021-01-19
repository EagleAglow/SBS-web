@extends('layouts.app')

@section('title', '| Add User')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Add Role</h5>
                </div>
                <div class="card-body">

                <form method="POST" action="{{ route('roles.store') }}" accept-charset="UTF-8">
                        @csrf
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" required autocomplete="name" autofocus>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Permissions') }}</label>
                            <div class="col-md-6 my-group">   
                            @foreach ($permissions as $permission)
                                <div>
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}">
                                    &nbsp;<label for={{ $permission->name }}>{{ ucfirst($permission->name) }}</label>
                                </div>
                            @endforeach                        
                            </div>
                        </div>

                        <input class="btn btn-primary float-right" type="submit" value="Add">
                    </form>
                </div>
			</div>
        </div>
	</div>
</div>

@endsection
