@extends('layouts.app')

@section('title', '| Edit Role')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Edit Role:&nbsp; {{$role->name}}</h5>
                </div>
					<div class="card-body">

                    <form method="POST" action="{{ route('roles.update', $role->id) }}" accept-charset="UTF-8">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ? old('name') : $role->name }}" required autocomplete="name" autofocus>
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
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                    @php 
                                    if ($role->hasPermissionTo($permission->name)){ echo ' checked="checked">'; } else { echo '>'; }
                                    @endphp
                                    &nbsp;<label for={{ $permission->name }}>{{ $permission->name }}</label>
                                </div>
                            @endforeach                        
                            </div>
                        </div>

                        <input class="btn btn-primary float-right" type="submit" value="Save">
                    </form>
					</div>

			</div>
        </div>
	</div>
</div>

@endsection