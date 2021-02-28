<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
use App\Exports\UsersExport;
 
use App\Imports\UsersImport;
use App\Imports\UsersImportWithMail;
 
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
            $welcome = $request['welcome'];
            if (isset($welcome)){
                if ($welcome == 'welcome'){
                    // import and send emails
                    Excel::import(new UsersImportWithMail,$request->file('file'));
                    return redirect('admins/excel-csv-file-users')->with('status', 'The users excel/csv file has been imported, with mail to new users.');
                } else {
                    flash('Sorry, no import - unexpected condition!')->warning()->important();
                    return redirect('admins/excel-csv-file-users');
                }
            } else {
                // import and send emails
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
