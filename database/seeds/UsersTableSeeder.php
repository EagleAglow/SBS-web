<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\BidderGroup;

class UsersTableSeeder extends Seeder
{

    private function myBidderFill($nm,$em,$pwd,$bg,$bgno)  // name, email, password, bidder_group, bidder_group_primary_order
    {
        // expects bidder_group codes: none (no role assigned), demo (role=bidder-demo), tsu (role=bidder-tsu),
        // irpa (role=bidder-irpa),oidp (role=bidder-oidp), traffic (roles=bidder-traffic)

        if ($bg=='traffic'){
            $bg_id = App\BidderGroup::select('id')->where('code','TRAFFIC')->first()->id;
        } else {
            if ($bg=='none'){
                $bg_id = App\BidderGroup::select('id')->where('code',strtoupper($bg))->first()->id;
            } else {
                $bg_id = App\BidderGroup::select('id')->where('code',strtoupper($bg))->first()->id;
            }
        }

        $newUser = User::create([
        'name' => $nm,
        'email' => $em,
        'password' => Hash::make($pwd),
        // set verified time so this system does not send out verfication emails - remove if you want them to verify
        // also change overall system setting - see: Auth::routes  in web.php
        'email_verified_at' => '2000-01-01',
        'bidder_group_id' => $bg_id,
        'bidder_primary_order' => $bgno,
        // accept defaults for... 
        //   'has_bid' = false
        //   'bid_order' = null   
        //   'bidder_secondary_order' = null

        ]);


        if ($bg=='traffic'){
            $bg_role = 'bidder-tnon'; 
            $newUser->assignRole($bg_role);
            $bg_role = 'bidder-tcom'; 
            $newUser->assignRole($bg_role);
        } else {
            if ($bg=='none'){
                // no role is assigned
            } else {
                $bg_role = 'bidder-' . $bg; 
                $newUser->assignRole($bg_role);
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
        UsersTableSeeder::myBidderFill('Demo Bidder One','one@demo.com','password','demo',10);
        UsersTableSeeder::myBidderFill('Demo Bidder Two','two@demo.com','password','demo',12);
        UsersTableSeeder::myBidderFill('Demo Bidder Three','three@demo.com','password','demo',12);
        UsersTableSeeder::myBidderFill('Demo Bidder Four','four@demo.com','password','demo',12);
        UsersTableSeeder::myBidderFill('Demo Bidder Five','five@demo.com','password','demo',13);

        // add demo users - admin / supervisor / superuser
        $newUser = User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            // verified time so this does not send out verfication emails
            'email_verified_at' => '2000-01-01',
            // bidder group: NONE
            'bidder_group_id' => 8,  // no bidding group/role
        ]);
        $newUser->assignRole('admin');

        $newUser = User::create([
            'name' => 'Demo Superuser',
            'email' => 'superuser@demo.com',
            'password' => Hash::make('password'),
            // verified time so this does not send out verfication emails
            'email_verified_at' => '2000-01-01',
            // bidder group: NONE
            'bidder_group_id' => 8,  // no bidding group/role
        ]);
        $newUser->assignRole('superuser');

        $newUser = User::create([
            'name' => 'Demo Supervisor',
            'email' => 'supervisor@demo.com',
            'password' => Hash::make('password'),
            // verified time so this does not send out verfication emails
            'email_verified_at' => '2000-01-01',
            // bidder group: NONE
            'bidder_group_id' => 8,  // no bidding group/role
            ]);
        $newUser->assignRole('supervisor');

/////////////////////////////////////////////////////////////////////////
        // for development - has all roles. NOT suitable for some testing, though...
/////////////////////////////////////////////////////////////////////////
$newUser = User::create([
            'name' => 'Developer',
            'email' => 'dev@demo.com',
            'password' => Hash::make('password'),
            // verified time so this does not send out verfication emails
            'email_verified_at' => '2000-01-01',
            // bidder group: DEMO
            'bidder_group_id' => 1,
            'bidder_primary_order' => 1,

        ]);
        $newUser->assignRole('bidder-demo');
        $newUser->assignRole('bidder-tsu');
        $newUser->assignRole('bidder-oidp');
        $newUser->assignRole('bidder-irpa');
        $newUser->assignRole('bidder-tcom');
        $newUser->assignRole('bidder-tnon');
        $newUser->assignRole('supervisor');
        $newUser->assignRole('admin');
        $newUser->assignRole('superuser');
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////

        // add bogus bidders

        // TSU
        UsersTableSeeder::myBidderFill("D'AGNILLO, Gilbert","Gilbert.D'Agnillo@Demo.com",'password','tsu',101);
        UsersTableSeeder::myBidderFill('MCCRAY, Hayfa','Hayfa.Mccray@Demo.com','password','tsu',102);
        UsersTableSeeder::myBidderFill("O'HAGAN, Dustin","Dustin.O'Hagan@Demo.com",'password','tsu',103);
        UsersTableSeeder::myBidderFill('CARSON, Eric','Eric.Carson@Demo.com','password','tsu',104);

/* 
        UsersTableSeeder::myBidderFill('PAGE, Merrill','Merrill.Page@Demo.com','password','tsu',105);
        UsersTableSeeder::myBidderFill('CORDIER, Valentin','Valentin.Cordier@Demo.com','password','tsu',106);
        UsersTableSeeder::myBidderFill('ROWE, Lester','Lester.Rowe@Demo.com','password','tsu',107);
        UsersTableSeeder::myBidderFill('ESTRADA, John','John.Estrada@Demo.com','password','tsu',108);
        UsersTableSeeder::myBidderFill('BOWMAN, Baxter','Baxter.Bowman@Demo.com','password','tsu',109);
        UsersTableSeeder::myBidderFill('WALTON, Lillith','Lillith.Walton@Demo.com','password','tsu',110);
        UsersTableSeeder::myBidderFill('TYSON, Lance','Lance.Tyson@Demo.com','password','tsu',111);
        UsersTableSeeder::myBidderFill('HOWARD, Cruz','Cruz.Howard@Demo.com','password','tsu',112);
        UsersTableSeeder::myBidderFill('MALONE, Rhoda','Rhoda.Malone@Demo.com','password','tsu',113);
        UsersTableSeeder::myBidderFill('HODGES, Mary','Mary.Hodges@Demo.com','password','tsu',114);
        UsersTableSeeder::myBidderFill('MENDOZA, Desirae','Desirae.Mendoza@Demo.com','password','tsu',115);
        UsersTableSeeder::myBidderFill('MADDOX, Plato','Plato.Maddox@Demo.com','password','tsu',116);
        UsersTableSeeder::myBidderFill('BALL, Grady','Grady.Ball@Demo.com','password','tsu',117);
        UsersTableSeeder::myBidderFill('POIRIER, Felix','Felix.Poirier@Demo.com','password','tsu',118);
        UsersTableSeeder::myBidderFill('DAVIS, Lev','Lev.Davis@Demo.com','password','tsu',119);
        UsersTableSeeder::myBidderFill('HEAD, Claudia','Claudia.Head@Demo.com','password','tsu',120);
        UsersTableSeeder::myBidderFill('GERARD, Maxime','Maxime.Gerard@Demo.com','password','tsu',121);
        UsersTableSeeder::myBidderFill('MILLET, Renaud','Renaud.Millet@Demo.com','password','tsu',122);
        UsersTableSeeder::myBidderFill('NGUYEN, Libby','Libby.Nguyen@Demo.com','password','tsu',123);
        UsersTableSeeder::myBidderFill('BLANCHARD, Renaud','Renaud.Blanchard@Demo.com','password','tsu',124);
         */
        
        // IRPA
        UsersTableSeeder::myBidderFill('WARNER, Enzo','Enzo.Warner@Demo.com','password','irpa',125);

/* 
        UsersTableSeeder::myBidderFill('VINCENT, Melodie','Melodie.Vincent@Demo.com','password','irpa',126);
        UsersTableSeeder::myBidderFill('SOLIS, Aaron','Aaron.Solis@Demo.com','password','irpa',127);
        UsersTableSeeder::myBidderFill('PENNINGTON, Jenette','Jenette.Pennington@Demo.com','password','irpa',128);
        UsersTableSeeder::myBidderFill('BOONE, Reagan','Reagan.Boone@Demo.com','password','irpa',129);
        UsersTableSeeder::myBidderFill('LECLERCQ, Azalia','Azalia.Leclercq@Demo.com','password','irpa',130);
        UsersTableSeeder::myBidderFill('DURAND, Lorenzo','Lorenzo.Durand@Demo.com','password','irpa',131);
        UsersTableSeeder::myBidderFill('LAINE, Killian','Killian.Laine@Demo.com','password','irpa',132);
        UsersTableSeeder::myBidderFill('COLLIN, Davy','Davy.Collin@Demo.com','password','irpa',133);
        UsersTableSeeder::myBidderFill('BULLOCK, Alexis','Alexis.Bullock@Demo.com','password','irpa',134);
        UsersTableSeeder::myBidderFill('BENTLEY, Cade','Cade.Bentley@Demo.com','password','irpa',135);
        UsersTableSeeder::myBidderFill('KIRK, Vielka','Vielka.Kirk@Demo.com','password','irpa',136);
        UsersTableSeeder::myBidderFill('SUTTON, Lance','Lance.Sutton@Demo.com','password','irpa',137);
        UsersTableSeeder::myBidderFill('HESS, Diana','Diana.Hess@Demo.com','password','irpa',138);

 */        
        
        // OIDP
        UsersTableSeeder::myBidderFill('MUELLER, Ciaran','Ciaran.Mueller@Demo.com','password','oidp',139);
        UsersTableSeeder::myBidderFill('WALL, Florence','Florence.Wall@Demo.com','password','oidp',139);
        UsersTableSeeder::myBidderFill('ELLIS, Chaney','Chaney.Ellis@Demo.com','password','oidp',139);
        UsersTableSeeder::myBidderFill('HAMILTON, Heidi','Heidi.Hamilton@Demo.com','password','oidp',140);
        UsersTableSeeder::myBidderFill('ROCHE, Gilbert','Gilbert.Roche@Demo.com','password','oidp',140);

        
        // TRAFFIC
        UsersTableSeeder::myBidderFill('ROLLAND, Theo','Theo.Rolland@Demo.com','password','traffic',109);
        UsersTableSeeder::myBidderFill('PHILIPPE, Julien','Julien.Philippe@Demo.com','password','traffic',127);
        UsersTableSeeder::myBidderFill('LOTT, Janna','Janna.Lott@Demo.com','password','traffic',139);
        UsersTableSeeder::myBidderFill('ROY, Baptiste','Baptiste.Roy@Demo.com','password','traffic',141);
        UsersTableSeeder::myBidderFill('FAURE, Victor','Victor.Faure@Demo.com','password','traffic',142);

/* 

        // TRAFFIC
        UsersTableSeeder::myBidderFill('ROLLAND, Theo','Theo.Rolland@Demo.com','password','traffic',141);
        UsersTableSeeder::myBidderFill('PHILIPPE, Julien','Julien.Philippe@Demo.com','password','traffic',141);
        UsersTableSeeder::myBidderFill('LOTT, Janna','Janna.Lott@Demo.com','password','traffic',141);
        UsersTableSeeder::myBidderFill('ROY, Baptiste','Baptiste.Roy@Demo.com','password','traffic',141);
        UsersTableSeeder::myBidderFill('FAURE, Victor','Victor.Faure@Demo.com','password','traffic',142);
        UsersTableSeeder::myBidderFill('MORTON, Sybill','Sybill.Morton@Demo.com','password','traffic',142);
        UsersTableSeeder::myBidderFill('COLLIN, Dylan','Dylan.Collin@Demo.com','password','traffic',143);
        UsersTableSeeder::myBidderFill('NICHOLS, Malachi','Malachi.Nichols@Demo.com','password','traffic',144);
        UsersTableSeeder::myBidderFill('MALLET, Matheo','Matheo.Mallet@Demo.com','password','traffic',144);
        UsersTableSeeder::myBidderFill('GIRARD, Baptiste','Baptiste.Girard@Demo.com','password','tnon',145);
        UsersTableSeeder::myBidderFill('CRUZ, Celeste','Celeste.Cruz@Demo.com','password','tcom',146);
        UsersTableSeeder::myBidderFill('ABBOTT, Katelyn','Katelyn.Abbott@Demo.com','password','traffic',146);
        UsersTableSeeder::myBidderFill('BRUN, Malik','Malik.Brun@Demo.com','password','traffic',147);
        UsersTableSeeder::myBidderFill('MOREAU, Simon','Simon.Moreau@Demo.com','password','traffic',148);
        UsersTableSeeder::myBidderFill('RIOS, Ulysses','Ulysses.Rios@Demo.com','password','traffic',149);
        UsersTableSeeder::myBidderFill('COOK, Lyle','Lyle.Cook@Demo.com','password','traffic',150);
        UsersTableSeeder::myBidderFill('WILLIAM, Farrah','Farrah.William@Demo.com','password','traffic',151);
        UsersTableSeeder::myBidderFill('SNIDER, Ursula','Ursula.Snider@Demo.com','password','traffic',152);
        UsersTableSeeder::myBidderFill('GILLIAM, Angela','Angela.Gilliam@Demo.com','password','traffic',153);
        UsersTableSeeder::myBidderFill('WARNER, Vincent','Vincent.Warner@Demo.com','password','traffic',154);
        UsersTableSeeder::myBidderFill('BRAY, William','William.Bray@Demo.com','password','traffic',155);
        UsersTableSeeder::myBidderFill('LAINE, Yohan','Yohan.Laine@Demo.com','password','traffic',156);
        UsersTableSeeder::myBidderFill('MCMAHON, May','May.Mcmahon@Demo.com','password','traffic',157);
        UsersTableSeeder::myBidderFill('MALDONADO, Reuben','Reuben.Maldonado@Demo.com','password','traffic',158);
        UsersTableSeeder::myBidderFill('CARTER, Kristen','Kristen.Carter@Demo.com','password','traffic',159);
        UsersTableSeeder::myBidderFill('HARRINGTON, Yvette','Yvette.Harrington@Demo.com','password','traffic',160);
        UsersTableSeeder::myBidderFill('BULLOCK, Jonas','Jonas.Bullock@Demo.com','password','traffic',160);
        UsersTableSeeder::myBidderFill('WEISS, Ramona','Ramona.Weiss@Demo.com','password','traffic',160);
        UsersTableSeeder::myBidderFill('FRANCOIS, Anthony','Anthony.Francois@Demo.com','password','traffic',161);
        UsersTableSeeder::myBidderFill('SLOAN, Moses','Moses.Sloan@Demo.com','password','traffic',162);
        UsersTableSeeder::myBidderFill('MARKS, Dalton','Dalton.Marks@Demo.com','password','traffic',163);
        UsersTableSeeder::myBidderFill('LEFEVRE, Yohan','Yohan.Lefevre@Demo.com','password','traffic',164);
        UsersTableSeeder::myBidderFill('ANDRE, Aaron','Aaron.Andre@Demo.com','password','traffic',165);
        UsersTableSeeder::myBidderFill('WOOTEN, Ashely','Ashely.Wooten@Demo.com','password','traffic',166);
        UsersTableSeeder::myBidderFill('MOREL, Noë','Noë.Morel@Demo.com','password','traffic',166);
        UsersTableSeeder::myBidderFill('ROCHA, Lara','Lara.Rocha@Demo.com','password','traffic',166);
        UsersTableSeeder::myBidderFill('COLLET, Maxence','Maxence.Collet@Demo.com','password','traffic',167);
        UsersTableSeeder::myBidderFill('NOEL, Amber','Amber.Noel@Demo.com','password','traffic',168);
        UsersTableSeeder::myBidderFill('GOOD, Maxine','Maxine.Good@Demo.com','password','traffic',169);
        UsersTableSeeder::myBidderFill('ELLIS, Fleur','Fleur.Ellis@Demo.com','password','traffic',170);
        UsersTableSeeder::myBidderFill('BARLOW, Elliott','Elliott.Barlow@Demo.com','password','traffic',171);
        UsersTableSeeder::myBidderFill('GAINES, Morgan','Morgan.Gaines@Demo.com','password','traffic',171);
        UsersTableSeeder::myBidderFill('COLLIER, Dacey','Dacey.Collier@Demo.com','password','traffic',171);
        UsersTableSeeder::myBidderFill('NASH, Marvin','Marvin.Nash@Demo.com','password','traffic',172);
        UsersTableSeeder::myBidderFill('FRANK, Zelda','Zelda.Frank@Demo.com','password','traffic',172);
        UsersTableSeeder::myBidderFill('KLINE, Laurel','Laurel.Kline@Demo.com','password','traffic',172);
        UsersTableSeeder::myBidderFill('GRANT, Conan','Conan.Grant@Demo.com','password','traffic',173);
        UsersTableSeeder::myBidderFill('FRANCOIS, Tristan','Tristan.Francois@Demo.com','password','traffic',174);
        UsersTableSeeder::myBidderFill('PHILIPPE, Bruno','Bruno.Philippe@Demo.com','password','traffic',174);
        UsersTableSeeder::myBidderFill('SIMON, Martin','Martin.Simon@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('SOLOMON, Erin','Erin.Solomon@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('FLORES, Bethany','Bethany.Flores@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('NORMAN, Ora','Ora.Norman@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('POPE, Christopher','Christopher.Pope@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('CLEMONS, Shay','Shay.Clemons@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('FOWLER, Travis','Travis.Fowler@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('GUILLAUME, Gabin','Gabin.Guillaume@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('ROY, Diego','Diego.Roy@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('AUSTIN, Stella','Stella.Austin@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('CARON, Dimitri','Dimitri.Caron@Demo.com','password','traffic',175);
        UsersTableSeeder::myBidderFill('SIMON, Julien','Julien.Simon@Demo.com','password','traffic',176);
        UsersTableSeeder::myBidderFill('CHARPENTIER, Victor','Victor.Charpentier@Demo.com','password','traffic',177);
        UsersTableSeeder::myBidderFill('SANCHEZ, Nathan','Nathan.Sanchez@Demo.com','password','traffic',177);
        UsersTableSeeder::myBidderFill('FLETCHER, Rafael','Rafael.Fletcher@Demo.com','password','traffic',177);
        UsersTableSeeder::myBidderFill('CHRISTIAN, Kylan','Kylan.Christian@Demo.com','password','traffic',177);
        UsersTableSeeder::myBidderFill('CARLSON, Bree','Bree.Carlson@Demo.com','password','traffic',178);
        UsersTableSeeder::myBidderFill('LEFEVRE, Colin','Colin.Lefevre@Demo.com','password','traffic',179);
        UsersTableSeeder::myBidderFill('REMY, Jules','Jules.Remy@Demo.com','password','traffic',180);
        UsersTableSeeder::myBidderFill('MCDONALD, Orla','Orla.Mcdonald@Demo.com','password','traffic',181);
        UsersTableSeeder::myBidderFill('KINNEY, Keely','Keely.Kinney@Demo.com','password','traffic',182);
        UsersTableSeeder::myBidderFill('PARKS, Denise','Denise.Parks@Demo.com','password','traffic',183);
        UsersTableSeeder::myBidderFill('KEY, Christian','Christian.Key@Demo.com','password','traffic',184);
        UsersTableSeeder::myBidderFill('EVRARD, Tom','Tom.Evrard@Demo.com','password','traffic',185);
        UsersTableSeeder::myBidderFill('SCHNEIDER, Alexis','Alexis.Schneider@Demo.com','password','traffic',185);
        UsersTableSeeder::myBidderFill('WOOTEN, Sloane','Sloane.Wooten@Demo.com','password','traffic',186);
        UsersTableSeeder::myBidderFill('FONTAINE, Felix','Felix.Fontaine@Demo.com','password','traffic',186);
        UsersTableSeeder::myBidderFill('LITTLE, Samantha','Samantha.Little@Demo.com','password','traffic',187);
        UsersTableSeeder::myBidderFill('TRUJILLO, Rigel','Rigel.Trujillo@Demo.com','password','traffic',187);
        UsersTableSeeder::myBidderFill('TERRELL, Ina','Ina.Terrell@Demo.com','password','traffic',188);
        UsersTableSeeder::myBidderFill('SEXTON, Fuller','Fuller.Sexton@Demo.com','password','traffic',189);
        UsersTableSeeder::myBidderFill('LAINE, Alexis','Alexis.Laine@Demo.com','password','traffic',190);
        UsersTableSeeder::myBidderFill('SILVA, Jason','Jason.Silva@Demo.com','password','traffic',191);
        UsersTableSeeder::myBidderFill('BENOIT, Nolan','Nolan.Benoit@Demo.com','password','traffic',192);
        UsersTableSeeder::myBidderFill('GILLESPIE, Halla','Halla.Gillespie@Demo.com','password','traffic',193);
        UsersTableSeeder::myBidderFill('ALLISON, Hyatt','Hyatt.Allison@Demo.com','password','traffic',194);
        UsersTableSeeder::myBidderFill('HOLLAND, Andrew','Andrew.Holland@Demo.com','password','traffic',195);
        UsersTableSeeder::myBidderFill('BAILLY, Simon','Simon.Bailly@Demo.com','password','traffic',196);
        UsersTableSeeder::myBidderFill('ROY, emile','emile.Roy@Demo.com','password','traffic',197);
        UsersTableSeeder::myBidderFill('WALSH, Nyssa','Nyssa.Walsh@Demo.com','password','traffic',198);
        UsersTableSeeder::myBidderFill('MARIE, Matheo','Matheo.Marie@Demo.com','password','traffic',199);
        UsersTableSeeder::myBidderFill('HAMMOND, Iona','Iona.Hammond@Demo.com','password','traffic',200);
        UsersTableSeeder::myBidderFill('JOLY, Malo','Malo.Joly@Demo.com','password','traffic',201);
        UsersTableSeeder::myBidderFill('MCCORMICK, Daquan','Daquan.Mccormick@Demo.com','password','traffic',202);
        UsersTableSeeder::myBidderFill('BURT, Duncan','Duncan.Burt@Demo.com','password','traffic',202);
        UsersTableSeeder::myBidderFill('ROBIN, Cedric','Cedric.Robin@Demo.com','password','traffic',203);
        UsersTableSeeder::myBidderFill('HOOVER, Rhonda','Rhonda.Hoover@Demo.com','password','traffic',203);
        UsersTableSeeder::myBidderFill('BAIRD, Steven','Steven.Baird@Demo.com','password','traffic',204);
        UsersTableSeeder::myBidderFill('DOMINGUEZ, Tatiana','Tatiana.Dominguez@Demo.com','password','traffic',205);
        UsersTableSeeder::myBidderFill('REY, Esteban','Esteban.Rey@Demo.com','password','traffic',205);
        UsersTableSeeder::myBidderFill('GRANT, Martina','Martina.Grant@Demo.com','password','traffic',205);
        UsersTableSeeder::myBidderFill('MILLS, Lavinia','Lavinia.Mills@Demo.com','password','traffic',205);
        UsersTableSeeder::myBidderFill('HERMAN, Keane','Keane.Herman@Demo.com','password','traffic',206);
        UsersTableSeeder::myBidderFill('PERRIN, Victor','Victor.Perrin@Demo.com','password','traffic',206);
        UsersTableSeeder::myBidderFill('PRESTON, Elliott','Elliott.Preston@Demo.com','password','traffic',207);
        UsersTableSeeder::myBidderFill('WALKER, Victor','Victor.Walker@Demo.com','password','traffic',207);
        UsersTableSeeder::myBidderFill('REY, Leonard','Leonard.Rey@Demo.com','password','traffic',208);
        UsersTableSeeder::myBidderFill('GREER, Jenna','Jenna.Greer@Demo.com','password','traffic',209);
        UsersTableSeeder::myBidderFill('ROBERT, Gregory','Gregory.Robert@Demo.com','password','traffic',210);
        UsersTableSeeder::myBidderFill('HESTER, Macey','Macey.Hester@Demo.com','password','traffic',211);
        UsersTableSeeder::myBidderFill('COLIN, Adrian','Adrian.Colin@Demo.com','password','traffic',212);
        UsersTableSeeder::myBidderFill('WILLIS, Warren','Warren.Willis@Demo.com','password','traffic',212);
        UsersTableSeeder::myBidderFill('MUNOZ, Naomi','Naomi.Munoz@Demo.com','password','traffic',212);
        UsersTableSeeder::myBidderFill('DAVIDSON, Macaulay','Macaulay.Davidson@Demo.com','password','traffic',212);
        UsersTableSeeder::myBidderFill('NOEL, Marwane','Marwane.Noel@Demo.com','password','traffic',213);
        UsersTableSeeder::myBidderFill('SARGENT, Yuli','Yuli.Sargent@Demo.com','password','traffic',213);
        UsersTableSeeder::myBidderFill('NICHOLS, Winifred','Winifred.Nichols@Demo.com','password','traffic',214);
        UsersTableSeeder::myBidderFill('SHEPHERD, Paul','Paul.Shepherd@Demo.com','password','traffic',214);
        UsersTableSeeder::myBidderFill('WARREN, Kitra','Kitra.Warren@Demo.com','password','traffic',214);
        UsersTableSeeder::myBidderFill('GIRAUD, Nathan','Nathan.Giraud@Demo.com','password','traffic',214);
        UsersTableSeeder::myBidderFill('NOEL, Carter','Carter.Noel@Demo.com','password','traffic',215);
        UsersTableSeeder::myBidderFill('DRAKE, Kenneth','Kenneth.Drake@Demo.com','password','traffic',215);
        UsersTableSeeder::myBidderFill('POPE, Cleo','Cleo.Pope@Demo.com','password','traffic',215);
        UsersTableSeeder::myBidderFill('CONRAD, Ignatius','Ignatius.Conrad@Demo.com','password','traffic',216);
        UsersTableSeeder::myBidderFill('HENDERSON, Kimberley','Kimberley.Henderson@Demo.com','password','traffic',216);
        UsersTableSeeder::myBidderFill('CHARPENTIER, Adam','Adam.Charpentier@Demo.com','password','traffic',216);
        UsersTableSeeder::myBidderFill('FITZGERALD, Orli','Orli.Fitzgerald@Demo.com','password','traffic',217);
        UsersTableSeeder::myBidderFill('BURKS, Fallon','Fallon.Burks@Demo.com','password','traffic',218);
        UsersTableSeeder::myBidderFill('BOULANGER, Maxime','Maxime.Boulanger@Demo.com','password','traffic',219);
        UsersTableSeeder::myBidderFill('TATE, Shay','Shay.Tate@Demo.com','password','traffic',220);
        UsersTableSeeder::myBidderFill('DUVAL, Dimitri','Dimitri.Duval@Demo.com','password','traffic',221);
        UsersTableSeeder::myBidderFill('GLASS, Xena','Xena.Glass@Demo.com','password','traffic',222);
        UsersTableSeeder::myBidderFill('BRAY, Whitney','Whitney.Bray@Demo.com','password','traffic',223);
        UsersTableSeeder::myBidderFill('WEBER, Xena','Xena.Weber@Demo.com','password','traffic',224);
        UsersTableSeeder::myBidderFill('RUIZ, Jenna','Jenna.Ruiz@Demo.com','password','traffic',225);
        UsersTableSeeder::myBidderFill('LEFEBVRE, Julien','Julien.Lefebvre@Demo.com','password','traffic',226);
        UsersTableSeeder::myBidderFill('MCCLURE, Jescie','Jescie.Mcclure@Demo.com','password','traffic',227);
        UsersTableSeeder::myBidderFill('CARRE, Maxence','Maxence.Carre@Demo.com','password','traffic',227);
        UsersTableSeeder::myBidderFill('GONZALEZ, Bastien','Bastien.Gonzalez@Demo.com','password','traffic',228);
        UsersTableSeeder::myBidderFill('MARECHAL, Timothee','Timothee.Marechal@Demo.com','password','traffic',229);
        UsersTableSeeder::myBidderFill('MAILLARD, Malo','Malo.Maillard@Demo.com','password','traffic',230);
        UsersTableSeeder::myBidderFill('CASE, Jermaine','Jermaine.Case@Demo.com','password','traffic',231);
        UsersTableSeeder::myBidderFill('WASHINGTON, Ella','Ella.Washington@Demo.com','password','traffic',232);
        UsersTableSeeder::myBidderFill('RIVERS, Camille','Camille.Rivers@Demo.com','password','traffic',233);
        UsersTableSeeder::myBidderFill('DAVID, Louis','Louis.David@Demo.com','password','traffic',233);
        UsersTableSeeder::myBidderFill('MARCHAND, Zacharis','Zacharis.Marchand@Demo.com','password','traffic',233);
        UsersTableSeeder::myBidderFill('PERROT, Mehdi','Mehdi.Perrot@Demo.com','password','traffic',234);
        UsersTableSeeder::myBidderFill('YOUNG, Thaddeus','Thaddeus.Young@Demo.com','password','traffic',235);
        UsersTableSeeder::myBidderFill('BOYLE, Aaron','Aaron.Boyle@Demo.com','password','traffic',236);
        UsersTableSeeder::myBidderFill('WATTS, Eliana','Eliana.Watts@Demo.com','password','traffic',237);
        UsersTableSeeder::myBidderFill('LARSEN, Kadeem','Kadeem.Larsen@Demo.com','password','traffic',238);
        UsersTableSeeder::myBidderFill('BAUER, Drake','Drake.Bauer@Demo.com','password','traffic',239);
        UsersTableSeeder::myBidderFill('ROBLES, Susan','Susan.Robles@Demo.com','password','traffic',240);
        UsersTableSeeder::myBidderFill('MORRIS, Yuli','Yuli.Morris@Demo.com','password','traffic',241);
        UsersTableSeeder::myBidderFill('COLON, Kerry','Kerry.Colon@Demo.com','password','traffic',242);
        UsersTableSeeder::myBidderFill('WILLIS, Allen','Allen.Willis@Demo.com','password','traffic',243);
        UsersTableSeeder::myBidderFill('DUPONT, Kevin','Kevin.Dupont@Demo.com','password','traffic',244);
        UsersTableSeeder::myBidderFill('VARGAS, Amity','Amity.Vargas@Demo.com','password','traffic',244);
        UsersTableSeeder::myBidderFill('FRENCH, Carl','Carl.French@Demo.com','password','traffic',244);
        UsersTableSeeder::myBidderFill('FAULKNER, Anastasia','Anastasia.Faulkner@Demo.com','password','traffic',245);
        UsersTableSeeder::myBidderFill('HYDE, Maxine','Maxine.Hyde@Demo.com','password','traffic',246);
        UsersTableSeeder::myBidderFill('RUSSO, Ariel','Ariel.Russo@Demo.com','password','traffic',246);
        UsersTableSeeder::myBidderFill('MARTIN, Nathan','Nathan.Martin@Demo.com','password','traffic',246);
        UsersTableSeeder::myBidderFill('BEST, Karyn','Karyn.Best@Demo.com','password','traffic',247);
        UsersTableSeeder::myBidderFill('HERRERA, Christen','Christen.Herrera@Demo.com','password','traffic',248);
        UsersTableSeeder::myBidderFill('LACROIX, Lilian','Lilian.Lacroix@Demo.com','password','traffic',249);
        UsersTableSeeder::myBidderFill('GRAVES, Ahmed','Ahmed.Graves@Demo.com','password','traffic',250);
        UsersTableSeeder::myBidderFill('BRYAN, Nehru','Nehru.Bryan@Demo.com','password','traffic',251);
        UsersTableSeeder::myBidderFill('WEISS, Keith','Keith.Weiss@Demo.com','password','traffic',252);
        UsersTableSeeder::myBidderFill('CARPENTER, Ignacia','Ignacia.Carpenter@Demo.com','password','traffic',253);
        UsersTableSeeder::myBidderFill('MERCADO, Clementine','Clementine.Mercado@Demo.com','password','traffic',253);
        UsersTableSeeder::myBidderFill('SOSA, Francesca','Francesca.Sosa@Demo.com','password','traffic',254);
        UsersTableSeeder::myBidderFill('EVANS, Castor','Castor.Evans@Demo.com','password','traffic',254);
        UsersTableSeeder::myBidderFill('PREVOST, Mathis','Mathis.Prevost@Demo.com','password','traffic',255);
        UsersTableSeeder::myBidderFill('KLEIN, Colin','Colin.Klein@Demo.com','password','traffic',256);
        UsersTableSeeder::myBidderFill('SILVA, Isadora','Isadora.Silva@Demo.com','password','traffic',257);
        UsersTableSeeder::myBidderFill('AUBERT, Clement','Clement.Aubert@Demo.com','password','traffic',257);
        UsersTableSeeder::myBidderFill('REMY, Dorian','Dorian.Remy@Demo.com','password','traffic',258);
        UsersTableSeeder::myBidderFill('BRADLEY, Cassandra','Cassandra.Bradley@Demo.com','password','traffic',258);
        UsersTableSeeder::myBidderFill('MEDINA, Isabelle','Isabelle.Medina@Demo.com','password','traffic',258);
        UsersTableSeeder::myBidderFill('SIMPSON, Brenda','Brenda.Simpson@Demo.com','password','traffic',259);
        UsersTableSeeder::myBidderFill('CHRISTENSEN, Inez','Inez.Christensen@Demo.com','password','traffic',259);
        UsersTableSeeder::myBidderFill('MORGAN, Vanna','Vanna.Morgan@Demo.com','password','traffic',260);
        UsersTableSeeder::myBidderFill('LAMB, Scarlett','Scarlett.Lamb@Demo.com','password','traffic',260);
        UsersTableSeeder::myBidderFill('MARECHAL, Valentin','Valentin.Marechal@Demo.com','password','traffic',260);
        UsersTableSeeder::myBidderFill('GARNIER, Mathis','Mathis.Garnier@Demo.com','password','traffic',261);
        UsersTableSeeder::myBidderFill('JEAN, Evan','Evan.Jean@Demo.com','password','traffic',261);
        UsersTableSeeder::myBidderFill('FUENTES, Benedict','Benedict.Fuentes@Demo.com','password','traffic',262);
        UsersTableSeeder::myBidderFill('CHAN, Lars','Lars.Chan@Demo.com','password','traffic',263);
        UsersTableSeeder::myBidderFill('MONROE, Jasper','Jasper.Monroe@Demo.com','password','traffic',263);
        UsersTableSeeder::myBidderFill('RILEY, Diana','Diana.Riley@Demo.com','password','traffic',263);
        UsersTableSeeder::myBidderFill('CURTIS, Jana','Jana.Curtis@Demo.com','password','traffic',263);
        UsersTableSeeder::myBidderFill('RICHARD, Alexis','Alexis.Richard@Demo.com','password','traffic',264);
        UsersTableSeeder::myBidderFill('GILBERT, Melodie','Melodie.Gilbert@Demo.com','password','traffic',264);
        UsersTableSeeder::myBidderFill('LEVEQUE, Alexis','Alexis.Leveque@Demo.com','password','traffic',264);
        UsersTableSeeder::myBidderFill('BEACH, Shellie','Shellie.Beach@Demo.com','password','traffic',265);
        UsersTableSeeder::myBidderFill('ROWLAND, Dominic','Dominic.Rowland@Demo.com','password','traffic',265);
        UsersTableSeeder::myBidderFill('TRAVIS, Elton','Elton.Travis@Demo.com','password','traffic',265);
        UsersTableSeeder::myBidderFill('BERNARD, Diego','Diego.Bernard@Demo.com','password','traffic',265);
        UsersTableSeeder::myBidderFill('CONWAY, Kevyn','Kevyn.Conway@Demo.com','password','traffic',266);
        UsersTableSeeder::myBidderFill('GUERRA, Quemby','Quemby.Guerra@Demo.com','password','traffic',266);
        UsersTableSeeder::myBidderFill('MASSON, Remi','Remi.Masson@Demo.com','password','traffic',267);
        UsersTableSeeder::myBidderFill('MEDINA, Freya','Freya.Medina@Demo.com','password','traffic',267);
        UsersTableSeeder::myBidderFill('BRUNET, Kylian','Kylian.Brunet@Demo.com','password','traffic',268);
        UsersTableSeeder::myBidderFill('MULLER, Quentin','Quentin.Muller@Demo.com','password','traffic',268);
        UsersTableSeeder::myBidderFill('GILBERT, Upton','Upton.Gilbert@Demo.com','password','traffic',269);
        UsersTableSeeder::myBidderFill('HUMPHREY, Jason','Jason.Humphrey@Demo.com','password','traffic',269);
        UsersTableSeeder::myBidderFill('LAINE, emile','emile.Laine@Demo.com','password','traffic',269);
        UsersTableSeeder::myBidderFill('MOONEY, Alexa','Alexa.Mooney@Demo.com','password','traffic',270);
        UsersTableSeeder::myBidderFill('DUVAL, Amine','Amine.Duval@Demo.com','password','traffic',270);
        UsersTableSeeder::myBidderFill('BOWEN, Slade','Slade.Bowen@Demo.com','password','traffic',270);
        UsersTableSeeder::myBidderFill('FERNANDEZ, Adam','Adam.Fernandez@Demo.com','password','traffic',270);
        UsersTableSeeder::myBidderFill('GRAVES, Keefe','Keefe.Graves@Demo.com','password','traffic',271);
        UsersTableSeeder::myBidderFill('HARVEY, Sigourney','Sigourney.Harvey@Demo.com','password','traffic',271);
        UsersTableSeeder::myBidderFill('CARR, Brenda','Brenda.Carr@Demo.com','password','traffic',272);
        UsersTableSeeder::myBidderFill('STONE, Levi','Levi.Stone@Demo.com','password','traffic',272);
        UsersTableSeeder::myBidderFill('BOWERS, Edan','Edan.Bowers@Demo.com','password','traffic',272);



 */

                        
    }
}