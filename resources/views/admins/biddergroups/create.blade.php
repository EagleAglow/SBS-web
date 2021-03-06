@extends('layouts.app')

@section('title', '| Add Bidder Group')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Add Bidder Group</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('biddergroups.store') }}" accept-charset="UTF-8">
                        @csrf
                        <div class="form-group row">
                            <label for="code" class="col-md-4 col-form-label text-md-right">{{ __('Code') }}</label>
                            <div class="col-md-6">
                                <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" required autocomplete="code" autofocus>
                                @error('code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="order" class="col-md-4 col-form-label text-md-right">{{ __('Order') }}</label>
                            <div class="col-md-6">
                                <input id="order" type="text" class="form-control @error('order') is-invalid @enderror" name="order" required autocomplete="order" autofocus>
                                @error('order')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

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

                        <div class="form-group row squash">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Bids For lines') }}</label>
                            <div class="col-md-6 my-group">   
                            @foreach ($roles as $role)
                                @if(!(strpos($role->name, 'bid-for-') === false ))
                                <div>
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}">
                                    &nbsp;<label for={{ $role->name }}>{{ strtoupper(str_replace('bid-for-','',$role->name)) }}</label>
                                </div>
                                @endif
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
