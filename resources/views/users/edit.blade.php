@extends('layouts.app')

@section('title', '| Edit User')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Edit User: {{$user->name}}</h5>
                </div>
					<div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user->id) }}" accept-charset="UTF-8">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ? old('name') : $user->name }}" required autocomplete="name" autofocus>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') ? old('email') : $user->email }}" required autocomplete="email">
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="phone_number" class="col-md-4 col-form-label text-md-right">{{ __('Phone Number') }}</label>
                            <div class="col-md-6">
                                <input id="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number') ? old('phone_number') : $user->phone_number }}" autocomplete="phone_number">
                                @error('phone_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <!-- bidder_group dropdown  -->
                        <div class="form-group row">
                            <label for="bidder_group_id" class="col-md-4 col-form-label text-md-right">{{ __('Group') }}</label>
                            <div class="col-md-4">
                                <select required class="form-control" name="bidder_group_id" id="bidder_group_id" >
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ $group->id == $user->bidder_group_id ? 'selected':'' }}>{{ $group->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="bid_order" class="col-md-4 col-form-label text-md-right">{{ __('Bid Order') }}</label>
                            <div class="col-md-6">
                                <input id="bid_order" type="text" class="form-control @error('bid_order') is-invalid @enderror" name="bid_order" value="{{ old('bid_order') ? old('bid_order') : $user->bid_order }}" autocomplete="bid_order" autofocus>
                                @error('bid_order')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="seniority_date" class="col-md-4 col-form-label text-md-right">{{ __('Seniority Order') }}</label>
                            <div class="col-md-6">
                                <input id="seniority_date" type="date" class="form-control @error('seniority_date') is-invalid @enderror" name="seniority_date" value="{{ old('seniority_date') ? old('seniority_date') : $user->seniority_date }}" autocomplete="seniority_date" autofocus>
                                @error('seniority_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Roles') }}</label>
                            <div class="col-md-6 my-group">   
                            @foreach ($roles as $role)
                                @if(strpos($role->name, 'bid-for-') === false )
                                    @if(strpos($role->name, 'flag-') === false )
                                        @if(strpos($role->name, 'bidder-active') === false )
                                            <div>
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                                @php
                                                    if ($user->hasRole($role->name)){ echo ' checked="checked">'; } else { echo '>'; }
                                                @endphp
                                                &nbsp;<label for={{ $role->name }}>{{ ucfirst($role->name) }}</label>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            @endforeach                        
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Flags') }}</label>
                            <div class="col-md-6 my-group">   
                            @foreach ($roles as $role)
                                @if(strpos($role->name, 'flag-') !== false )
                                        <div>
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                            @php
                                                if ($user->hasRole($role->name)){ echo ' checked="checked">'; } else { echo '>'; }
                                            @endphp
                                            &nbsp;<label for={{ $role->name }}>{{ ucfirst(substr($role->name,5)) }}</label>
                                        </div>
                                @endif
                            @endforeach                        
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('New Password') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password">
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password_confirmation" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" autocomplete="new-password_confirmation">
                                @error('password_confirmation')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @else
                                    <span style="font-size:0.75rem;color:red;">Leave both password fields blank for no change</span>
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