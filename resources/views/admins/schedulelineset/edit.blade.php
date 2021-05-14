@extends('layouts.app')

@section('title', '| Edit Schedule Line')

@section('content') 

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Edit Schedule Line:&nbsp; {{$schedule_line->line}}</h5>
                </div>
					<div class="card-body">
                    <form method="POST" action="{{ route('schedulelineset.update', $schedule_line->id) }}" accept-charset="UTF-8">
                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="line" class="col-md-3 col-form-label text-md-right">{{ __('Line') }}</label>
                            <div class="col-md-8">
                                <input id="line" type="text" class="form-control @error('line') is-invalid @enderror" name="line" value="{{ old('line') ? old('line') : $schedule_line->line }}" required autocomplete="line" autofocus>
                                @error('line')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <!-- schedule dropdown  -->
                        <div class="form-group row">
                            <label for="schedule_id" class="col-md-3 col-form-label text-md-right">{{ __('Schedule') }}</label>
                            <div class="col-md-8">
                                <select required class="form-control" name="schedule_id" id="schedule_id">
                                    @foreach($schedules as $schedule)
                                        <option value="{{ $schedule->id }}" {{ $schedule->id == $schedule_line->schedule_id ? 'selected':'' }}>{{ $schedule->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- line_group dropdown  -->
                        <div class="form-group row">
                            <label for="line_group_id" class="col-md-3 col-form-label text-md-right">{{ __('Group') }}</label>
                            <div class="col-md-8">
                                <select required class="form-control" name="line_group_id" id="line_group_id" >
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ $group->id == $schedule_line->line_group_id ? 'selected':'' }}>{{ $group->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="comment" class="col-md-3 col-form-label text-md-right">{{ __('Comment') }}</label>
                            <div class="col-md-8">
                                <input id="comment" type="text" class="form-control @error('comment') is-invalid @enderror" name="comment" value="{{ old('comment') ? old('comment') : $schedule_line->comment }}" required autocomplete="comment" autofocus>
                                @error('comment')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('Blackout') }}</label>
                            <div class="col-md-3 my-group">   
                                <input type="checkbox" name="blackout"
                                @php 
                                    if ($schedule_line->blackout==1){ echo ' checked="checked">'; } else { echo '>'; }
                                @endphp
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('NEXUS') }}</label>
                            <div class="col-md-3 my-group">   
                                <input type="checkbox" name="nexus"
                                @php 
                                    if ($schedule_line->nexus==1){ echo ' checked="checked">'; } else { echo '>'; }
                                @endphp
                        </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('Barge') }}</label>
                            <div class="col-md-3 my-group">   
                                <input type="checkbox" name="barge"
                                @php 
                                    if ($schedule_line->barge==1){ echo ' checked="checked">'; } else { echo '>'; }
                                @endphp
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('Offsite') }}</label>
                            <div class="col-md-3 my-group">   
                                <input type="checkbox" name="offsite" 
                                @php 
                                    if ($schedule_line->offsite==1){ echo ' checked="checked">'; } else { echo '>'; }
                                @endphp
                            </div>
                        </div>

                        @php
                        for ($n = 1; $n <= 56; $n++) {
                            $d = 'day_' . substr(('00' . $n),-2);
                            echo '<!-- ' . $d . ' dropdown  -->';
                            echo '<div class="form-group row">';
                            echo '<label for="day_01" class="col-md-3 col-form-label text-md-right">';
                            echo 'Day ' . $n;
                            echo '</label><div class="col-md-6">';
                            echo '<select required class="form-control" name="' . $d . '" id="' . $d . '">';
                            foreach($shifts as $shift){
                                if ($shift->name=='----'){ $cwt = 'Day Off'; } else {
                                    $cwt = $shift->name . '  (' . $shift->begin_short . ' - ' . $shift->end_short . ')';
                                }
                                if ($shift->id==$schedule_line->$d){
                                    $v = '<option value="' . $shift->id . '" selected>';
                                } else {
                                    $v = '<option value="' . $shift->id . '">';
                                }


                                echo $v . $cwt . '</option>';
                            }
                            echo '</select></div></div>';
                        }
                        @endphp

                        <input class="btn btn-primary float-right" type="submit" value="Save">
                    </form>
					</div>

			</div>
        </div>
	</div>
</div>

@endsection