@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start mt-0 mb-3">
        <div class="col-md">
            <h4>{{ __('Schedule Lines') }}</h4>
            <h6>{{ __('Schedule lines are 56 days long')}}</h6>
        </div>
		<div class="col-md-4 d-flex justify-content-end">
			<a href="{{ route('schedulelines.create') }}"><button type="button" class="btn btn-success">Add Schedule Line</button></a>
		</div>
    </div>
</div>
 

@include('flash::message')
 


@isset($schedule_lines)
    <div class="container shadow">
        <div class="row justify-content-center">
            <div class="col-sm-12">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><div class="pagination-text"></div>
                        @php
                            // days to display
                            $delta = '7';
                            // sanitize
                            if (!isset($page)){ $page = 1; }
                            if (isset($cycles)){
                                if (($cycles <= 0 ) || ( 5 <= $cycles )){
                                    $cycles = 1;
                                }
                            } else { $cycles = 1; }

                            if (isset($first_day)){
                                if (($first_day <= 0 ) || ( 57 <= $first_day )){
                                    $first_day = 1;
                                }
                            } else { $first_day = 1; }

                            if (isset($last_day)){
                                if (($last_day <= 9 ) || ( 57 <= $last_day )){
                                    $last_day = $first_day + $delta - 1;
                                }
                            } else {
                                $last_day = $first_day + $delta - 1;
                            }
                            
                            if ( $last_day > 56 ){
                                    $last_day = 56;
                                    $first_day = $last_day - $delta + 1;                                
                            }
                        @endphp
                        <tr>
                        <th class="text-center btn-shift" scope="col">

                        @if ($first_day > 1)
                        <form action="{{ route('schedulelines.show') }}" method="POST">
                            <input type="hidden" name="schedule_title" value="{{ $schedule_title }}">
                            <input type="hidden" name="start_date" value="{{ $start_date }}">
                            <input type="hidden" name="cycles" value="{{ $cycles }}">
                            <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                            <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                            <input type="hidden" name="page" value="{{ $page }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-shift">&#8656;&nbsp;Earlier</button>
                        </form>
                        @else
                        &nbsp;
                        @endif
                        </th>

                        @php
                            if ($first_day>1){
                                $offset = '+' . ($first_day - 1) . ' days';
                            }

                            for ($d = $first_day; $d <= $last_day; $d++) {
                                echo '<th class="text-center" scope="col">' . '&nbsp;</th>';
                            }

                        @endphp    
                        <th class="text-center btn-shift" scope="col">
                        @if ($last_day < 56)
                        <form action="{{ route('schedulelines.show') }}" method="POST">
                            <input type="hidden" name="schedule_title" value="{{ $schedule_title }}">
                            <input type="hidden" name="start_date" value="{{ $start_date }}">
                            <input type="hidden" name="cycles" value="{{ $cycles }}">
                            <input type="hidden" name="first_day" value="{{ $first_day + $delta }}">
                            <input type="hidden" name="last_day" value="{{ $last_day + $delta }}">
                            <input type="hidden" name="page" value="{{ $page }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary">Later&nbsp;&#8658;</button>
                        </form>
                        @else
                        &nbsp;
                        @endif
                        </th>
                        </tr>

                        @php
                            echo '<!-- cycle dayes  -->';

                            if ($first_day>1){
                                $offset = '+' . ($first_day - 1) . ' days';
                            }


                            echo '<!-- weekday names  -->';
                            echo '<tr><th class="text-center" scope="col">Bid Line</th>';


                            if ($first_day>1){
                                $offset = '+' . ($first_day - 1) . ' days';
                                }

                            for ($d = $first_day; $d <= $last_day; $d++) {
                                echo '<th class="text-center" scope="col">' . 'Day<br>' . $d . '</th>';
                            }


                            echo '<th class="text-center" scope="col">Action</th></tr>';
                        @endphp
                        </thead>

                        <tbody>
                            <div class = "container">
                            @foreach($schedule_lines as $schedule_line)
                                <tr>
                                    <td class="text-center">
                                        @php
                                        // handle blackout
                                        if ($schedule_line->blackout == 1){
                                            echo '<div class="blackout"><span class="blackout">BLACK OUT<br></span><span class="line-number">';
                                        } else {
                                            echo '<div><span class="line-number">';
                                        }
                                        @endphp
                                        {{ $schedule_line->line }}</span>
                                        <span class="line-group">{{ $schedule_line->line_group->code }}</span>

                                        @php
                                        // bidder info 
                                        if (isset($schedule_line->user_id)){
                                            echo '<div class="line-bidder">' . App\User::findOrFail($schedule_line->user_id)->name . '</div>';
                                        }

                                        // comment
                                        $comment = $schedule_line->comment; 
                                        if ($schedule_line->nexus == 1){
                                            $comment = $comment . ', NEXUS';
                                        }
                                        if ($schedule_line->barge == 1){
                                            $comment = $comment . ', BARGE';
                                        }
                                        if ($schedule_line->offsite == 1){
                                            $comment = $comment . ', OFFSITE';
                                        }
                                        echo '<div class="line-comment">' . $comment . '</div>';
                                        echo '<div class="line-comment">' . $schedule_line->schedule->title . '</div>';
                                        @endphp
                                        </div>
                                    </td>

                                    @php
                                        $stamp = strtotime( $start_date );
                                        if ($first_day>1){
                                            $offset = '+' . ($first_day - 1) . ' days';
                                            $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);
                                        }

                                        for ($d = $first_day; $d <= $last_day; $d++) {
                                            $day = date('D', $stamp);
                                            if ($day=='Sat' OR $day=='Sun') {
                                                echo '<td class="text-center line-code week-end" scope="col">';
                                            } else {
                                                echo '<td class="text-center line-code" scope="col">';
                                            }
                                            echo App\ShiftCode::find($schedule_line->getCode($d))->shift_divs . '</td>';

                                            $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                        }
                                    @endphp
                                    <td>
                                        <div class="row">
                                            <div style="margin-left:auto;margin-right:auto;">

                                                <a href="{{ route('schedulelines.edit', $schedule_line->id) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Edit</button></a>
                                            </div>
                                            <div style="margin-left:auto;margin-right:auto;">

                                                <form action="{{ route('schedulelines.destroy', $schedule_line->id) }}" method="POST" class="delete">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                        @csrf
                                                        {{ method_field('DELETE') }}
                                                    <button type="submit" onclick="return confirm('Delete Line {{$schedule_line->line}}?')" class="btn btn-danger btn-my-delete">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                @endforeach
                            </div>

                            @php
                                // things to include with pagination 
                                $params = array('schedule_title'=>$schedule_title, 'start_date'=>$start_date,
                                    'cycles'=>$cycles, 'first_day'=>$first_day,'last_day'=>$last_day);
                            @endphp
                            {{$schedule_lines->appends($params)->links() }}    
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endisset


@endsection
