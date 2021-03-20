@extends('layouts.app')

@section('title', '| Edit Bidder Group')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Edit Bidder Group:&nbsp; {{$bidder_group->code}}</h5>
                </div>
                <div class="card-body">Bidder group codes identify which bidders can bid for the work assignments identified by line group codes.
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('biddergroups.update', $bidder_group->id) }}" accept-charset="UTF-8">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="code" class="col-md-4 col-form-label text-md-right">{{ __('Code') }}</label>
                            <div class="col-md-6">
                                <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') ? old('code') : $bidder_group->code }}" required autocomplete="code" autofocus>
                                @error('code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="order" class="col-md-4 col-form-label text-md-right">{{ __('Bidding Order') }}</label>
                            <div class="col-md-6">
                                <input id="order" type="text" class="form-control @error('order') is-invalid @enderror" name="order" value="{{ old('order') ? old('order') : $bidder_group->order }}" required autocomplete="order" autofocus>
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
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ? old('name') : $bidder_group->name }}" required autocomplete="name" autofocus>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Bids For Lines') }}</label>
                            <div class="col-md-6 my-group">   
                            @foreach ($roles as $role)
                                @if(!(strpos($role->name, 'bid-for-') === false ))
                                <div>
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                    @php
                                        if ($bidder_group->hasRole($role->name)){ echo ' checked="checked">'; } else { echo '>'; }
                                    @endphp
                                    &nbsp;<label for={{ $role->name }}>{{ strtoupper(str_replace('bid-for-','',$role->name)) }}</label>
                                </div>
                                @endif
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