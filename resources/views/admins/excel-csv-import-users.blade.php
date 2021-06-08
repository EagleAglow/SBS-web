@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header">Admin - Users Import/Export</div>

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
                    Import file must use the format of the export file, including header row. Do not use codes/names for groups/roles that are not already in the
                    system. Email address is the index key. Existing admin users are not modified. Other existing entries are updated. No users are deleted. New
                    users get a random password (and password reset email if the box is checked). <span style="color:red;">For names/email with extended (non-ASCII)
                    characters, use UTF-8 encoding!</span> One way to do that is to open the CSV file with Windows Notepad, then: File | Save As | Encoding | UTF-8.
                </div>

                <div class="card-body my-squash">
                <form id="excel-csv-import-form" method="POST"  action="{{ url('admins/import-excel-csv-file-users') }}" accept-charset="utf-8" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col">
                            <input type="checkbox" name="welcome" value="welcome" >
                                    &nbsp;<label for="welcome">Send password reset email for new users. <span style="color:red"><b>Are the addresses correct?</b></span></label>
                        </div>
                    </div>    

                    <div class="row">
                        <div class="col">
                            <input type="checkbox" name="sms" value="sms" >
                                    &nbsp;<label for="sms">Send password reset SMS for new users. <span style="color:red"><b>Are the phone numbers correct?</b></span></label>
                        </div>
                    </div>    

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
                            Check box to send email to new users, choose "csv" or "xls" file for import. Wait for the filename to appear, then submit.
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
                            For major changes to the user list (e.g., reset all passwords), this removes all users except those with admin/superuser roles. 
                            BEFORE you do this, MAKE SURE to export a CSV file to build the replacement import list with the correct format.
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