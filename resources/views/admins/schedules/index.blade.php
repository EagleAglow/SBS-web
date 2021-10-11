@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">
                <div class="card-header">
                    <div class="flex row"><div class="col">Admin - Schedules</div>
                        <div class="col">
                            <div class="text-right">
                                <a href="{{ route('schedules.create') }}"><button type="button"  onclick="return confirm('This only adds a schedule, individual schedule lines will need to be added later. It is better to Clone the existing schedule. Continue?')" class="btn btn-success">Add Schedule</button></a>
                            </div>
                        </div>
                    </div>
                </div>

                @include('flash::message')

                @isset($schedules)
                <!--If record of schedules were found show the list of schedules-->                
                <div class="card-body my-squash">
                    <div class="table-responsive-md">
                        <table class="table table-striped">
                        <thead>
                            <tr>
                            <th class="text-center" scope="col">Title</th>
                            <th class="text-center" scope="col">Start Date</th>
                            <th class="text-center" scope="col">Days</th>
                            <th class="text-center" scope="col">Cycles</th>
                            <th class="text-center" scope="col">Last Date</th>
                            <th class="text-center" scope="col">Approved</th>
                            <th class="text-center" scope="col">Active</th>
                            <th colspan="2" class="text-center" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                                <tr>
                                    <td class="text-center">{{ $schedule->title }}</td>
                                    <td class="text-center">{{ date('d-M-Y', strtotime($schedule->start)) }}</td>
                                    <td class="text-center">{{ $schedule->cycle_days }}</td>
                                    <td class="text-center">{{ $schedule->cycle_count }}</td>

                                    @php
                                    $n = ((($schedule->cycle_count) * ($schedule->cycle_days) ) -1) . ' days';
                                    $last =  date_add( date_create( $schedule->start ), date_interval_create_from_date_string($n) );
                                    @endphp
                                    <td class="text-center">{{ date_format( $last,"d-M-Y") }}</td>
                                    <td class="text-center">
                                    @php
                                        if ($schedule->approved==1){  echo 'Yes';} else { echo 'No';}
                                    @endphp
                                    </td>
                                    <td class="text-center">
                                    @php
                                        if ($schedule->active==1){  echo 'Yes';} else { echo 'No';}
                                    @endphp
                                    </td>
                                    <td>
                                        <div class="col">
                                            <div class="row">
                                                <a href="{{ route('schedules.edit', $schedule->id) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Edit</button></a>
                                            </div>
                                            <div class="row">
                                                <a href="{{ route('schedules.clone', $schedule->id) }}"><button type="button" onclick="return confirm('Clone Schedule: {{$schedule->title}}? This will also copy schedule lines to the new schedule.')" class="btn btn-success btn-my-success">Clone</button></a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col">
                                            <div class="row">
                                                <a href="{{ url('admins/schedulelineset', $schedule->id) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Lines</button></a>
                                            </div>
                                            <div class="row">
                                                <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" class="delete">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                        @csrf
                                                        {{ method_field('DELETE') }}
                                                    <button type="submit" onclick="return confirm('Delete Schedule {{$schedule->title}}?')" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="card-body">
                    <div class="col-auto"><span style="color:red;"><b>There are no schedules.</b></span></div>
                </div>
                @endif
            </div>					
        </div>
    </div>
</div>

@endsection