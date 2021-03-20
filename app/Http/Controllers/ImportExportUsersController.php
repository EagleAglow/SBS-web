<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
use App\Exports\UsersExport;
 
use App\Imports\UsersImport;
use App\Imports\UsersImportWithSMS;
use App\Imports\UsersImportWithMail;
use App\Imports\UsersImportWithMailSMS;
 
use Maatwebsite\Excel\Facades\Excel;

use Auth;
 
use App\User;
 
class ImportExportUsersController extends Controller
{


// should use middle war instead of check on admin role - FIX ME LATER

    /**
    * @return \Illuminate\Support\Collection
    */
    public function index()
    {
       if (Auth::user()->hasRole('admin')){
           return view('admins/excel-csv-import-users');
        } else {
            abort('401');
        }
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function importExcelCSVUsers(Request $request) 
    {
        if (Auth::user()->hasRole('admin')){
            $validatedData = $request->validate([
            'file' => 'required',
            ]);
            $choice = 0;
            $welcome = $request['welcome'];
            $sms = $request['sms'];
            if ($welcome == 'welcome'){ $choice = $choice +1;}
            if ($sms == 'sms'){ $choice = $choice +2;}
            switch ($choice){
                case 1:
                    // import and send emails
                    Excel::import(new UsersImportWithMail,$request->file('file'));
                    return redirect('admins/excel-csv-file-users')->with('status', 'The users excel/csv file has been imported, with mail to new users.');
                    break;
                case 2:
                    // import and send SMS
                    Excel::import(new UsersImportWithSMS,$request->file('file'));
                    return redirect('admins/excel-csv-file-users')->with('status', 'The users excel/csv file has been imported, with SMS to new users.');
                    break;
                case 3:
                    // import and send both emails and SMS
                    Excel::import(new UsersImportWithMailSMS,$request->file('file'));
                    return redirect('admins/excel-csv-file-users')->with('status', 'The users excel/csv file has been imported, with mail and SMS to new users.');
                    break;
                default:
                    // import only
                    Excel::import(new UsersImport,$request->file('file'));
                    return redirect('admins/excel-csv-file-users')->with('status', 'The users excel/csv file has been imported.');
            }
        } else {
            abort('401');
        }
    }
 
    /**
    * @return \Illuminate\Support\Collection
    */
    public function exportExcelCSVUsers($slug) 
    {
        if (Auth::user()->hasRole('admin')){
            return Excel::download(new UsersExport, 'users.'.$slug);
        } else {
            abort('401');
        }
    }


    public function userpurge()
    {
        if (Auth::user()->hasRole('admin')){

            // delete all users excepty admins and superusers
            $count = 0;
            $users = User::get();
            foreach($users as $user){
                if ((!$user->hasRole('superuser')) and (!$user->hasRole('admin')) ){
                    $user->delete();
                    $count = $count +1;
                }
            }
            flash($count . ' users were removed.')->success();
//            return view('admins.dashImportExport');
            return view('admins.excel-csv-import-users'); 
        } else {
            abort('401');
        }
    }

}
