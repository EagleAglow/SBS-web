@extends('layouts.app')

@section('title', '| Add Shift Code')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow"> 
				<div class="card-header"><h5>Add Shift Code</h5>
                </div>
                <div class="card-body">Codes are four characters. Times are formatted HH:MM.
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('shiftcodes.store') }}" accept-charset="UTF-8">
                        @csrf
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Code') }}</label>
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
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Start Time') }}</label>
                            <div class="col-md-6">
                                <input id="begin_time" type="text" class="form-control @error('begin_time') is-invalid @enderror" name="begin_time" required autocomplete="begin_time" autofocus>
                                @error('begin_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('End Time') }}</label>
                            <div class="col-md-6">
                                <input id="end_time" type="text" class="form-control @error('end_time') is-invalid @enderror" name="end_time" required autocomplete="end_time" autofocus>
                                @error('end_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
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
