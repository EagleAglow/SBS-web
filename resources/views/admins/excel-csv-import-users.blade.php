@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header">Users - Import/Export Excel/CSV Files</div>

                @include('flash::message')

                @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            Save a copy of all users as "csv" file, with column header row. 
                        </div>
                        <div class="col justify-content-end">
                            <a href="{{url('admins/export-excel-csv-file-users/csv')}}" class="btn btn-success pull-right">&nbsp;Export CSV&nbsp;</a>
                        </div>
                    </div>
                </div>

                <hr>                
                <div class="card-body my-squash">
                    <div class="row">
                        <div class="col-md-9">
                            Save a copy of all users as an "xlsx" file, with column header row.
                        </div>
                        <div class="col justify-content-end">
                            <a href="{{url('admins/export-excel-csv-file-users/xlsx')}}" class="btn btn-success pull-right">Export Excel</a>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="card-body my-squash">
                    Import file must use the format of the export file, including header row. Do not use codes/names for groups/roles that are not already in the system.
                    Email address is the index key. Existing admin users are not modified. Other existing entries are updated. No users are deleted. For password reset of
                    existing users, or to add new users, there must be text (6 or more characters) in the fifth column, with a heading of "PASSWORD". Otherwise, Passwords
                    won't change, nor new users be added. For names/email with extended ASCII characters, use the CSV format! 
                </div>

                <div class="card-body my-squash">
                <form id="excel-csv-import-form" method="POST"  action="{{ url('admins/import-excel-csv-file-users') }}" accept-charset="utf-8" enctype="multipart/form-data">
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


                <hr>                
                <div class="card-body my-squash">
                    <div class="row">
                        <div class="col-md-9">
                            For major changes to the user list, you can remove all users, except those with admin/superuser roles, then import new users. 
                            BEFORE you do this, MAKE SURE to export a CSV file to build the replacement list with the correct format.
                        </div>
                        <div class="col justify-content-end">
                            <a href="{{ url('admins/userpurge') }}" class="btn btn-danger" onclick="$('#cover-spin').show(0)">Purge Users</a>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>  
@endsection