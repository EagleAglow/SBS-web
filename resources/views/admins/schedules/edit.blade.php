@extends('layouts.app')

@section('title', '| Edit Schedule')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-10">
			<div class="card shadow">
				<div class="card-header">Edit Schedule:&nbsp; {{$schedule->title}}</div>
    
                <div class="card-body">
                    <form method="POST" action="{{ route('schedules.update', $schedule->id) }}" accept-charset="UTF-8">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="title" class="col-md-3 col-form-label text-md-right">{{ __('Title') }}</label>
                            <div class="col-md-8">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') ? old('title') : $schedule->title }}" required autocomplete="title" autofocus>
                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="start" class="col-md-3 col-form-label text-md-right">{{ __('Start Date') }}</label>
                            <div class="col-md-6">
                                <input id="start" type="date" class="form-control @error('start') is-invalid @enderror" name="start" value="{{ substr(old('start'), 0 , 10) ? substr(old('start'), 0 , 10) : substr($schedule->start, 0 , 10) }}"  required autocomplete="start" autofocus>
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="cycle_count" class="col-md-3 col-form-label text-md-right">{{ __('Cycles') }}</label>
                            <div class="col-md-2">
                                <input id="cycle_count" type="text" class="form-control @error('cycle_count') is-invalid @enderror" name="cycle_count" value="{{ old('cycle_count') ? old('cycle_count') : $schedule->cycle_count }}" required autocomplete="cycle_count" autofocus>
                                @error('cycle_count')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('Approved') }}</label>
                            <div class="col-md-8 my-group">   
                                @php
                                    echo '<input type="checkbox" name="approved" ';
                                    // count bidders for bidder groups
                                    $groups = App\BidderGroup::where('code','!=','NONE')->orderBy('order')->get();
                                    $bidders_by_group = array();
                                    foreach($groups as $group){
                                        $bidders_by_group[$group->code] = count(App\User::where('bidder_group_id',$group->id)->get());
                                    }
                                    // adjust to line groups, add TRAFFIC to TCOM
                                    $bidders_by_group['TCOM'] = $bidders_by_group['TRAFFIC']+$bidders_by_group['TCOM'];
                                    // adjust to line groups, add TRAFFIC to TNON
                                    $bidders_by_group['TNON'] = $bidders_by_group['TRAFFIC']+$bidders_by_group['TNON'];

                                    // count lines for line groups
                                    $groups = App\LineGroup::where('code','!=','NONE')->orderBy('order')->get();
                                    $lines_by_group = array();
                                    foreach($groups as $group){
                                        $lines_by_group[$group->code] = count(App\ScheduleLine::where('blackout','!=',1)->where('schedule_id',$schedule->id)->where('line_group_id',$group->id)->get());
                                    }

                                    // compare $bidders_by_group to $lines_by_group, skipping group 'TRAFFIC' (by using $lines_by_group)
                                    // see if the number of lines is at least the number of bidders by group

                                    $pass = 'yes';
                                    $flags_by_group = array();
                                    foreach($lines_by_group as $group_code=>$group_count){
                                        if ($group_count < $bidders_by_group[$group_code]){
                                            $pass = 'no';
                                        }
                                    }

                                    // if active, add note to approved checkbox
                                    if ($schedule->active==1){ 
                                        if ($schedule->approved==1){ 
                                            echo ' checked="checked"> &nbsp;&nbsp;&nbsp;<span style="color:red;">Make schedule inactive before unchecking this!</span>'; 
                                        } else { 
                                            echo '> &nbsp;&nbsp;&nbsp;<span style="color:red;">ERROR: This should be checked for an active schedule!</span>'; 
                                        }
                                    } else {
                                        if ($schedule->approved==1){ 
                                            echo ' checked="checked">';
                                        } else { 
                                            if($pass == 'no'){
                                                echo 'disabled="disabled"> &nbsp;&nbsp;&nbsp;<span style="color:red;">A bidder group has more bidders than lines!</span>'; 
                                            } else {
                                                echo '>';
                                            }
                                        }
                                    }
                                @endphp
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('Active') }}</label>
                            <div class="col-md-8 my-group">   
                                @php 
                                    echo '<input type="checkbox" name="active"';
                                    if ($schedule->active==1){ echo ' checked="checked">'; } else {
                                        // has it been approved?
                                        if ($schedule->approved==1){
                                            // check for bidder order setup
                                            $problem_count = 0;
                                            // see if all bidders have seniority value
                                            $bidder_count = 0;
                                            $users = App\User::all();
                                            foreach($users as $user){
                                                // see if user has a bidder role
                                                $user_roles = $user->roles;
                                                $is_bidder = false;
                                                foreach($user_roles as $user_role){
                                                    if ( str_starts_with($user_role->name,'bidder-') ){
                                                        $is_bidder = true;
                                                        break;
                                                    }
                                                }
                                                if ($is_bidder){
//                                              if ($user->can('bid-self')){
                                                    if(!isset($user->bidder_primary_order)){
                                                        $bidder_count = $bidder_count +1;
                                                    }
                                                }
                                            }
                                            if($bidder_count > 0){
                                                $problem_count = $problem_count +1;
                                            }

                                            // see if seniority order and tie-breaker work without duplicates
                                            $bidder_count = 0;
                                            foreach($users as $user){
                                                // see if user has a bidder role
                                                $user_roles = $user->roles;
                                                $is_bidder = false;
                                                foreach($user_roles as $user_role){
                                                    if ( str_starts_with($user_role->name,'bidder-') ){
                                                        $is_bidder = true;
                                                        break;
                                                    }
                                                }
                                                if ($is_bidder){
//                                              if ($user->can('bid-self')){
                                                    if(isset($user->bidder_primary_order)){
                                                        if( count(App\User::where('bidder_primary_order',$user->bidder_primary_order)->where('bidder_secondary_order',$user->bidder_secondary_order)->get()) > 1){
                                                            $bidder_count = $bidder_count +1;
                                                        }
                                                    }
                                                }
                                            }
                                            if($bidder_count > 0){
                                                $problem_count = $problem_count +1;
                                            }
                                            if ($problem_count > 0){
                                                echo 'disabled="disabled"> &nbsp;&nbsp;&nbsp;<span style="color:red;">Check Bidder Order</span>'; 
                                            } else {
                                                echo '>';
                                            }
                                        } else {
                                            echo 'disabled="disabled"> &nbsp;&nbsp;&nbsp;<span style="color:red;">Not Approved Yet</span>'; 
                                        }
                                    }
                                @endphp
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