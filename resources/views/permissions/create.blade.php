@extends('layouts.app')

@section('title', '| Add Shift')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Add Permission</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('permissions.store') }}" accept-charset="UTF-8">
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
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Roles') }}</label>
                            <div class="col-md-6 my-group">
{{--                        //Show roles, only if any roles exist   --}}                             
                            @if(!$roles->isEmpty())   
                                @foreach ($roles as $role) 
                                    <div>
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}">
                                        &nbsp;<label for={{ $role->name }}>{{ ucfirst($role->name) }}</label>
                                    </div>
                                @endforeach  
                            @endif                      
                            </div>
                        </div>
                        <input class="btn btn-primary float-right" type="submit" value="Add">
                        <a href="{{url()->previous()}}" class="btn btn-success">Cancel</a>
                    </form>
                </div>
			</div>
        </div>
	</div>
</div>

@endsection
