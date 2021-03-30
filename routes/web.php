<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportExportUsersController; 
use App\Http\Controllers\ImportExportShiftCodesController; 
use App\Http\Controllers\ImportExportSchedulesController; 
use App\Http\Controllers\ExportBidsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

/*   // commented out - remove later, along with demo blade

    // this should probably be in a controller or middleware??
    $demoDev = DB::table('users')->select('email')->where('email','=','dev@demo.com')->first('id');
    $demoSuperuser = DB::table('users')->select('email')->where('email','=','superuser@demo.com')->first('id');
    $demoAdmin = DB::table('users')->select('email')->where('email','=','admin@demo.com')->first('id');
    $demoSupervisor = DB::table('users')->select('email')->where('email','=','supervisor@demo.com')->first('id');
    $demoBidderOne = DB::table('users')->select('email')->where('email','=','bidder_one@demo.com')->first('id');
    $demoBidderTwo = DB::table('users')->select('email')->where('email','=','bidder_two@demo.com')->first('id');
    $demoBidderThree = DB::table('users')->select('email')->where('email','=','bidder_three@demo.com')->first('id');
    $demoBidderFour = DB::table('users')->select('email')->where('email','=','bidder_four@demo.com')->first('id');
    $demoBidderFive = DB::table('users')->select('email')->where('email','=','bidder_five@demo.com')->first('id');
    if ( is_null($demoDev) AND is_null($demoSuperuser) AND is_null($demoAdmin) AND is_null($demoSupervisor) AND is_null($demoBidderOne) AND is_null($demoBidderTwo) AND is_null($demoBidderThree) AND is_null($demoBidderFour) AND is_null($demoBidderFive) ){
        return view('welcome');
    } else {
        return view('demo');
    }

 */

    return view('welcome');
});

// original
//Auth::routes();
// for all features, including 'verify' for email verification
//Auth::routes(['login' => true, 'reset' => true, 'register' => true, 'verify' => true,]);
// see how to use verification in: HomeController

// for only login and password reset...
Auth::routes(['login' => true, 'reset' => true, 'register' => false,]);

// supposedly, to set email verification as a requirement to reach a route, use...
//
//   Route::get('profile', function () {
//        // Only verified users may enter...
//    })->middleware('verified');
//
//  does not seem to work that way....
//
// ---------------------------  or??  ----------------------------------
//  see HomeController for how to use middleware check
// ---------------------------------------------------------------------

// set language
Route::get('/lang/{lang}', 'LocalizationController@index')->name('lang');

// download ICS file
Route::get('/bidders/dash/ics/{id}', 'BidderDashController@ics')->name('bidders.dash.ics');


// future - remove HomeController ??
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/admins/dash', 'AdminDashController@index')->name('admins.dash');
Route::get('/bidders/dash', 'BidderDashController@index')->name('bidders.dash');
Route::get('/supervisors/dash', 'SupervisorDashController@index')->name('supervisors.dash');
Route::get('/superusers/dash', 'SuperuserDashController@index')->name('superusers.dash');


// show progress scoreboard to admins / supervisors
Route::get('users/progress', 'ProgressScoreboardController@index')->name('users.progress');


// permissions (superuser mode)
Route::resource('permissions', 'PermissionController');

// roles (superuser mode)
Route::resource('roles', 'RoleController');

// users (superuser mode)
Route::resource('users', 'UserController');


// superuser group
Route::prefix('superusers')->group(function () {
//    Route::resource('/schedulelines','ScheduleLineController');
    Route::resource('/schedulelines','ScheduleLineController')->except(['show']);
    // handles pagination
    Route::post('/schedulelines/show','ScheduleLineController@show')->name('schedulelines.show');


    Route::resource('/picks','PickController');
});

// admin mode
Route::prefix('admins')->group(function () {
    // manage bidder group code table
    Route::resource('/biddergroups','BidderGroupController');
    // manage liner group code table
    Route::resource('/linegroups','LineGroupController');
    // manage shift code table
    Route::resource('/shiftcodes','ShiftCodeController');
    // a sected schedule with its schedule lines
//    Route::resource('/schedulelineset','ScheduleLineSetController');
    Route::resource('/schedulelineset','ScheduleLineSetController')->except(['create']);
    // handles new scheduleline in selected schedule
    Route::get('/schedulelinset{schedule_id}','ScheduleLineSetController@create')->name('schedulelineset.create');

    // schedules
    Route::resource('/schedules','ScheduleController');
    Route::get('/schedules{id}','ScheduleController@clone')->name('schedules.clone');

    Route::get('/dashBidding', 'AdminDashBiddingController@index')->name('admins.dashBidding');

    // fixup bidding order
    Route::get('/fix','AdminDashBiddingController@fix')->name('admins.fix');
    // erase bids and reset bid order
    Route::get('/reset','AdminDashBiddingController@reset')->name('admins.reset');
    // start bidding
    Route::get('/start','AdminDashBiddingController@start')->name('admins.start');
    // pause bidding
    Route::get('/pause','AdminDashBiddingController@pause')->name('admins.pause');
    // continue bidding
    Route::get('/continue','AdminDashBiddingController@continue')->name('admins.continue');

    // import/export
    Route::get('excel-csv-file-users', [ImportExportUsersController::class, 'index']);
    Route::post('import-excel-csv-file-users', [ImportExportUsersController::class, 'importExcelCSVUsers']);
    Route::get('export-excel-csv-file-users/{slug}', [ImportExportUsersController::class, 'exportExcelCSVUsers']);

    Route::get('excel-csv-file-schedules', [ImportExportSchedulesController::class, 'index']);
    Route::post('import-excel-csv-file-schedules', [ImportExportSchedulesController::class, 'importExcelCSVSchedules']);
    Route::get('export-excel-csv-file-schedules/{slug}', [ImportExportSchedulesController::class, 'exportExcelCSVSchedules']);

    Route::get('export-excel-file-bids/{slug}', [ExportBidsController::class, 'exportExcelBids']);

    Route::get('excel-csv-file-shift-codes', [ImportExportShiftCodesController::class, 'index']);
    Route::post('import-excel-csv-file-shift-codes', [ImportExportShiftCodesController::class, 'importExcelCSVShiftCodes']);
    Route::get('export-excel-csv-file-shift-codes/{slug}', [ImportExportShiftCodesController::class, 'exportExcelCSVShiftCodes']);

    // bulk user delete
    Route::get('/userpurge', 'ImportExportUsersController@userpurge')->name('admins.userpurge');

    // log items table
    Route::get('logitems', 'LogItemController@index')->name('admins.logitems');
    Route::get('logitems/purge', 'LogItemController@purge')->name('admins.logitems.purge');

    // settings
    Route::get('settings', 'SettingController@index')->name('admins.settings');
    // set 'name' or 'taken' on bid page
    Route::get('settings/name', 'SettingController@name')->name('admins.settings.name');
    Route::get('settings/taken', 'SettingController@taken')->name('admins.settings.taken');

    // control email to next bidder
    Route::get('settings/nextbidderemailon', 'SettingController@nextbidderemailon')->name('admins.settings.nextbidderemailon');
    Route::get('settings/nextbidderemailoff', 'SettingController@nextbidderemailoff')->name('admins.settings.nextbidderemailoff');

    // control "bid accepted" email to bidder
    Route::get('settings/bidacceptedemailon', 'SettingController@bidacceptedemailon')->name('admins.settings.bidacceptedemailon');
    Route::get('settings/bidacceptedemailoff', 'SettingController@bidacceptedemailoff')->name('admins.settings.bidacceptedemailoff');

    // control "use test email"
    Route::get('settings/testmailon', 'SettingController@testmailon')->name('admins.settings.testmailon');
    Route::get('settings/testmailoff', 'SettingController@testmailoff')->name('admins.settings.testmailoff');
    Route::post('settings/testmailsetaddress', 'SettingController@testmailsetaddress')->name('admins.settings.testmailsetaddress');

    // control "use test text"
    Route::get('settings/testtexton', 'SettingController@testtexton')->name('admins.settings.testtexton');
    Route::get('settings/testtextoff', 'SettingController@testtextoff')->name('admins.settings.testtextoff');
    Route::post('settings/testtextsetphone', 'SettingController@testtextsetphone')->name('admins.settings.testtextsetphone');

    // control text to next bidder
    Route::get('settings/nextbiddertexton', 'SettingController@nextbiddertexton')->name('admins.settings.nextbiddertexton');
    Route::get('settings/nextbiddertextoff', 'SettingController@nextbiddertextoff')->name('admins.settings.nextbiddertextoff');

    // bulk text/mail
    Route::post('settings/sendbulktext', 'SettingController@sendbulktext')->name('admins.settings.sendbulktext');
    Route::post('settings/sendbulkmail', 'SettingController@sendbulkmail')->name('admins.settings.sendbulkmail');

    // control auto bidding
    Route::get('settings/autobidon', 'SettingController@autobidon')->name('admins.settings.autobidon');
    Route::get('settings/autobidoff', 'SettingController@autobidoff')->name('admins.settings.autobidoff');

});



// show selected schedule to anyone
Route::resource('users/scheduleshow', 'UserScheduleShowController');

// show selected scheduleline to active bidder
Route::resource('/bidder/bid', 'BidByBidderController');
// bidder bidding
Route::post('/bidder/setbid{id}', 'BidByBidderController@setbid')->name('bidder.setbid');

// supervisor bidding
Route::resource('/supervisor/bidfor', 'BidBySupervisorController');
Route::post('/supervisor/setbidfor{id}', 'BidBySupervisorController@setbidfor')->name('supervisor.setbidfor');
