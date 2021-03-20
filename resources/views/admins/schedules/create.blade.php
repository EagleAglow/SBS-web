@extends('layouts.app')

@section('title', '| Add Schedulee')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-10">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Add Schedule</h5>
                <h6>{{ __('Schedules are blocks of one or more 56 day cycles. Schedules must not overlap, or have gaps between them. This page only adds a new schedule title.  Use CLONE on the index page to make a new schedule.')}}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('schedules.store') }}" accept-charset="UTF-8">
                        @csrf
                        <div class="form-group row">
                            <label for="title" class="col-md-3 col-form-label text-md-right">{{ __('Title') }}</label>
                            <div class="col-md-8">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" required autocomplete="title" autofocus>
                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="start" class="col-md-3 col-form-label text-md-right">{{ __('Start Date') }}</label>
                            <div class="col-md-4">
                                <input id="start" type="date" class="form-control @error('start') is-invalid @enderror" name="start" autocomplete="start" autofocus>
                                @error('start')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="cycle_count" class="col-md-3 col-form-label text-md-right">{{ __('Cycles') }}</label>
                            <div class="col-md-2">
                                <input id="cycle_count" type="text" class="form-control @error('cycle_count') is-invalid @enderror" name="cycle_count" autocomplete="cycle_count" autofocus>
                                @error('cycle_count')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('Approved') }}</label>
                            <div class="col-md-3 my-group">   
                                <input type="checkbox" name="approved" >
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('Active') }}</label>
                            <div class="col-md-3 my-group">   
                                <input type="checkbox" name="active" >
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
