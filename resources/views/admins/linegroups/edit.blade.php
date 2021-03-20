@extends('layouts.app')

@section('title', '| Edit Line Group')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Edit Line Group:&nbsp; {{$line_group->code}}</h5>
                </div> 
                <div class="card-body">Line group codes identify the schedule lines for a particular work assignment.
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('linegroups.update', $line_group->id) }}" accept-charset="UTF-8">
                        @csrf
                        @method('PUT')

                        <div class="form-group row">
                            <label for="order" class="col-md-4 col-form-label text-md-right">{{ __('Display Order') }}</label>
                            <div class="col-md-6">
                                <input id="order" type="text" class="form-control @error('order') is-invalid @enderror" name="order" value="{{ old('order') ? old('order') : $line_group->order }}" required autocomplete="order" autofocus>
                                @error('order')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Description') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ? old('name') : $line_group->name }}" required autocomplete="name" autofocus>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
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