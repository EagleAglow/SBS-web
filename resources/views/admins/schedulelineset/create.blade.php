@extends('layouts.app')

@section('title', '| Add Schedule line')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card mt-5 shadow">
				<div class="card-header"><h5>Add Schedule Line</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('schedulelineset.store', $schedule_id) }}" accept-charset="UTF-8">
                        @csrf
                        <!-- schedule dropdown  -->
                        <div class="form-group row">
                            <label for="schedule_id" class="col-md-3 col-form-label text-md-right">{{ __('Schedule') }}</label>
                            <div class="col-md-8">
                                <select required class="form-control" name="schedule_id" id="schedule_id">
                                    @foreach($schedules as $schedule)
                                        <option value="{{ $schedule->id }}" {{ $schedule->id == $schedule_id ? 'selected':'disabled="disabled"' }}>{{ $schedule->title }}</option>

                                    @endforeach
                                </select>
                            </div>
                        </div> 
                        <!-- line_group dropdown  -->
                        <div class="form-group row">
                            <label for="line_group_id" class="col-md-3 col-form-label text-md-right">{{ __('Group') }}</label>
                            <div class="col-md-8">
                                <select required class="form-control" name="line_group_id" id="line_group_id">
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> 

                        <div class="form-group row">
                            <label for="line" class="col-md-3 col-form-label text-md-right">{{ __('Line') }}</label>
                            <div class="col-md-8">
                                <input id="line" type="text" class="form-control @error('line') is-invalid @enderror" name="line" required autocomplete="line" autofocus>
                                @error('line')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="comment" class="col-md-3 col-form-label text-md-right">{{ __('Comment') }}</label>
                            <div class="col-md-8">
                                <input id="comment" type="text" class="form-control @error('comment') is-invalid @enderror" name="comment" autocomplete="comment" autofocus>
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
                                <input type="checkbox" name="blackout" >
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('NEXUS') }}</label>
                            <div class="col-md-3 my-group">   
                                <input type="checkbox" name="nexus" >
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('Barge') }}</label>
                            <div class="col-md-3 my-group">   
                                <input type="checkbox" name="barge" >
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">{{ __('Offsite') }}</label>
                            <div class="col-md-3 my-group">   
                                <input type="checkbox" name="offsite" >
                            </div>
                        </div>

                        @php
                        $max_days = App\Schedule::where('id',$schedule_id)->first()->cycle_days;
                        for ($n = 1; $n <= $max_days; $n++) {
                            $d = 'day_' . substr(('000' . $n),-3);
                            echo '<!-- ' . $d . ' dropdown  -->';
                            echo '<div class="form-group row">';
                            echo '<label for=" . $d . " class="col-md-3 col-form-label text-md-right">';
                            echo 'Day ' . $n;
                            echo '</label><div class="col-md-6">';
                            echo '<select required class="form-control" name="' . $d . '" id="' . $d . '">';
                            foreach($shifts as $shift){
                                if ($shift->name!='????'){  
                                    if ($shift->name=='----'){
                                        $cwt = 'Day Off'; } else {
                                        $cwt = $shift->name . '  (' . $shift->begin_short . ' - ' . $shift->end_short . ')';
                                    }
                                    echo '<option value="' . $shift->id . '">' . $cwt . '</option>';
                                }
                            }
                            echo '</select></div></div>';
                        }
                        @endphp

                        <input class="btn btn-primary float-right" type="submit" value="Add">
                    </form>
                </div>
			</div>
        </div>
	</div>
</div>

@endsection
