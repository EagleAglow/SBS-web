@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header">Admin - Schedules Import/Export</div>

                @include('flash::message')

                @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            Save a copy of all schedule lines as "csv" file, with column header row.
                        </div>
                        <div class="col justify-content-end">
                            <a href="{{url('admins/export-excel-csv-file-schedules/csv')}}" class="btn btn-success pull-right">&nbsp;Export CSV&nbsp;</a>
                        </div>
                    </div>
                </div>
 
                <hr>
                <div class="card-body my-squash">
                    <div class="row">
                        <div class="col-md-9">
                            Save a copy of all schedule lines as an "xlsx" file, with column header row.
                        </div>
                        <div class="col justify-content-end">
                            <a href="{{url('admins/export-excel-csv-file-schedules/xlsx')}}" class="btn btn-success pull-right">Export Excel</a>
                        </div>
                    </div>
                </div>

                <hr>                
                <div class="card-body my-squash">
                    Import file must use the format of the export file, including header row. Unrecognized codes/names for shifts/groups/schedules 
                    will cause the line to be placed in an "Import Errors" schedule.
                    The schedule/group/line combination is the index key. Existing entries are updated. Others are added. None are deleted.</div>
                <div class="card-body my-squash">
                    <form id="excel-csv-import-form" method="POST"  action="{{ url('admins/import-excel-csv-file-schedules') }}" accept-charset="utf-8" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <input type="file" name="file" placeholder="Choose file">
                                </div>
                                @error('file')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>    

                        <div class="row">
                            <div class="col-md-9">
                                First, choose a "csv" or "xls" file for import. Wait for the filename to appear, then submit.
                            </div>          
                            <div class="col justify-content-end">
                                <button type="submit" class="btn btn-primary" id="submit" onclick="$('#cover-spin').show(0)">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Submit&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</button>
                            </div>
                        </div>

                    </form>
                </div>


            </div>
        </div>
    </div>
</div>  
@endsection