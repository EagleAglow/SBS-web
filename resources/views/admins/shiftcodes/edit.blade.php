@extends('layouts.app')

@section('title', '| Edit Shift Code')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Edit Shift Code:&nbsp; {{$shift_code->name}}</h5>
                </div>
                <div class="card-body">Codes are four characters.
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('shiftcodes.update', $shift_code->id) }}" accept-charset="UTF-8">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Code') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ? old('name') : $shift_code->name }}" required autocomplete="name" autofocus>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

{{--

                        <div class="form-group row">
                            <label for="begin_time" class="col-md-4 col-form-label text-md-right">{{ __('Shift Begins') }}</label>
                            <div class="col-md-6">
                                <input id="begin_time" type="time" class="form-control @error('begin_time') is-invalid @enderror" name="begin_time" value="{{ old('begin_time') ? old('begin_time') : $shift_code->begin_time }}" required autocomplete="begin_time" autofocus>
                                @error('begin_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="end_time" class="col-md-4 col-form-label text-md-right">{{ __('Shift Ends') }}</label>
                            <div class="col-md-6">
                                <input id="end_time" type="time" class="form-control @error('end_time') is-invalid @enderror" name="end_time" value="{{ old('end_time') ? old('end_time') : $shift_code->end_time }}" required autocomplete="end_time" autofocus>
                                @error('end_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <!-- sets minimum time to current client (system) time -->
                            <input type="text" id="timepicker" data-mintime="now"/>
                        </div>

--}}

                        <div class="form-group row">
                            <label for="begin_time" class="col-md-4 col-form-label text-md-right">{{ __('Shift Begins') }}</label>
                            <div class="col-md-6">
                                <input id="begin_time" type="text" class="form-control @error('begin_time') is-invalid @enderror" name="begin_time" value="{{ old('begin_time') ? old('begin_time') : $shift_code->begin_time }}" required autocomplete="begin_time" autofocus>
                                @error('begin_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="end_time" class="col-md-4 col-form-label text-md-right">{{ __('Shift Ends') }}</label>
                            <div class="col-md-6">
                                <input id="end_time" type="text" class="form-control @error('end_time') is-invalid @enderror" name="end_time" value="{{ old('end_time') ? old('end_time') : $shift_code->end_time }}" required autocomplete="end_time" autofocus>
                                @error('end_time')
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