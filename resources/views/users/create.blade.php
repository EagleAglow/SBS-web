@extends('layouts.app')

@section('title', '| Add User')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Add User</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.store') }}" accept-charset="UTF-8">
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
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" required autocomplete="email">
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
                                <input id="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" autocomplete="phone_number">
                                @error('phone_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="seniority_date" class="col-md-4 col-form-label text-md-right">{{ __('Seniority Date') }}</label>
                            <div class="col-md-6">
                                <input id="seniority_date" type="date" class="form-control @error('seniority_date') is-invalid @enderror" name="seniority_date" autocomplete="seniority_date" autofocus>
                                @error('start')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <!-- bidder_group dropdown  -->
                        <div class="form-group row">
                            <label for="bidder_group_id" class="col-md-4 col-form-label text-md-right">{{ __('Bidder Group') }}</label>
                            <div class="col-md-4">
                                <select required class="form-control" name="bidder_group_id" id="bidder_group_id" >
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" >{{ $group->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row squash">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Roles') }}</label>
                            <div class="col-md-6 my-group">   
                            @foreach ($roles as $role)
                                @if(strpos($role->name, 'bid-for-') === false )
                                    @if(strpos($role->name, 'bidder-active') === false )
                                    <div>
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}">
                                        &nbsp;<label for={{ $role->name }}>{{ ucfirst($role->name) }}</label>
                                    </div>
                                    @endif
                                @endif
                            @endforeach                        
                            </div>
                        </div>
{{--  not using password entry fields
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
                                @enderror
                            </div>
                        </div> 
--}}
                        <div class="form-group row squash">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Welcome E-Mail?') }}</label>
                            <div class="col-md-6 my-group">   
                                <div>
                                    <input type="checkbox" name="welcome" value="welcome" checked="checked">
                                    &nbsp;<label for="welcome">Send password reset</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row squash">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Welcome SMS?') }}</label>
                            <div class="col-md-6 my-group">   
                                <div>
                                    <input type="checkbox" name="sms" value="sms" checked="checked">
                                    &nbsp;<label for="SMS">Send password reset</label>
                                </div>
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