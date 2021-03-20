<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\BidderGroup;

class UsersTableSeeder extends Seeder
{

    private function myBidderFill($nm,$em,$pwd,$bg,$seniority)  // name, email, password, bidder_group, seniority_date
    {
/* old code
        // expects bidder_group codes: none (no role assigned), demo (role=bid-for-demo), tsu (role=bid-for-tsu),
        // irpa (role=bid-for-irpa),oidp (role=bid-for-oidp), traffic (roles=bid-for-traffic)

        if ($bg=='traffic'){
            $bg_id = App\BidderGroup::select('id')->where('code','TRAFFIC')->first()->id;
        } else {
            if ($bg=='none'){
                $bg_id = App\BidderGroup::select('id')->where('code',strtoupper($bg))->first()->id;
            } else {
                $bg_id = App\BidderGroup::select('id')->where('code',strtoupper($bg))->first()->id;
            }
        }
*/

        // assign bid group id from bid group code
        $bidder_group = App\BidderGroup::select('id')->where('code',strtoupper($bg))->first();
        if (isset($bidder_group)){
            $bg_id = $bidder_group->id;
        } else {
            $bg_id = App\BidderGroup::select('id')->where('code','NONE')->first()->id;
        }

        $newUser = User::create([
        'name' => $nm,
        'email' => $em,
        'password' => Hash::make($pwd),
        // set verified time so this system does not send out verfication emails - remove if you want them to verify
        // also change overall system setting - see: Auth::routes  in web.php
        'email_verified_at' => '2000-01-01',
        'bidder_group_id' => $bg_id,
        'seniority_date' => $seniority,
        // accept defaults for... 
        //   'has_bid' = false
        //   'bid_order' = null   
        //   'bidder_tie_breaker' = null

        ]);

/* old code
        if ($bg=='traffic'){
            $bg_role = 'bid-for-tnon'; 
            $newUser->assignRole($bg_role);
            $bg_role = 'bid-for-tcom'; 
            $newUser->assignRole($bg_role);
        } else {
            if ($bg=='none'){
                // no role is assigned
            } else {
                $bg_role = 'bid-for-' . $bg; 
                $newUser->assignRole($bg_role);
            }
        }
*/

        // assign bidding roles based on bidding group
        if (isset($bidder_group)){
            $role_names = $bidder_group->getRoleNames();
            if (count($role_names)>0){
                foreach ($role_names as $role_name) {
                    $newUser->assignRole($role_name); //Assigning role to user
                }
            }
        }

    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the database so we don't repeat the seed
        DB::table('users')->delete();

        // create seed users

        // add demo users - bidders
        UsersTableSeeder::myBidderFill('Demo Bidder One','one@demo.com','password','demo','1995-01-01');
        UsersTableSeeder::myBidderFill('Demo Bidder Two','two@demo.com','password','demo','1995-06-01');
        UsersTableSeeder::myBidderFill('Demo Bidder Three','three@demo.com','password','demo','1995-06-01');
        UsersTableSeeder::myBidderFill('Demo Bidder Four','four@demo.com','password','demo','1995-06-01');
        UsersTableSeeder::myBidderFill('Demo Bidder Five','five@demo.com','password','demo','1996-07-05');

        // add demo users - admin / supervisor / superuser
        $newUser = User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            // verified time so this does not send out verfication emails
            'email_verified_at' => '2000-01-01',
            // bidder group: NONE
            'bidder_group_id' => 10,  // NONE = no bidding group/role
        ]);
        $newUser->assignRole('admin');

        $newUser = User::create([
            'name' => 'Demo Superuser',
            'email' => 'superuser@demo.com',
            'password' => Hash::make('password'),
            // verified time so this does not send out verfication emails
            'email_verified_at' => '2000-01-01',
            // bidder group: NONE
            'bidder_group_id' => 10,  // NONE = no bidding group/role
        ]);
        $newUser->assignRole('superuser');

        $newUser = User::create([
            'name' => 'Demo Supervisor',
            'email' => 'supervisor@demo.com',
            'password' => Hash::make('password'),
            // verified time so this does not send out verfication emails
            'email_verified_at' => '2000-01-01',
            // bidder group: NONE
            'bidder_group_id' => 10,  // NONE = no bidding group/role
            ]);
        $newUser->assignRole('supervisor');

/////////////////////////////////////////////////////////////////////////
        // for development 
/////////////////////////////////////////////////////////////////////////
$newUser = User::create([
            'name' => 'Developer',
            'email' => 'dev@demo.com',
            'password' => Hash::make('password'),
            // verified time so this does not send out verfication emails
            'email_verified_at' => '2000-01-01',
            // bidder group: NONE
            'bidder_group_id' => 10,  // NONE = no bidding group/role
        ]);
        $newUser->assignRole('supervisor');
        $newUser->assignRole('admin'); 
        $newUser->assignRole('superuser');
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////

        // add bogus bidders

        // TSU 
        UsersTableSeeder::myBidderFill("D'AGNILLO, Gilbert","Gilbert.D'Agnillo@Demo.com",'password','TSU','1986-02-03');
        UsersTableSeeder::myBidderFill("MCCRAY, Hayfa","Hayfa.Mccray@Demo.com",'password','TSU','1986-02-20');
        UsersTableSeeder::myBidderFill("O'HAGAN, Dustin","Dustin.O'Hagan@Demo.com",'password','TSU','1986-03-09');
        UsersTableSeeder::myBidderFill("CARSON, Eric","Eric.Carson@Demo.com",'password','TSU','1986-03-09');
        UsersTableSeeder::myBidderFill("PAGE, Merrill","Merrill.Page@Demo.com",'password','TSU','1986-04-12');
        UsersTableSeeder::myBidderFill("CORDIER, Valentin","Valentin.Cordier@Demo.com",'password','TSU','1986-04-29');
        UsersTableSeeder::myBidderFill("ROWE, Lester","Lester.Rowe@Demo.com",'password','TSU','1989-05-24');
        UsersTableSeeder::myBidderFill("ESTRADA, John","John.Estrada@Demo.com",'password','TSU','1989-05-25');
        UsersTableSeeder::myBidderFill("BOWMAN, Baxter","Baxter.Bowman@Demo.com",'password','TSU','1989-06-11');
        UsersTableSeeder::myBidderFill("WALTON, Lillith","Lillith.Walton@Demo.com",'password','TSU','1989-06-11');
        UsersTableSeeder::myBidderFill("TYSON, Lance","Lance.Tyson@Demo.com",'password','TSU','1989-06-11');
        UsersTableSeeder::myBidderFill("HOWARD, Cruz","Cruz.Howard@Demo.com",'password','TSU','1990-08-09');
        // FEDEX
        UsersTableSeeder::myBidderFill("MALONE, Rhoda","Rhoda.Malone@Demo.com",'password','FEDEX','1986-08-26');
        UsersTableSeeder::myBidderFill("HODGES, Mary","Mary.Hodges@Demo.com",'password','FEDEX','1986-09-12');
        UsersTableSeeder::myBidderFill("MENDOZA, Desirae","Desirae.Mendoza@Demo.com",'password','FEDEX','1989-06-11');
        UsersTableSeeder::myBidderFill("MADDOX, Plato","Plato.Maddox@Demo.com",'password','FEDEX','1986-10-16');
        // DET
        UsersTableSeeder::myBidderFill("BALL, Grady","Grady.Ball@Demo.com",'password','DET','1986-11-02');
        UsersTableSeeder::myBidderFill("POIRIER, Félix","Félix.Poirier@Demo.com",'password','DET','1986-11-19');
        UsersTableSeeder::myBidderFill("DAVIS, Lev","Lev.Davis@Demo.com",'password','DET','1986-12-06');
        UsersTableSeeder::myBidderFill("HEAD, Claudia","Claudia.Head@Demo.com",'password','DET','1989-06-11');
        UsersTableSeeder::myBidderFill("GERARD, Maxime","Maxime.Gerard@Demo.com",'password','DET','1987-01-09');
        UsersTableSeeder::myBidderFill("MILLET, Renaud","Renaud.Millet@Demo.com",'password','DET','1989-05-24');
        UsersTableSeeder::myBidderFill("NGUYEN, Libby","Libby.Nguyen@Demo.com",'password','DET','1989-05-25');
        UsersTableSeeder::myBidderFill("BLANCHARD, Renaud","Renaud.Blanchard@Demo.com",'password','DET','1989-06-28');
        UsersTableSeeder::myBidderFill("WARNER, Enzo","Enzo.Warner@Demo.com",'password','DET','1989-06-28');
        UsersTableSeeder::myBidderFill("VINCENT, Melodie","Melodie.Vincent@Demo.com",'password','DET','1993-11-16');
        UsersTableSeeder::myBidderFill("SOLIS, Aaron","Aaron.Solis@Demo.com",'password','DET','1993-12-16');
        UsersTableSeeder::myBidderFill("PENNINGTON, Jenette","Jenette.Pennington@Demo.com",'password','DET','1989-06-11');
        UsersTableSeeder::myBidderFill("BOONE, Reagan","Reagan.Boone@Demo.com",'password','DET','1994-01-03');
        UsersTableSeeder::myBidderFill("LECLERCQ, Azalia","Azalia.Leclercq@Demo.com",'password','DET','1994-01-19');
        UsersTableSeeder::myBidderFill("DURAND, Lorenzo","Lorenzo.Durand@Demo.com",'password','DET','1994-02-19');
        UsersTableSeeder::myBidderFill("LAINE, Killian","Killian.Laine@Demo.com",'password','DET','1987-07-15');
        // OIDP
        UsersTableSeeder::myBidderFill("COLLIN, Davy","Davy.Collin@Demo.com",'password','OIDP','1987-11-28');
        UsersTableSeeder::myBidderFill("BULLOCK, Alexis","Alexis.Bullock@Demo.com",'password','OIDP','1987-11-29');
        UsersTableSeeder::myBidderFill("BENTLEY, Cade","Cade.Bentley@Demo.com",'password','OIDP','1987-12-15');
        UsersTableSeeder::myBidderFill("KIRK, Vielka","Vielka.Kirk@Demo.com",'password','OIDP','1987-12-15');
        UsersTableSeeder::myBidderFill("SUTTON, Lance","Lance.Sutton@Demo.com",'password','OIDP','1989-06-11');
        UsersTableSeeder::myBidderFill("HESS, Diana","Diana.Hess@Demo.com",'password','OIDP','1987-12-15');
        UsersTableSeeder::myBidderFill("MUELLER, Ciaran","Ciaran.Mueller@Demo.com",'password','OIDP','1994-01-19');
        UsersTableSeeder::myBidderFill("WALL, Florence","Florence.Wall@Demo.com",'password','OIDP','1994-01-19');
        UsersTableSeeder::myBidderFill("ELLIS, Chaney","Chaney.Ellis@Demo.com",'password','OIDP','1994-01-19');
        UsersTableSeeder::myBidderFill("HAMILTON, Heidi","Heidi.Hamilton@Demo.com",'password','OIDP','1994-01-19');
        UsersTableSeeder::myBidderFill("ROCHE, Gilbert","Gilbert.Roche@Demo.com",'password','OIDP','1994-01-19');
        UsersTableSeeder::myBidderFill("ROLLAND, Théo","Théo.Rolland@Demo.com",'password','OIDP','1994-02-05');
        UsersTableSeeder::myBidderFill("PHILIPPE, Julien","Julien.Philippe@Demo.com",'password','OIDP','1994-02-05');
        UsersTableSeeder::myBidderFill("LOTT, Janna","Janna.Lott@Demo.com",'password','OIDP','1994-02-05');
        UsersTableSeeder::myBidderFill("ROY, Baptiste","Baptiste.Roy@Demo.com",'password','OIDP','1994-06-05');
        // IRPA
        UsersTableSeeder::myBidderFill("FAURE, Victor","Victor.Faure@Demo.com",'password','IRPA','1988-01-01');
        UsersTableSeeder::myBidderFill("MORTON, Sybill","Sybill.Morton@Demo.com",'password','IRPA','1988-01-02');
        UsersTableSeeder::myBidderFill("COLLIN, Dylan","Dylan.Collin@Demo.com",'password','IRPA','1988-01-03');
        UsersTableSeeder::myBidderFill("NICHOLS, Malachi","Malachi.Nichols@Demo.com",'password','IRPA','1988-01-04');
        UsersTableSeeder::myBidderFill("MALLET, Mathéo","Mathéo.Mallet@Demo.com",'password','IRPA','1988-01-05');
        UsersTableSeeder::myBidderFill("GIRARD, Baptiste","Baptiste.Girard@Demo.com",'password','IRPA','1988-01-06');
        UsersTableSeeder::myBidderFill("CRUZ, Celeste","Celeste.Cruz@Demo.com",'password','IRPA','1988-01-06');
        UsersTableSeeder::myBidderFill("ABBOTT, Katelyn","Katelyn.Abbott@Demo.com",'password','IRPA','1988-01-08');
        UsersTableSeeder::myBidderFill("BRUN, Malik","Malik.Brun@Demo.com",'password','IRPA','1988-01-09');
        UsersTableSeeder::myBidderFill("MOREAU, Simon","Simon.Moreau@Demo.com",'password','IRPA','1988-01-10');
        UsersTableSeeder::myBidderFill("RIOS, Ulysses","Ulysses.Rios@Demo.com",'password','IRPA','1988-01-11');
        UsersTableSeeder::myBidderFill("COOK, Lyle","Lyle.Cook@Demo.com",'password','IRPA','1988-01-12');
        UsersTableSeeder::myBidderFill("WILLIAM, Farrah","Farrah.William@Demo.com",'password','IRPA','1988-01-13');
        // TANDC = Traffic and commercial
        UsersTableSeeder::myBidderFill("SNIDER, Ursula","Ursula.Snider@Demo.com",'password','REGULAR','1988-06-19');
        UsersTableSeeder::myBidderFill("GILLIAM, Angela","Angela.Gilliam@Demo.com",'password','REGULAR','1988-07-06');
        UsersTableSeeder::myBidderFill("WARNER, Vincent","Vincent.Warner@Demo.com",'password','REGULAR','1988-07-23');
        UsersTableSeeder::myBidderFill("BRAY, William","William.Bray@Demo.com",'password','REGULAR','1988-07-23');
        UsersTableSeeder::myBidderFill("LAINE, Yohan","Yohan.Laine@Demo.com",'password','REGULAR','1988-08-26');
        UsersTableSeeder::myBidderFill("MCMAHON, May","May.Mcmahon@Demo.com",'password','REGULAR','1989-07-15');
        UsersTableSeeder::myBidderFill("MALDONADO, Reuben","Reuben.Maldonado@Demo.com",'password','REGULAR','1988-09-12');
        UsersTableSeeder::myBidderFill("CARTER, Kristen","Kristen.Carter@Demo.com",'password','REGULAR','1988-10-16');
        UsersTableSeeder::myBidderFill("HARRINGTON, Yvette","Yvette.Harrington@Demo.com",'password','REGULAR','1988-11-02');
        UsersTableSeeder::myBidderFill("BULLOCK, Jonas","Jonas.Bullock@Demo.com",'password','REGULAR','1988-11-02');
        UsersTableSeeder::myBidderFill("WEISS, Ramona","Ramona.Weiss@Demo.com",'password','REGULAR','1988-11-02');
        UsersTableSeeder::myBidderFill("FRANCOIS, Anthony","Anthony.Francois@Demo.com",'password','REGULAR','1988-11-19');
        UsersTableSeeder::myBidderFill("SLOAN, Moses","Moses.Sloan@Demo.com",'password','REGULAR','1988-12-06');
        UsersTableSeeder::myBidderFill("MARKS, Dalton","Dalton.Marks@Demo.com",'password','REGULAR','1988-12-23');
        UsersTableSeeder::myBidderFill("LEFEVRE, Yohan","Yohan.Lefevre@Demo.com",'password','REGULAR','1989-01-09');
        UsersTableSeeder::myBidderFill("ANDRE, Aaron","Aaron.Andre@Demo.com",'password','REGULAR','1989-01-26');
        UsersTableSeeder::myBidderFill("WOOTEN, Ashely","Ashely.Wooten@Demo.com",'password','REGULAR','1989-02-12');
        UsersTableSeeder::myBidderFill("MOREL, Noë","Noë.Morel@Demo.com",'password','REGULAR','1989-02-12');
        UsersTableSeeder::myBidderFill("ROCHA, Lara","Lara.Rocha@Demo.com",'password','REGULAR','1989-02-12');
        UsersTableSeeder::myBidderFill("COLLET, Maxence","Maxence.Collet@Demo.com",'password','REGULAR','1989-03-01');
        UsersTableSeeder::myBidderFill("NOEL, Amber","Amber.Noel@Demo.com",'password','REGULAR','1989-03-18');
        UsersTableSeeder::myBidderFill("GOOD, Maxine","Maxine.Good@Demo.com",'password','REGULAR','1989-04-04');
        UsersTableSeeder::myBidderFill("ELLIS, Fleur","Fleur.Ellis@Demo.com",'password','REGULAR','1989-05-08');
        UsersTableSeeder::myBidderFill("BARLOW, Elliott","Elliott.Barlow@Demo.com",'password','REGULAR','1989-05-08');
        UsersTableSeeder::myBidderFill("GAINES, Morgan","Morgan.Gaines@Demo.com",'password','REGULAR','1989-05-08');
        UsersTableSeeder::myBidderFill("COLLIER, Dacey","Dacey.Collier@Demo.com",'password','REGULAR','1989-06-07');
        UsersTableSeeder::myBidderFill("NASH, Marvin","Marvin.Nash@Demo.com",'password','REGULAR','1989-06-08');
        UsersTableSeeder::myBidderFill("FRANK, Zelda","Zelda.Frank@Demo.com",'password','REGULAR','1989-06-09');
        UsersTableSeeder::myBidderFill("KLINE, Laurel","Laurel.Kline@Demo.com",'password','REGULAR','1989-06-10');
        UsersTableSeeder::myBidderFill("GRANT, Conan","Conan.Grant@Demo.com",'password','REGULAR','1989-06-11');
        UsersTableSeeder::myBidderFill("FRANCOIS, Tristan","Tristan.Francois@Demo.com",'password','REGULAR','1989-06-11');
        UsersTableSeeder::myBidderFill("PHILIPPE, Bruno","Bruno.Philippe@Demo.com",'password','REGULAR','1989-06-28');
        UsersTableSeeder::myBidderFill("MORPHEUS, Tim","Tim.Morpheus@Demo.com",'password','REGULAR','1989-06-29');
        UsersTableSeeder::myBidderFill("HO, Sally","Sally.Ho@Demo.com",'password','REGULAR','1989-06-30');
        UsersTableSeeder::myBidderFill("ZENITH, For","For.Zenith@Demo.com",'password','REGULAR','1989-07-01');
        UsersTableSeeder::myBidderFill("RIBBOT, Robert","Robert.Ribbot@Demo.com",'password','REGULAR','1989-07-15');
        UsersTableSeeder::myBidderFill("KONG, King","King.Kong@Demo.com",'password','REGULAR','1989-07-15');
        UsersTableSeeder::myBidderFill("SIMON, Martin","Martin.Simon@Demo.com",'password','REGULAR','1989-07-15');
        UsersTableSeeder::myBidderFill("SOLOMON, Erin","Erin.Solomon@Demo.com",'password','REGULAR','1989-07-16');
        UsersTableSeeder::myBidderFill("FLORES, Bethany","Bethany.Flores@Demo.com",'password','REGULAR','1989-07-16');
        UsersTableSeeder::myBidderFill("NORMAN, Ora","Ora.Norman@Demo.com",'password','REGULAR','1989-07-16');
        UsersTableSeeder::myBidderFill("POPE, Christopher","Christopher.Pope@Demo.com",'password','REGULAR','1989-07-17');
        UsersTableSeeder::myBidderFill("CLEMONS, Shay","Shay.Clemons@Demo.com",'password','REGULAR','1989-08-11');
        UsersTableSeeder::myBidderFill("FOWLER, Travis","Travis.Fowler@Demo.com",'password','REGULAR','1989-08-11');
        UsersTableSeeder::myBidderFill("GUILLAUME, Gabin","Gabin.Guillaume@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("ROY, Diego","Diego.Roy@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("AUSTIN, Stella","Stella.Austin@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("CARON, Dimitri","Dimitri.Caron@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("SIMON, Julien","Julien.Simon@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("CHARPENTIER, Victor","Victor.Charpentier@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("SANCHEZ, Nathan","Nathan.Sanchez@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("FLETCHER, Rafael","Rafael.Fletcher@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("CHRISTIAN, Kylan","Kylan.Christian@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("CARLSON, Bree","Bree.Carlson@Demo.com",'password','REGULAR','1989-08-15');
        UsersTableSeeder::myBidderFill("LEFEVRE, Colin","Colin.Lefevre@Demo.com",'password','REGULAR','1989-09-21');
        UsersTableSeeder::myBidderFill("REMY, Jules","Jules.Remy@Demo.com",'password','REGULAR','1989-10-08');
        UsersTableSeeder::myBidderFill("MCDONALD, Orla","Orla.Mcdonald@Demo.com",'password','REGULAR','1989-10-08');
        UsersTableSeeder::myBidderFill("KINNEY, Keely","Keely.Kinney@Demo.com",'password','REGULAR','1989-10-08');
        UsersTableSeeder::myBidderFill("PARKS, Denise","Denise.Parks@Demo.com",'password','REGULAR','1989-10-08');
        UsersTableSeeder::myBidderFill("KEY, Christian","Christian.Key@Demo.com",'password','REGULAR','1989-10-08');
        UsersTableSeeder::myBidderFill("EVRARD, Tom","Tom.Evrard@Demo.com",'password','REGULAR','1990-01-01');
        UsersTableSeeder::myBidderFill("SCHNEIDER, Alexis","Alexis.Schneider@Demo.com",'password','REGULAR','1990-01-02');
        UsersTableSeeder::myBidderFill("WOOTEN, Sloane","Sloane.Wooten@Demo.com",'password','REGULAR','1990-01-03');
        UsersTableSeeder::myBidderFill("FONTAINE, Félix","Félix.Fontaine@Demo.com",'password','REGULAR','1989-07-15');
        UsersTableSeeder::myBidderFill("LITTLE, Samantha","Samantha.Little@Demo.com",'password','REGULAR','1990-01-05');
        UsersTableSeeder::myBidderFill("TRUJILLO, Rigel","Rigel.Trujillo@Demo.com",'password','REGULAR','1990-01-06');
        UsersTableSeeder::myBidderFill("TERRELL, Ina","Ina.Terrell@Demo.com",'password','REGULAR','1990-01-07');
        UsersTableSeeder::myBidderFill("SEXTON, Fuller","Fuller.Sexton@Demo.com",'password','REGULAR','1990-03-10');
        UsersTableSeeder::myBidderFill("LAINE, Alexis","Alexis.Laine@Demo.com",'password','REGULAR','1990-03-10');
        UsersTableSeeder::myBidderFill("SILVA, Jason","Jason.Silva@Demo.com",'password','REGULAR','1990-03-10');
        UsersTableSeeder::myBidderFill("BENOIT, Nolan","Nolan.Benoit@Demo.com",'password','REGULAR','1990-04-30');
        UsersTableSeeder::myBidderFill("GILLESPIE, Halla","Halla.Gillespie@Demo.com",'password','REGULAR','1990-04-30');
        UsersTableSeeder::myBidderFill("ALLISON, Hyatt","Hyatt.Allison@Demo.com",'password','REGULAR','1990-06-03');
        UsersTableSeeder::myBidderFill("HOLLAND, Andrew","Andrew.Holland@Demo.com",'password','REGULAR','1990-06-03');
        UsersTableSeeder::myBidderFill("BAILLY, Simon","Simon.Bailly@Demo.com",'password','REGULAR','1990-07-07');
        UsersTableSeeder::myBidderFill("ROY, Émile","Émile.Roy@Demo.com",'password','REGULAR','1990-07-08');
        UsersTableSeeder::myBidderFill("WALSH, Nyssa","Nyssa.Walsh@Demo.com",'password','REGULAR','1990-07-09');
        UsersTableSeeder::myBidderFill("MARIE, Mathéo","Mathéo.Marie@Demo.com",'password','REGULAR','1990-07-10');
        UsersTableSeeder::myBidderFill("HAMMOND, Iona","Iona.Hammond@Demo.com",'password','REGULAR','1990-07-11');
        UsersTableSeeder::myBidderFill("JOLY, Malo","Malo.Joly@Demo.com",'password','REGULAR','1990-07-12');
        UsersTableSeeder::myBidderFill("MCCORMICK, Daquan","Daquan.Mccormick@Demo.com",'password','REGULAR','1990-07-13');
        UsersTableSeeder::myBidderFill("BURT, Duncan","Duncan.Burt@Demo.com",'password','REGULAR','1990-07-14');
        UsersTableSeeder::myBidderFill("ROBIN, Cédric","Cédric.Robin@Demo.com",'password','REGULAR','1990-11-03');
        UsersTableSeeder::myBidderFill("HOOVER, Rhonda","Rhonda.Hoover@Demo.com",'password','REGULAR','1990-11-03');
        UsersTableSeeder::myBidderFill("BAIRD, Steven","Steven.Baird@Demo.com",'password','REGULAR','1990-11-20');
        UsersTableSeeder::myBidderFill("XEROX, Seven","Seven.Xerox@Demo.com",'password','REGULAR','1990-11-21');
        UsersTableSeeder::myBidderFill("IBM, Was","Was.Ibm@Demo.com",'password','REGULAR','1990-12-07');
        UsersTableSeeder::myBidderFill("DOMINGUEZ, Tatiana","Tatiana.Dominguez@Demo.com",'password','REGULAR','1990-12-07');
        UsersTableSeeder::myBidderFill("REY, Esteban","Esteban.Rey@Demo.com",'password','REGULAR','1990-12-07');
        UsersTableSeeder::myBidderFill("GRANT, Martina","Martina.Grant@Demo.com",'password','REGULAR','1990-12-08');
        UsersTableSeeder::myBidderFill("MILLS, Lavinia","Lavinia.Mills@Demo.com",'password','REGULAR','1990-12-09');
        UsersTableSeeder::myBidderFill("HERMAN, Keane","Keane.Herman@Demo.com",'password','REGULAR','1990-12-10');
        UsersTableSeeder::myBidderFill("PERRIN, Victor","Victor.Perrin@Demo.com",'password','REGULAR','1990-12-11');
        UsersTableSeeder::myBidderFill("PRESTON, Elliott","Elliott.Preston@Demo.com",'password','REGULAR','1990-12-12');
        UsersTableSeeder::myBidderFill("WALKER, Victor","Victor.Walker@Demo.com",'password','REGULAR','1991-01-10');
        UsersTableSeeder::myBidderFill("REY, Léonard","Léonard.Rey@Demo.com",'password','REGULAR','1991-01-10');
        UsersTableSeeder::myBidderFill("GREER, Jenna","Jenna.Greer@Demo.com",'password','REGULAR','1991-01-10');
        UsersTableSeeder::myBidderFill("ROBERT, Grégory","Grégory.Robert@Demo.com",'password','REGULAR','1991-01-10');
        UsersTableSeeder::myBidderFill("HESTER, Macey","Macey.Hester@Demo.com",'password','REGULAR','1991-03-19');
        UsersTableSeeder::myBidderFill("COLIN, Adrian","Adrian.Colin@Demo.com",'password','REGULAR','1991-03-19');
        UsersTableSeeder::myBidderFill("WILLIS, Warren","Warren.Willis@Demo.com",'password','REGULAR','1991-04-05');
        UsersTableSeeder::myBidderFill("MUNOZ, Naomi","Naomi.Munoz@Demo.com",'password','REGULAR','1991-04-06');
        UsersTableSeeder::myBidderFill("DAVIDSON, Macaulay","Macaulay.Davidson@Demo.com",'password','REGULAR','1991-04-07');
        UsersTableSeeder::myBidderFill("NOEL, Marwane","Marwane.Noel@Demo.com",'password','REGULAR','1991-04-08');
        UsersTableSeeder::myBidderFill("SARGENT, Yuli","Yuli.Sargent@Demo.com",'password','REGULAR','1991-04-22');
        UsersTableSeeder::myBidderFill("NICHOLS, Winifred","Winifred.Nichols@Demo.com",'password','REGULAR','1991-04-22');
        UsersTableSeeder::myBidderFill("SHEPHERD, Paul","Paul.Shepherd@Demo.com",'password','REGULAR','1991-05-09');
        UsersTableSeeder::myBidderFill("WARREN, Kitra","Kitra.Warren@Demo.com",'password','REGULAR','1991-05-26');
        UsersTableSeeder::myBidderFill("GIRAUD, Nathan","Nathan.Giraud@Demo.com",'password','REGULAR','1991-05-26');
        UsersTableSeeder::myBidderFill("NOEL, Carter","Carter.Noel@Demo.com",'password','REGULAR','1991-05-26');
        UsersTableSeeder::myBidderFill("DRAKE, Kenneth","Kenneth.Drake@Demo.com",'password','REGULAR','1991-05-27');
        UsersTableSeeder::myBidderFill("POPE, Cleo","Cleo.Pope@Demo.com",'password','REGULAR','1991-05-28');
        UsersTableSeeder::myBidderFill("CONRAD, Ignatius","Ignatius.Conrad@Demo.com",'password','REGULAR','1991-05-29');
        UsersTableSeeder::myBidderFill("HENDERSON, Kimberley","Kimberley.Henderson@Demo.com",'password','REGULAR','1991-06-12');
        UsersTableSeeder::myBidderFill("CHARPENTIER, Adam","Adam.Charpentier@Demo.com",'password','REGULAR','1991-06-12');
        UsersTableSeeder::myBidderFill("FITZGERALD, Orli","Orli.Fitzgerald@Demo.com",'password','REGULAR','1991-06-29');
        UsersTableSeeder::myBidderFill("BURKS, Fallon","Fallon.Burks@Demo.com",'password','REGULAR','1991-07-16');
        UsersTableSeeder::myBidderFill("BOULANGER, Maxime","Maxime.Boulanger@Demo.com",'password','REGULAR','1991-08-02');
        UsersTableSeeder::myBidderFill("TATE, Shay","Shay.Tate@Demo.com",'password','REGULAR','1991-08-02');
        UsersTableSeeder::myBidderFill("DUVAL, Dimitri","Dimitri.Duval@Demo.com",'password','REGULAR','1991-09-05');
        UsersTableSeeder::myBidderFill("GLASS, Xena","Xena.Glass@Demo.com",'password','REGULAR','1991-09-22');
        UsersTableSeeder::myBidderFill("BRAY, Whitney","Whitney.Bray@Demo.com",'password','REGULAR','1991-10-09');
        UsersTableSeeder::myBidderFill("WEBER, Xena","Xena.Weber@Demo.com",'password','REGULAR','1991-10-26');
        UsersTableSeeder::myBidderFill("RUIZ, Jenna","Jenna.Ruiz@Demo.com",'password','REGULAR','1991-11-12');
        UsersTableSeeder::myBidderFill("LEFEBVRE, Julien","Julien.Lefebvre@Demo.com",'password','REGULAR','1991-11-29');
        UsersTableSeeder::myBidderFill("MCCLURE, Jescie","Jescie.Mcclure@Demo.com",'password','REGULAR','1991-12-16');
        UsersTableSeeder::myBidderFill("CARRE, Maxence","Maxence.Carre@Demo.com",'password','REGULAR','1991-12-16');
        UsersTableSeeder::myBidderFill("GONZALEZ, Bastien","Bastien.Gonzalez@Demo.com",'password','REGULAR','1992-01-02');
        UsersTableSeeder::myBidderFill("MARECHAL, Timothée","Timothée.Marechal@Demo.com",'password','REGULAR','1992-01-19');
        UsersTableSeeder::myBidderFill("MAILLARD, Malo","Malo.Maillard@Demo.com",'password','REGULAR','1992-02-05');
        UsersTableSeeder::myBidderFill("CASE, Jermaine","Jermaine.Case@Demo.com",'password','REGULAR','1992-02-22');
        UsersTableSeeder::myBidderFill("WASHINGTON, Ella","Ella.Washington@Demo.com",'password','REGULAR','1992-03-10');
        UsersTableSeeder::myBidderFill("RIVERS, Camille","Camille.Rivers@Demo.com",'password','REGULAR','1992-03-27');
        UsersTableSeeder::myBidderFill("DAVID, Louis","Louis.David@Demo.com",'password','REGULAR','1992-03-28');
        UsersTableSeeder::myBidderFill("MARCHAND, Zacharis","Zacharis.Marchand@Demo.com",'password','REGULAR','1992-04-30');
        UsersTableSeeder::myBidderFill("PERROT, Mehdi","Mehdi.Perrot@Demo.com",'password','REGULAR','1992-04-30');
        UsersTableSeeder::myBidderFill("YOUNG, Thaddeus","Thaddeus.Young@Demo.com",'password','REGULAR','1992-04-30');
        UsersTableSeeder::myBidderFill("BLOCK, Allen","Allen.Block@Demo.com",'password','REGULAR','1992-04-30');
        UsersTableSeeder::myBidderFill("BRICK, Kevin","Kevin.Brick@Demo.com",'password','REGULAR','1992-05-01');
        UsersTableSeeder::myBidderFill("SNIPE, Anastasia","Anastasia.Snipe@Demo.com",'password','REGULAR','1992-05-02');
        UsersTableSeeder::myBidderFill("BOYLE, Aaron","Aaron.Boyle@Demo.com",'password','REGULAR','1992-05-02');
        UsersTableSeeder::myBidderFill("WATTS, Eliana","Eliana.Watts@Demo.com",'password','REGULAR','1992-06-03');
        UsersTableSeeder::myBidderFill("LARSEN, Kadeem","Kadeem.Larsen@Demo.com",'password','REGULAR','1992-06-20');
        UsersTableSeeder::myBidderFill("BAUER, Drake","Drake.Bauer@Demo.com",'password','REGULAR','1992-07-07');
        UsersTableSeeder::myBidderFill("ROBLES, Susan","Susan.Robles@Demo.com",'password','REGULAR','1992-07-24');
        UsersTableSeeder::myBidderFill("MORRIS, Yuli","Yuli.Morris@Demo.com",'password','REGULAR','1992-08-10');
        UsersTableSeeder::myBidderFill("COLON, Kerry","Kerry.Colon@Demo.com",'password','REGULAR','1992-08-10');
        UsersTableSeeder::myBidderFill("WILLIS, Allen","Allen.Willis@Demo.com",'password','REGULAR','1992-09-13');
        UsersTableSeeder::myBidderFill("DUPONT, Kevin","Kevin.Dupont@Demo.com",'password','REGULAR','1992-09-13');
        UsersTableSeeder::myBidderFill("VARGAS, Amity","Amity.Vargas@Demo.com",'password','REGULAR','1992-09-13');
        UsersTableSeeder::myBidderFill("FRENCH, Carl","Carl.French@Demo.com",'password','REGULAR','1992-09-30');
        UsersTableSeeder::myBidderFill("FAULKNER, Anastasia","Anastasia.Faulkner@Demo.com",'password','REGULAR','1992-10-17');
        UsersTableSeeder::myBidderFill("HYDE, Maxine","Maxine.Hyde@Demo.com",'password','REGULAR','1992-10-17');
        UsersTableSeeder::myBidderFill("RUSSO, Ariel","Ariel.Russo@Demo.com",'password','REGULAR','1992-10-17');
        UsersTableSeeder::myBidderFill("MARTIN, Nathan","Nathan.Martin@Demo.com",'password','REGULAR','1992-11-03');
        UsersTableSeeder::myBidderFill("BEST, Karyn","Karyn.Best@Demo.com",'password','REGULAR','1992-11-03');
        UsersTableSeeder::myBidderFill("HERRERA, Christen","Christen.Herrera@Demo.com",'password','REGULAR','1992-12-07');
        UsersTableSeeder::myBidderFill("LACROIX, Lilian","Lilian.Lacroix@Demo.com",'password','REGULAR','1992-12-07');
        UsersTableSeeder::myBidderFill("GRAVES, Ahmed","Ahmed.Graves@Demo.com",'password','REGULAR','1992-12-07');
        UsersTableSeeder::myBidderFill("BRYAN, Nehru","Nehru.Bryan@Demo.com",'password','REGULAR','1993-01-27');
        UsersTableSeeder::myBidderFill("WEISS, Keith","Keith.Weiss@Demo.com",'password','REGULAR','1993-01-27');
        UsersTableSeeder::myBidderFill("CARPENTER, Ignacia","Ignacia.Carpenter@Demo.com",'password','REGULAR','1993-01-27');
        UsersTableSeeder::myBidderFill("MERCADO, Clementine","Clementine.Mercado@Demo.com",'password','REGULAR','1993-03-02');
        UsersTableSeeder::myBidderFill("SOSA, Francesca","Francesca.Sosa@Demo.com",'password','REGULAR','1993-03-02');
        UsersTableSeeder::myBidderFill("EVANS, Castor","Castor.Evans@Demo.com",'password','REGULAR','1993-03-02');
        UsersTableSeeder::myBidderFill("PREVOST, Mathis","Mathis.Prevost@Demo.com",'password','REGULAR','1993-03-02');
        UsersTableSeeder::myBidderFill("KLEIN, Colin","Colin.Klein@Demo.com",'password','REGULAR','1993-04-22');
        UsersTableSeeder::myBidderFill("SILVA, Isadora","Isadora.Silva@Demo.com",'password','REGULAR','1993-05-09');
        UsersTableSeeder::myBidderFill("AUBERT, Clément","Clément.Aubert@Demo.com",'password','REGULAR','1993-05-09');
        UsersTableSeeder::myBidderFill("REMY, Dorian","Dorian.Remy@Demo.com",'password','REGULAR','1993-05-09');
        UsersTableSeeder::myBidderFill("BRADLEY, Cassandra","Cassandra.Bradley@Demo.com",'password','REGULAR','1993-05-26');
        UsersTableSeeder::myBidderFill("MEDINA, Isabelle","Isabelle.Medina@Demo.com",'password','REGULAR','1993-05-26');
        UsersTableSeeder::myBidderFill("SIMPSON, Brenda","Brenda.Simpson@Demo.com",'password','REGULAR','1993-05-26');
        UsersTableSeeder::myBidderFill("CHRISTENSEN, Inez","Inez.Christensen@Demo.com",'password','REGULAR','1993-05-26');
        UsersTableSeeder::myBidderFill("MORGAN, Vanna","Vanna.Morgan@Demo.com",'password','REGULAR','1993-06-29');
        UsersTableSeeder::myBidderFill("LAMB, Scarlett","Scarlett.Lamb@Demo.com",'password','REGULAR','1993-06-29');
        UsersTableSeeder::myBidderFill("MARECHAL, Valentin","Valentin.Marechal@Demo.com",'password','REGULAR','1993-06-30');
        UsersTableSeeder::myBidderFill("GARNIER, Mathis","Mathis.Garnier@Demo.com",'password','REGULAR','1993-06-30');
        UsersTableSeeder::myBidderFill("JEAN, Evan","Evan.Jean@Demo.com",'password','REGULAR','1993-07-16');
        UsersTableSeeder::myBidderFill("FUENTES, Benedict","Benedict.Fuentes@Demo.com",'password','REGULAR','1993-07-16');
        UsersTableSeeder::myBidderFill("CHAN, Lars","Lars.Chan@Demo.com",'password','REGULAR','1993-08-19');
        UsersTableSeeder::myBidderFill("FERNANDEZ, Damian","Damian.Fernandez@Demo.com",'password','REGULAR','1993-08-19');
        UsersTableSeeder::myBidderFill("MONROE, Jasper","Jasper.Monroe@Demo.com",'password','REGULAR','1993-08-19');
        UsersTableSeeder::myBidderFill("RILEY, Diana","Diana.Riley@Demo.com",'password','REGULAR','1993-08-19');
        UsersTableSeeder::myBidderFill("CURTIS, Jana","Jana.Curtis@Demo.com",'password','REGULAR','1993-08-20');
        UsersTableSeeder::myBidderFill("RICHARD, Alexis","Alexis.Richard@Demo.com",'password','REGULAR','1993-08-20');
        UsersTableSeeder::myBidderFill("GILBERT, Melodie","Melodie.Gilbert@Demo.com",'password','REGULAR','1993-08-20');
        UsersTableSeeder::myBidderFill("LEVEQUE, Alexis","Alexis.Leveque@Demo.com",'password','REGULAR','1993-09-05');
        UsersTableSeeder::myBidderFill("BEACH, Shellie","Shellie.Beach@Demo.com",'password','REGULAR','1993-09-05');
        UsersTableSeeder::myBidderFill("ROWLAND, Dominic","Dominic.Rowland@Demo.com",'password','REGULAR','1993-09-05');
        UsersTableSeeder::myBidderFill("TRAVIS, Elton","Elton.Travis@Demo.com",'password','REGULAR','1993-09-22');
        UsersTableSeeder::myBidderFill("BERNARD, Diego","Diego.Bernard@Demo.com",'password','REGULAR','1993-09-22');
        UsersTableSeeder::myBidderFill("CONWAY, Kevyn","Kevyn.Conway@Demo.com",'password','REGULAR','1993-09-22');
        UsersTableSeeder::myBidderFill("GUERRA, Quemby","Quemby.Guerra@Demo.com",'password','REGULAR','1993-10-09');
        UsersTableSeeder::myBidderFill("MASSON, Rémi","Rémi.Masson@Demo.com",'password','REGULAR','1993-10-09');
        UsersTableSeeder::myBidderFill("MEDINA, Freya","Freya.Medina@Demo.com",'password','REGULAR','1993-10-09');
        UsersTableSeeder::myBidderFill("BRUNET, Kylian","Kylian.Brunet@Demo.com",'password','REGULAR','1993-11-12');
        UsersTableSeeder::myBidderFill("MULLER, Quentin","Quentin.Muller@Demo.com",'password','REGULAR','1993-11-13');
        UsersTableSeeder::myBidderFill("GILBERT, Upton","Upton.Gilbert@Demo.com",'password','REGULAR','1993-11-13');
        UsersTableSeeder::myBidderFill("HUMPHREY, Jason","Jason.Humphrey@Demo.com",'password','REGULAR','1993-11-13');
        UsersTableSeeder::myBidderFill("LAINE, Émile","Émile.Laine@Demo.com",'password','REGULAR','1993-11-29');
        UsersTableSeeder::myBidderFill("MOONEY, Alexa","Alexa.Mooney@Demo.com",'password','REGULAR','1993-12-16');
        UsersTableSeeder::myBidderFill("DUVAL, Amine","Amine.Duval@Demo.com",'password','REGULAR','1993-12-16');
        UsersTableSeeder::myBidderFill("BOWEN, Slade","Slade.Bowen@Demo.com",'password','REGULAR','1993-12-16');
        UsersTableSeeder::myBidderFill("FERNANDEZ, Adam","Adam.Fernandez@Demo.com",'password','REGULAR','1993-12-17');
        UsersTableSeeder::myBidderFill("GRAVES, Keefe","Keefe.Graves@Demo.com",'password','REGULAR','1993-12-18');
        UsersTableSeeder::myBidderFill("HARVEY, Sigourney","Sigourney.Harvey@Demo.com",'password','REGULAR','1994-01-02');
        UsersTableSeeder::myBidderFill("CARR, Brenda","Brenda.Carr@Demo.com",'password','REGULAR','1994-01-02');
        UsersTableSeeder::myBidderFill("BOWERS, Edan","Edan.Bowers@Demo.com",'password','REGULAR','1994-01-19');
        UsersTableSeeder::myBidderFill("KIRK, Keegan","Keegan.Kirk@Demo.com",'password','REGULAR','1994-01-19');
        UsersTableSeeder::myBidderFill("ROY, Enzo","Enzo.Roy@Demo.com",'password','REGULAR','1994-02-05');
        
                        
    }
}