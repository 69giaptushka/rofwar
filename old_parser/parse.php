<?PHP
// PARSE 
// a simple-minded parser/stats/events reporter for combined RoF
// mission report textfiles
// written by =69.GIAP=TUSHKA
// 2011-2013
// Version 1.65
// Mon Sep  9 18:14:46 EDT 2013

// the main program 

//record the starting time
$start=microtime();
$start=explode(" ",$start);
$start=$start[1]+$start[0];

// Begin Configuration - Edit these as needed.
// Generally setting the Campaign variable and then the LOGFILE variable
// is all that is needed.

// set variables

// Campaign variables (set multiple variables at one go)
// Set to 1 for a particular campaign, otherwise set it to 0
// Current campaigns
$FLANDERSEAGLES = 1; //	FLANDERS EAGLES mission log
// Past campaigns - these need to be set to avoid errors in php5.5.3
// (otherwise need to remove references to them later)
$SKIESOFTHEEMPIRES = 0; // SKIES OF THE EMPIRES mission log
$YANKEEDOODLE = 0; // YANKEE DOODLE mission log
$BLOODYAPRIL = 0; // BLOODY APRIL mission log
// End Campaign variables

// Logfile variables
// $LOGFILE points to the mission log to be analyzed
//$LOGFILE = "missionReport.txt";
//$LOGFILE = "missionReport2.txt";
//$LOGFILE = "missionReport10.txt";
//$LOGFILE = "missionReportSSII-0.txt";
//$LOGFILE = "missionReportApocalypseThen1.txt";
//$LOGFILE = "missionReportApocalypseThen20.txt";
//$LOGFILE = "missionReportSoE1.txt";
//$LOGFILE = "missionReportSoE8.txt";
//$LOGFILE = "missionReportBloodyAprilMission1.txt";
//$LOGFILE = "missionReportBloodyAprilMission20.txt";
//$LOGFILE = "missionReportDogTest1.txt";
//$LOGFILE = "missionReportYankeeDoodle1.txt";
//$LOGFILE = "missionReportSOEII1.txt";
//$LOGFILE = "missionReportSOEII13.txt";
//$LOGFILE = "missionReportYankeeDoodle20.txt";
//$LOGFILE = "missionReportChannelRace.txt";
//$LOGFILE = "missionReportFlandersEagles1.txt";
//$LOGFILE = "missionReportSOEII20.txt";
$LOGFILE = "missionReportFlandersEagles20.txt";

// Debugging variables
$DEBUG = 0;  // set to 1 for a complete debugging report, 0 for off.
// 100 for START, 101 for HIT, 102 for DAMAGE, 103 for KILL,
// 104 for PLAYER_MISSION_END, 105 for TAKEOFF, 106 for LANDING,
// 107 for MISSION_END, 108 for MISSION_OBJECTIVE, 109 for AIRFIELD,
// 110 for PLAYERPLANE, 111 for GROUPINIT, 112 for GAMEOBJECTINVOLVED
// 113 for INFLUENCEAREA_HEADER, 114 for INFLUENCEAREA_BOUNDARY,
// 115 for VERSION (nothing yet for BOTID... haven't found its use)

// Individual variables
// Usually you will skip these.
// If already set as campaign variables, they don't need to be set here
// and the campaign settings will override any set here.

// Map variables (default is the main "Western Front" map)
// Set $VERDUN to 1 to use the Verdun map, otherwise 0.
$VERDUN = 0;
// Or Set $LAKE to 1 to use the Lake map, otherwise 0.
$LAKE = 0;
// Or Set $CHANNEL to 1 to use the Channel map, otherwise 0.
$CHANNEL = 0;
// Set $SHOWAF to 1 to show the identies of airfields, to 0 to mask them.
$SHOWAF = 1;
// Unreported settings variables
// Set $FinishFlightonlylanded to 1 if it is checked on the server, otherwise 0.
// This setting is not reported in the "settings" section of the log
$FinishFlightonlylanded = 0;

// End individual variables

//End Configuration
// Don't edit anything below this line unless you know what you are doing.

// initialize counting variables to zero
$numstart = 0 ; // number of starts (hopefully just 1)
$numhits = 0 ; // total number of hits
$numdamage = 0; // total number of damage events
$numkills = 0 ; // total number of kills
$numends = 0; // total number of mission end events
$numtakeoffs = 0 ; // total number of takeoffs
$numlandings = 0 ; // total number of landings
$numplayers = 0 ; // total number of players
$numgobjects = 0; // total number of game objects involved
$numevents = 0; // total number of events
$numgroups = 0; // total number of groups
$numB = 0; // number of boundary definitions
$numiaheaders = 0; // number of influence area headers

// set fixed data arrays
// these might need to be updated as new things are added 
// perhaps put these in a separate initialization file? 
// At the momoment only need the $Countries and $Coalitions arrays,
// so I moved the others to a "spare parts" file.

$Countries = array (
"000"=>"Neutral",
"101"=>"France",
"102"=>"Great Britain",
"103"=>"USA",
"104"=>"Italy",
"105"=>"Russia",
"501"=>"Germany ",
"502"=>"Austro-Hungary",
"610"=>"War Dogs Country",
"620"=>"Mercenaries Country",
"630"=>"Knights Country",
"640"=>"Corsairs Country",
"600"=>"Future Country");

// default coalitions... can be overridden by campaign settings
// see the example below for SKIESOFTHEEMPIIRES
$Coalitions = array (
"0"=>"Neutral",
"1"=>"Entente",
"2"=>"Central Powers",
"3"=>"War Dogs",
"4"=>"Mercenaries",
"5"=>"Knights",
"6"=>"Corsairs",
"7"=>"Future");

// campaign settings
if ($YANKEEDOODLE) {
   $FinishFlightonlylanded = 1;
   $SHOWAF = 1;
   $VERDUN = 0;
   $LAKE = 0;
   $CHANNEL = 0;
}

if ($BLOODYAPRIL) {
   $FinishFlightonlylanded = 1;
   $SHOWAF = 1;
   $VERDUN = 0;
   $LAKE = 0;
   $CHANNEL = 0;
}

if ($SKIESOFTHEEMPIRES) {
   $FinishFlightonlylanded = 1;
   $SHOWAF = 0;
   $VERDUN = 1;
   $LAKE = 0;
   $CHANNEL = 0;
   $Coalitions = array (
   "0"=>"Neutral",
   "1"=>"British Commonwealth & Allied Forces",
   "2"=>"U.S.A and Central Alliance",
   "3"=>"War Dogs",
   "4"=>"Mercenaries",
   "5"=>"Knights",
   "6"=>"Corsairs",
   "7"=>"Future");
}

if ($FLANDERSEAGLES) {
   $FinishFlightonlylanded = 1;
   $SHOWAF = 1;
   $VERDUN = 0;
   $LAKE = 0;
   $CHANNEL = 1;
}

// select appropriate locations file
// Chose map: Verdun, Lake, Channel or default to main Western Front map
if ($VERDUN) {
   $LOCATIONSFILE = "Verdun_locations.csv";
   } elseif ($LAKE) {
   $LOCATIONSFILE = "Lake_locations.csv";
   } elseif ($CHANNEL) {
   $LOCATIONSFILE = "Channel_all_locations.csv";
   } else { // default
   $LOCATIONSFILE = "RoF_locations.csv";
}

// now that we know which to use, read in the locations file
GETLOCATIONS($LOCATIONSFILE);

// now get to work on the log itself
if (file_exists("$LOGFILE")) {
// the main program is simple - only four stages
   READLOG($LOGFILE); // read the logfile
   PARSE($numlines); // parse the logfile 
   PROCESS($numlines); // manipulate the data to extract the stats we want
   OUTPUT(); // display a mission report
} else {
   echo("Could not open $LOGFILE");
}
// done
// record the ending time
$end=microtime();
$end=explode(" ",$end);
$end=$end[1]+$end[0];
//printf("<p>Page was generated by PHP %s in %f seconds</p>\n",phpversion(),$end-$start); 
printf("<p>Report generated in %.1f seconds</p>\n",$end-$start); 

// Thus endeth the main program - the remainder is just functions and a borrowed class.
// Of course all the interesting stuff happens in the functions and the borrowed class.

// CLASS for point-in-polygon calculations... not my code. 
// The code is from http://www.assemblysys.com/dataServices/php_pointinpolygon.php
// This is much neater than my attempts. :)
// Besides, I don't do OO stuff.  I'm too old and fixed in my ways for that.
// The only changes I made were to return "inside" rather than "vertex" or "boundary".
// Note it uses x,y where we use x,z.  No problem.  Flip your perspective 90 degrees.  
// However it has an odd format for its points that we need to match,
// and it *does* need to be placed ahead of where it may be called.
// That is why it is placed early.  It refused to work at the end of this file.  :)
//

class pointLocation {
    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices

    function pointLocation() {
    }


        function pointInPolygon($point, $polygon, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;

        // Transform string coordinates into arrays with x and y values
        $point = $this->pointStringToCoordinates($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex);
        }

        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
           // return "vertex";
            return "inside";
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1];
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                //return "boundary";
                return "inside";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    //return "boundary";
                    return "inside";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is even, then it's in the polygon.
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }



    function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }

    }


    function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }


}
// End class pointLocation borrowed code


// FUNCTIONS (in roughly the order they are used)
// First GETLOCATIONS, READLOG and PARSE
// Then the functions that PARSE calls for each AType record
// Then PROCESS and the functions that it calls
// Then the functions that OUTPUT calls
// and finally OUTPUT itself

function GETLOCATIONS($LOCATIONSFILE) {
// Create a line-by-line array from a tab-separated locations file
   global $Locs; // locations
   global $numlocs; // number of locations
   global $LID; // location ID
   global $LX; // location X coordinate
   global $LZ; // location Z coordinate
   global $LName; // location name

// example
// LID  LX      LZ      LName
// 51   171499  60879.05        "Acheler"

   $Locs = file("$LOCATIONSFILE");
   $numlocs = count($Locs);
   for ($i=0; $i<$numlocs; $i++) {
      $Part = explode("\t",$Locs[$i],4);
      $LID[$i] = $Part[0];
      $LX[$i] = $Part[1];
      $LZ[$i] = $Part[2];
      $LName[$i] = preg_replace("/\"/","",$Part[3]); // strip off quotes
//      echo "LID = $LID[$i], LX = $LX[$i], LZ = $LZ[$i], LName = $LName[$i]<br>\n";
   }
}

function READLOG($LOGFILE) {
// Create a line-by-line array from the log file
   global $Log; // log lines
   global $numlines; // number of log lines
   $Log = file("$LOGFILE");
   $numlines = count($Log);
}

function PARSE($numlines) {
// This is the parser function, as if you couldn't have guessed
// more like a deconstructor... it breaks the lines into their core categories
// for further deconstruction into meaningful elements
// at the moment the data goes into in-memory arrays
// later we'll put much of this into a DB for permanent storage of campaign missions
   global $numlines; // number of log lines
   global $Log; // log lines
   global $Ticks; // time since start of mission in 1/50 sec ticks - begins each log line
   global $Startticks; // time mission started (expected to be 0)
   global $endticks; // time mission ended
   global $Part; // array to hold parts of log lines passed to functions
   global $AType; // category of information contained in this line

   // grab one line at a time from the Log array and process it from the top
   // use functions to parse the lines into global data arrays as we go along

   for ($i=0; $i<$numlines; $i++) {

      // get time for each log line
      // $Ticks is the time in 1/50 sec increments since mission start
      $Log[$i] = substr($Log[$i],2); // trim the "T:" leader off each line
      $Part = explode(" AType:",$Log[$i],2); // split line into time and remainder at " AType"
      $Ticks[$i] = $Part[0];
      $Part = explode(" ",$Part[1],2); // split into AType and remainder at space
      $AType[$i] = $Part[0];

      // there are only seventeen types of lines to parse, the ATypes
      if ("$AType[$i]" == "0") { START($i); }
      elseif ("$AType[$i]" == "1") { HIT($i); }
      elseif ("$AType[$i]" == "2") { DAMAGE($i); }
      elseif ("$AType[$i]" == "3") { KILL($i); }
      elseif ("$AType[$i]" == "4") { PLAYER_MISSION_END($i); }
      elseif ("$AType[$i]" == "5") { TAKEOFF($i); }
      elseif ("$AType[$i]" == "6") { LANDING($i); }
      elseif ("$AType[$i]" == "7") { MISSION_END($i); }
      elseif ("$AType[$i]" == "8") { MISSION_OBJECTIVE($i); }
      elseif ("$AType[$i]" == "9") { AIRFIELD($i); }
      elseif ("$AType[$i]" == "10") { PLAYERPLANE($i); }
      elseif ("$AType[$i]" == "11") { GROUPINIT($i); }
      elseif ("$AType[$i]" == "12") { GAMEOBJECTINVOLVED($i); }
      elseif ("$AType[$i]" == "13") { INFLUENCEAREA_HEADER($i); }
      elseif ("$AType[$i]" == "14") { INFLUENCEAREA_BOUNDARY($i); }
      elseif ("$AType[$i]" == "15") { VERSION($i); }
      elseif ("$AType[$i]" == "16") { BOTID($i); }
      else { UNKNOWN($i); }
   } // end of for loop
} // end of parse function

// FUNCTIONS called by the PARSE function:

function START($i) { // AType:0
   global $numstart; // number of starts (hopefully just 1)
   global $Sline; // line number for each start
   global $numevents; // number of mission events
   global $EVline; // lines that define mission events
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Startticks; // time mission started (expected to be 0)
   global $Part; // parts of log lines
   global $GDate; // game date at start of mission e.g. 1917.9.23
   global $GTime; // game time at start of mission e.g. 6:30:0
   global $MFile; // mission file location and name
   global $MID; // unknown - perhaps a mission ID?
   global $GType; // game type 0 = single, 1 = coop, 2 = dogfight, 3 = custom
   global $CNTRS; // countries and their coalitions as a string
   global $SETTS; // game settings where 0 = off 1 = on
   global $MODS; // mods 0 = off, 1 = on
   global $MissionID; // mission ID (name-date-time)

// example
// T:0 AType:0 GDate:1917.9.23 GTime:6:30:0 MFile:Multiplayer/Cooperative/September-storm-1v2.msnbin MID: GType:1 CNTRS:0:0,101:1,102:1,103:1,104:1,105:1,501:2,502:2,600:7,610:3,620:4,630:5,640:6 SETTS:00000001000000000100000000 MODS:0

   $Startticks = $Ticks[$i];
   // nibble away from the left end of the line, extracting data as we go
   $Part[0] = substr($Part[1],6); // trim the "GDate:" leader off this line
   $Part = explode(" GTime:",$Part[0],2); // split into GDate and remainder at " GTime:"
   $GDate = $Part[0];
   $Part = explode(" MFile:",$Part[1],2); // split into GTime and remainder at " MFile:"
   $GTime = $Part[0];
   $Part = explode(" MID:",$Part[1],2); // split into MFile and remainder at " MID:"
   $MFile = $Part[0];
   $Part = explode(" GType:",$Part[1],2); // split into MID and remainder at " GType"
   $MID = $Part[0];
   $Part = explode(" CNTRS:",$Part[1],2); // split into GType and remainder at " CNTRS:"
   $GType = $Part[0];
   $Part=explode(" SETTS:",$Part[1],2); // split into CNTRS and remainder at " SETTS:"
   $CNTRS = $Part[0];
   $Part=explode(" MODS:",$Part[1],2); // split into SETTS and remainder at " MODS:
   $SETTS = $Part[0];
   $MODS = rtrim($Part[1]); 
   // construct a mission ID from components 
   $Part = explode("/",$MFile,3); // split $MID into three parts at "/"
   $Part = explode(".msnbin",$Part[2],2); // trim off the .msnbin safely
   $MissionID = $Part[0] . "-" . $GDate . "-" . $GTime; // append date and time
   $Sline[$numstart] = $i ;
   ++$numstart;
   $EVline[$numevents] = $i ;
   ++$numevents;
}

function HIT($i) { // AType:1
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $AMMO; // what hit
   global $AID; // attacker ID in this context
   global $TID; // target ID
   global $numhits; // number of hits
   global $Hline; // lines that define hits

// example
// T:112542 AType:1 AMMO:explosion AID:1252352 TID:874496

   $Part[0] = substr($Part[1],5); // trim the "AMMO:" leader off this line
   $Part = explode(" AID:",$Part[0],2); // split into AMMO and remainder at " AID:"
   $AMMO[$i] = $Part[0];
   $Part = explode(" TID:",$Part[1],2); // split into AID and remainder at " TID:"
   $AID[$i] = $Part[0];
   $TID[$i] = rtrim($Part[1]);
   // add line number to Hline array
   $Hline[$numhits] = $i ;
   // add one to running total of kills
   ++$numhits;
// echo ("<p>HIT $Ticks[$i] $AMMO[$i] $AID[$i] $TID[$i]</p>\n");
}

function DAMAGE($i) { // AType:2
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $DMG; // damage
   global $AID; // attacker ID
   global $TID; // target ID
   global $POS; // position x,y,z
   global $numdamage; // number of damage events
   global $Dline;  // lines that define damage events

// example
// T:172502 AType:2 DMG:0.132 AID:-1 TID:1252352 POS(247666.016,29.535,84503.578)

   $Part[0] = substr($Part[1],4); // trim the "DMG:" leader off this line
   $Part = explode(" AID:",$Part[0],2); // split into DMG and remainder at " AID:"
   $DMG[$i] = $Part[0];
   $Part = explode(" TID:",$Part[1],2); // split into AID and remainder at " TID:"
   $AID[$i] = $Part[0];
   $Part = explode(" POS",$Part[1],2); // split into TID and POS at " POS"
   $TID[$i] = $Part[0];
   $POS[$i] = rtrim($Part[1]);
   // add line number to Dline array
   $Dline[$numdamage] = $i ;
   // add one to running total of kills
   ++$numdamage;
// echo ("<p>DAMAGE $Ticks[$i] $DMG[$i] $AID[$i] $TID[$i] $POS[$i]</p>\n");
}

function KILL($i) { // AType:3
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $AID; // attacker ID in this context
   global $TID; // target ID
   global $POS; // position x,y,z
   global $numkills; // number of kills
   global $Kline; //  lines that define kills
   global $numevents; // number of mission events
   global $EVline; // lines that define mission events

// example
// T:85853 AType:3 AID:-1 TID:223244 POS(242985.125,292.674,59563.691)

   $Part[0] = substr($Part[1],4); // trim the "AID:" leader off this line
   $Part = explode(" TID:",$Part[0],2); // split into AID and remainder at " TID:"
   $AID[$i] = $Part[0];
   $Part = explode(" POS",$Part[1],2); // split into TID and POS at " POS"
   $TID[$i] = $Part[0];
   $POS[$i] = rtrim($Part[1]);
   // add line number to Kline array
   $Kline[$numkills] = $i ;
   // add one to running total of kills
   ++$numkills;
   $EVline[$numevents] = $i ;
   ++$numevents;
//   echo ("<p>KILL $Ticks[$i] $AID[$i] $TID[$i] $POS[$i]</p>\n");
}

function PLAYER_MISSION_END($i) { // AType:4
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $PLID; // player plane id 
   global $PID; // plane ID (whether bot or player)
   global $BUL; // # of bullets
   global $SH; // unknown
   global $BOMB; // # of bombs
   global $RCT; // unknown
   global $POS; // position x,y,z
   global $numends; // number of mission ends
   global $Eline; // lines that define mission ends

// example
// T:177981 AType:4 PLID:1282048 PID:1283072 BUL:500 SH:0 BOMB:0 RCT:0 (232645.828,58.892,43200.594)

   $Part[0] = substr($Part[1],5); // trim the "PLID:" leader off this line
   $Part = explode(" PID:",$Part[0],2); // split into PLID and remainder at " PID:"
   $PLID[$i] = $Part[0];
   $Part = explode(" BUL:",$Part[1],2); // split into PID and remainder at " BUL:"
   $PID[$i] = $Part[0];
   $Part = explode(" SH:",$Part[1],2); // split into BUL and remainder at " SH:"
   $BUL[$i] = $Part[0];
   $Part = explode(" BOMB:",$Part[1],2); // split into SH and remainder at " BOMB:"
   $SH[$i] = $Part[0];
   $Part = explode(" RCT:",$Part[1],2); // split into SH and remainder at " RCT:"
   $BOMB[$i] = $Part[0];
   $Part = explode(" ",$Part[1],2); // split into RCT and POS at space
   $RCT[$i] = $Part[0];
   $POS[$i] = rtrim($Part[1]);
   // add line number to Eline array
   $Eline[$numends] = $i ;
   // add one to running total of mission ending events
   ++$numends;
// echo ("<p>PLAYER_MISSION_END $Ticks[$i] $PLID[$i] $PID[$i] $BUL[$i] $SH[$i] $BOMB[$i] $RCT[$i] $POS[$i]</p>\n");
}

function TAKEOFF($i) { // AType:5
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $PID; // plane ID (whether bot or player)
   global $POS; // position x,y,z
   global $numtakeoffs; // number of takeoffs
   global $Tline;  // takeoff lines
   global $numevents; // number of mission events
   global $EVline; // lines that define mission events

// example 
// T:140410 AType:5 PID:223253 POS(246859.891, 42.146, 68843.102)

   $Part[0] = substr($Part[1],4); // trim the "PID:" leader off this line
   $Part = explode(" POS",$Part[0],2); // split into PID and POS at " POS"
   $PID[$i] = $Part[0]; 
   $POS[$i] = rtrim($Part[1]);
   $Tline[$numtakeoffs] = $i ; 
   ++$numtakeoffs;
   $EVline[$numevents] = $i ;
   ++$numevents;
//   echo ("<p>TAKEOFF $Ticks[$i] $PID[$i] $POS[$i]</p>\n");
}

function LANDING($i) { // AType:6
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $PID; // plane ID (whether bot or player)
   global $POS; // position x,y,z
   global $numlandings; // number of landings
   global $Lline;  // landing lines
   global $numevents; // number of mission events
   global $EVline; // lines that define mission events

// example
// T:71580 AType:6 PID:223245 POS(243148.469, 24.424, 57384.961)

   $Part[0] = substr($Part[1],4); // trim the "PID:" leader off this line
   $Part = explode(" POS",$Part[0],2); // split into PID and POS at " POS"
   $PID[$i] = $Part[0]; 
   $POS[$i] = rtrim($Part[1]);
//   echo ("<p>LANDING $Ticks[$i] $PID[$i] $POS[$i] $numevents</p>\n");
   $Lline[$numlandings] = $i ; 
   ++$numlandings;
   $EVline[$numevents] = $i ;
   ++$numevents;
}

    
function MISSION_END($i) { // AType:7
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $endticks; // time mission ended
   global $numevents; // number of mission events
   global $EVline; // lines that define mission events

// example
// T:177981 AType:7 
   $endticks = $Ticks[$i]; 
   $EVline[$numevents] = $i ;
   ++$numevents;
// echo ("<p>MISSION_END $endticks</p>\n");
}

function MISSION_OBJECTIVE($i) { // Atype:8
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $OBJID; // objective ID
   global $COAL; // coalition ID
   global $TYPE; // objective type - primary or secondary
   global $RES; // result - objective achieved or not

// examples
// T:37907 AType:8 OBJID:39 POS(273490.000,32.018,95596.297) COAL:1 TYPE:0 RES:1
// T:37907 AType:8 OBJID:40 POS(273513.000,32.018,95676.203) COAL:2 TYPE:0 RES:0

   $Part[1] = substr($Part[1],6); // trim the "OBJID:" leader off this line
   $Part = explode(" POS",$Part[1],2); // split into OBJID and remainder at " POS"
   $OBJID[$i] = $Part[0];
   $Part = explode(" COAL:",$Part[1],2); // split into POS and remainder at " COAL:"
   $POS[$i] = $Part[0];
   $Part = explode(" TYPE:",$Part[1],2); // split into COAL and remainder at " TYPE:"
   $COAL[$i] = $Part[0];
   $Part = explode(" RES:",$Part[1],2); // split into TYPE and RES at " RES:"
   $TYPE[$i] = $Part[0];
   $RES[$i] = rtrim($Part[1]);
//   echo ("<p>MISSION_OBJECTIVE $Ticks[$i] $OBJID[$i] $POS[$i] $COAL[$i] $TYPE[$i] $RES[$i]</p>\n");
}

function AIRFIELD($i) { // AType:9
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $AID; // airfield ID in this context
   global $COUNTRY; // country ID
   global $POS; // position x,y,z
   global $IDS; // player profile ID/ plane ID

// example line
// T:10 AType:9 AID:481280 COUNTRY:501 POS(247744.000, 27.389, 84529.102) IDS()

   $Part[1] = substr($Part[1],4); // trim the "AID:" leader off this line
   $Part = explode(" COUNTRY:",$Part[1],2); // split into AID and remainder at " COUNTRY:"
   $AID[$i] = $Part[0];
   $Part = explode(" POS",$Part[1],2); // split into COUNTRY and remainder at " POS"
   $COUNTRY[$i] = $Part[0];
   $Part = explode(" IDS",$Part[1],2); // split into POS and IDS at " IDS"
   $POS[$i] = $Part[0];
   $IDS[$i] = rtrim($Part[1]);
// echo ("<p>AIRFIELD $Ticks[$i] $AID[$i] $COUNTRY[$i] $POS[$i] $IDS[$i]</p>\n");
}

function PLAYERPLANE($i) { // AType:10
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $Planes;
   global $PLID; // player plane id 
   global $PID; // plane ID (whether bot or player)
   global $BUL; // # of bullets
   global $SH; // unknown
   global $BOMB; // # of bombs
   global $RCT; // unknown
   global $POS; // position x,y,z
   global $IDS; // player profile ID/ plane ID
   global $LOGIN; // player account ID
   global $NAME; // player profile name
   global $TYPE; // type of plane in this context
   global $COUNTRY; // country ID
   global $FORM; // unknown - perhaps formation?
   global $FIELD; // unknown - perhaps type of field?
   global $INAIR; // unknown - perhaps airstart?
   global $PARENT; // unkown
   global $numplayers; // number of players
   global $Pline;  // lines that define players

// example line
// Type:10 PLID:1252352 PID:1253376 BUL:2000 SH:0 BOMB:7 RCT:0 (247592.031,29.269,84643.422) IDS:0c1459c5-5a39-411e-b03c-9149676c2f82 LOGIN:18c61e14-c0b2-4c70-9a8a-56f371fb85eb NAME:Tushka:69GIAP TYPE:Gotha G.V COUNTRY:501 FORM:0 FIELD:0 INAIR:0 PARENT:-1

   // nibble away from the left side of the line, extracting data as we go
   $Part[1] = substr($Part[1],5); // trim the "PLID:" leader off this line
   $Part = explode(" PID:",$Part[1],2); // split into PLID (planeID) and remainder at " PID:"
   $PLID[$i] = $Part[0];
   $Part = explode(" BUL:",$Part[1],2); // split into PID (playerID) and remainder at " BUL:"
   $PID[$i] = $Part[0];
   $Part = explode(" SH:",$Part[1],2); // split into BUL (bullets) and remainder  at " SH:"
   $BUL[$i] = $Part[0];
   $Part = explode(" BOMB:",$Part[1],2); // split into SH (?) and remainder  at " BOMB:"
   $SH[$i] = $Part[0];
   $Part = explode(" RCT:",$Part[1],2); // split into BOMB (bombs) and remainder at " RCT:"
   $BOMB[$i] = $Part[0];
   $Part = explode(" ",$Part[1],3); // split into RCT (?) and remainder at spaces
   $RCT[$i] = $Part[0];
   $POS[$i] = $Part[1];
   $Part[2] = substr($Part[2],4); // trim the "IDS:" leader off remainder
   $Part = explode(" LOGIN:",$Part[2],2); // split into IDS (profile) and remainder at " LOGIN:"
   $IDS[$i] = $Part[0];
   $Part = explode(" NAME:",$Part[1],2); // split into LOGIN (login) and remainder at " NAME:"
   $LOGIN[$i] = $Part[0];
   $Part = explode(" TYPE:",$Part[1],2); // split into NAME and remainder at " TYPE:"
   $NAME[$i] = $Part[0];
   $Part = explode(" COUNTRY:",$Part[1],2); // split into TYPE and remainder at " COUNTRY:"
   $TYPE[$i] = $Part[0];
   $Part = explode(" FORM:",$Part[1],2); // split into COUNTRY (country index) and remainder at " FORM:"
   $COUNTRY[$i] = $Part[0];
   $Part = explode(" FIELD:",$Part[1],2); // split into FORM (?) and remainder  at " FIELD:"
   $FORM[$i] = $Part[0];
   $Part = explode(" INAIR:",$Part[1],2); // split into FIELD (?) and remainder at " INAIR:"
   $FIELD[$i] = $Part[0];
   $Part = explode(" PARENT:",$Part[1],2); // split into INAIR (airstart?) and remainder at " PARENT:"
   $INAIR[$i] = $Part[0];
   $PARENT[$i] = rtrim($Part[1]);
   // note which line number refers to this player
   $Pline[$numplayers] = $i ;
   // add one to running total of players
   ++$numplayers;
//   ECHO ("<p>PLAYERPLANE $Ticks[$i] $PLID[$i] $PID[$i] $BUL[$i] $SH[$i] $BOMB[$i] $RCT[$i] $POS[$i] $IDS[$i] $LOGIN[$i] $NAME[$i] $TYPE[$i] $COUNTRY[$i] $FORM[$i] $FIELD[$i] $INAIR[$i] $PARENT[$i]<p>\n");
}

function GROUPINIT($i) { // AType:11
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $IDS; // plane ID
   global $GID; // group ID
   global $LID; // lead plane ID
   global $numgroups; // total number of groups
   global $Gline; // lines defining groups
      
   // examples
//T:80789 AType:11 GID:2220032 IDS:448512,1991680,2016256 LID:448512
//T:80789 AType:11 GID:2221056 IDS:277528,277530 LID:277528
// These are groups of AI planes.

//   echo ("<p>GROUPINIT $Ticks[$i] $Part[1]</p>\n");
   // nibble away from the left side of the line, extracting data as we go
   $Part[1] = substr($Part[1],4); // trim the "GID:" leader off this line
   $Part = explode(" IDS:",$Part[1],2); // split into GID (GroupID) and remainder at " IDS:"
   $GID[$i] = $Part[0];
   $Part = explode(" LID:",$Part[1],2); // split into IDS and LID at " LID:"
   $IDS[$i] = $Part[0];
   $LID[$i] = rtrim($Part[1]);
//   echo ("<p>GROUPINIT $numgroups $Ticks[$i] $GID[$i] $IDS[$i] $LID[$i]</p>\n");
   // note which line number refers to this group
   $Gline[$numgroups] = $i ;
   // add one to running total of groups
   ++$numgroups;
} 

function GAMEOBJECTINVOLVED($i) { // AType:12
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $ID; // object ID
   global $TYPE; // type of object in this context
   global $COUNTRY; // country ID
   global $NAME; // player profile name
   global $PID; // plane ID (whether bot or player)
   global $numgobjects; // number of game objects involved
   global $GOline; // lines defining game objects

// sample line - note, this is actually an airfield
// T:112473 AType:12 ID:14336 TYPE:fr_med COUNTRY:102 NAME:Bruay PID:-1^M

   $Part[1] = substr($Part[1],3); // trim the "ID:" leader off this line
   $Part = explode(" TYPE:",$Part[1],2); // split into ID (objectID) and remainder at " TYPE:"
   $ID[$i] = $Part[0];
   $Part = explode(" COUNTRY:",$Part[1],2); // split into TYPE and remainder at " COUNTRY:"
   $TYPE[$i] = $Part[0];
   $Part = explode(" NAME:",$Part[1],2); // split into COUNTY and remainder at " NAME:"
   $COUNTRY[$i] = $Part[0];
   $Part = explode(" PID:",$Part[1],2); // split into NAME and remainder at " PID:"
   $NAME[$i] = $Part[0];
   $PID[$i] = rtrim($Part[1]);
   // note which line number refers to this game object
   $GOline[$numgobjects] = $i ;
   // add one to running total of game objects
   ++$numgobjects;
//   echo ("<p>GAMEOBJECTINVOLVED Ticks = $Ticks[$i] ID = $ID[$i] TYPE = $TYPE[$i] COUNTRY = $COUNTRY[$i] NAME = $NAME[$i] PID = $PID[$i]<br>\n");
}

function INFLUENCEAREA_HEADER($i) { // AType:13
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $AID; // area ID in this context
   global $COUNTRY; // country ID
   global $ENABLED; // influence area enabled or not
   global $BC; // By Coalition inflight count of planes in area
   global $numiaheaders; // number of influence area headers
   global $IAHline; // lines defining Influence Area Headers

// example 
// T:174445 AType:13 AID:1239040 COUNTRY:501 ENABLED:1 BC(0,0,2,0,0,0,0,0)

   $Part[1] = substr($Part[1],4); // trim the "AID:" leader off this line
   $Part = explode(" COUNTRY:",$Part[1],2); // split into AID and remainder at " COUNTRY:"
   $AID[$i] = $Part[0];
   $Part = explode(" ENABLED:",$Part[1],2); // split into COUNTRY and remainder at " ENABLED:"
   $COUNTRY[$i] = $Part[0];
   $Part = explode(" BC",$Part[1],2); // split into ENABLED and BC at " BC"
   $ENABLED[$i] = $Part[0];
   $BC[$i] = rtrim($Part[1]);
   // note which line number refers to this Influence Area Header
   $IAHline[$numiaheaders] = $i ;
   // add one to running total of influence area headers count
   ++$numiaheaders;
// echo ("INFLUENCEAREA_HEADER $Ticks[$i] $AID[$i] $COUNTRY[$i] $ENABLED[$i] $BC[$i]</p>\n");
}

function INFLUENCEAREA_BOUNDARY($i) { // AType:14
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $AID; // area ID in this context
   global $BP; // boundary points
   global $numB; // number of boundary definitions
   global $Bline; // lines defining area boundaries
   global $Boundary0; // array of point pairs defining boundary 0
   global $Boundary1; // array of point pairs defining boundary 1
   global $BoundaryArray; // array of point pairs defining boundary

// example
//T:1 AType:14 AID:1241088 BP((200850.4,118.1,61015.1),(186052.6,118.1,52807.5),(185346.4,118.1,2427.4),(281753.6,118.1,4016.5),(281168.5,118.1,68537.0),(273736.8,118.1,72267.7),(254926.8,118.1,64523.4),(245927.2,118.1,58008.8),(238328.3,118.1,58978.3),(231793.4,118.1,60857.7),(221480.8,118.1,61415.5),(211645.6,118.1,64372.1))

   $Part[1] = substr($Part[1],4); // trim the "AID:" leader off this line
   $Part = explode(" BP",$Part[1],2); // split into AID and BP at " BP"
   $AID[$i] = $Part[0];
   $BP[$i] = rtrim($Part[1]);
//   echo ("<p>INFLUENCEAREA_BOUNDARY $Ticks[$i] $AID[$i] $BP[$i] </p>\n");
   // OK, now we need to convert the $BP[$i]s into the kind of arrays that
   // the pointLocation class is expecting.
   $BPA = preg_replace ("/,/", " ", $BP[$i]);
   //echo "$BPA<br>\n";
   // replace internal floating-point numbers with spaces
   $BPA = preg_replace ("/ \d+\D\d+ /", " ", $BPA);
   //echo "$BPA<br>\n";
   // replace point separators ") (" with spaces
   $BPA = preg_replace ("/\) \(/", ",", $BPA);
   //echo "$BPA<br>\n";
   // eliminate closing "))"
   $BPA = preg_replace ("/\)\)/", "", $BPA);
   //echo "$BPA<br>\n";
   // eliminate opening "(("
   $BPA = preg_replace ("/\(\(/", "", $BPA);
   //echo "$BPA<br>\n";
   $numpoints = substr_count($BPA, ",") +1;
   //echo "$numpoints numpoints<br>\n";
   $Boundary = explode(",",$BPA);

   // add last point = first point to close the loop
   $Boundary[$numpoints] = $Boundary[0];

   $BoundaryArray[$numB] = $Boundary;

   // debugging
   //for ($j = 0; $j < $numpoints; ++$j ){
   //   echo "Boundary = $Boundary[$j]<br>\n";
   //}
   //echo "Boundary = $Boundary[$numpoints]<br>\n";

   $Bline[$numB] = $i;
   ++$numB;
   //  echo "numB = $numB<br>\n";
}

function VERSION($i) { // AType:15
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $VER; // version (of what?)

// example
// T:0 AType:15 VER:15

   $VER[$i] = rtrim(substr($Part[1],4)); // trim the "VER:" leader off this line
// echo ("<p>VERSION $Ticks[$i] $VER[$i]</p>\n");
}

function BOTID($i) { // AType:16
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Part; // parts of log lines
   global $BOTID; // Bot ID?
   global $POS; // position x,y,z

// example
// T:21544 AType:16 BOTID:303114 POS(69075.984,168.115,197697.703)
// seems to be a despawn of a plane after pilot finishes (or disconnects)?
// often follows PLAYER_MISSION_END
// don't currently see a need for this information
// though it might be needed for multiple sorties.

//   echo ("$Ticks[$i] $Part[1]<br>\n");
   $Part[1] = rtrim(substr($Part[1],6)); // trim the "BOTID:" leader off this line
//   echo ("BOTID $Ticks[$i] $Part[1]<br>\n");
   $Part = explode(" POS",$Part[1],2); // split into BOTID and POS at " POS"
   $BOTID[$i] = $Part[0];
   $POS[$i] = rtrim($Part[1]);
//   echo ("BOTID $Ticks[$i] $BOTID[$i] $POS[$i]<br>\n");
}

function UNKNOWN($i) { // new AType
   echo "Unknown AType on line $i<br>\n";
}

// end of FUNCTIONS called by PARSE

function PROCESS() {
// massage the raw data into more useful (and smaller) arrays
// variables we need
   global $CNTRS; // countries and their coalitions as a string
   global $Part; // array to hold parts of log lines
   global $GTime; // game time at start of mission e.g. 6:30:0
   global $Startticks; // game start time in number of ticks since midnight
   global $numplayers; // number of players
   global $numhits; // number of hits
   global $numkills; // number of kills

//   echo "<p>PROCESSING...</p>\n"; 

   // calculate game time in ticks where 1 sec = 50 tick thus 1 min = 3000 ticks and 1 hr = 180000 ticks
   // start by calculating ticks equivalent of the starting time, eg: 6:30:0
   $Part = explode(":",$GTime,3); // split GTime into three parts at ":"
   $Startticks = ($Part[0] * 180000) + ($Part[1] * 3000) + ($Part[2] * 50);
   // call other functions needed to produce SEOW-like stats
   CNTRS($CNTRS);
   DEATHS($numplayers);
   WOUNDS($numplayers);
   ENDS($numplayers);
   HITSTATS($numplayers);
   KILLS($numkills);
   LASTHIT($numhits);
}

// FUNCTIONS called by PROCESS:  CNTRS, DEATHS, ENDS, HITSTATS, KILLS, LASTHIT, and WOUNDS

function CNTRS($CNTRS) {
// assign countries to their coalitions
// presuming only one start line
   global $CNTRS; // countries and their coalitions as a string
   global $COUNTRY; // country ID
   global $COAL; // coalition ID
   global $CoCoal; // array of countries and their coalitions
   
// Redvo's example:
//0:0,101:2,102:1,103:2,104:1,105:1,501:2,502:2,600:0,610:0,620:0,630:0,640:0

   // split into country:coalition pair at the commas.  There will be 13.
   $arr = explode(",",$CNTRS,13); 
   // now split the pairs at the colon and assign to the $CoCal array.
   for ($i = 0; $i < 13; ++$i) {
     $arr2 = explode(":",$arr[$i],2);
     $CoCoal[$arr2[0]]=$arr2[1];
   }
}

function DEATHS($numplayers) {
// record player deaths
   global $Pline;  // lines that define players
   global $endticks; // time mission ended
   global $numkills; // number of kills
   global $Kline; //  lines that define kills
   global $TID; // target ID
   global $PID; // plane ID (whether bot or player)
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $POS; // position x,y,z
   global $objectname; // object name from PID/AID/TID
   global $Death; // dead players numbers
   global $Deathticks; // ticks when died
   global $Deathpos; // position where died

   // loop through players using $Pline index
   for ($i = 0; $i < $numplayers; ++$i) {
      $Death[$i] = "0";
      $j = $Pline[$i];
      $Deathticks[$i] = $endticks;
      // now loop through kills, looking for targetID match to playersID
      for($k = 0; $k < $numkills ; ++$k) {
         $l = $Kline[$k];
         // if TID matches PID, player is dead... record details
         // aha... PID is not unique!  Need to involve time.
         if (($TID[$l] == $PID[$j]) && ($Ticks[$l] >= $Ticks[$j]))         {
            $Death[$i] = "1";
            $Deathticks[$i] = $Ticks[$l];
            $Deathpos[$i] = $POS[$l];
         } 
      }
   }
}

function KILLS($numkills) {
// record gameobject kills
// this may supercede DEATHS and simplify or replace DEAD and CRASHED
// because it is more general and uses the same index as gameobjectsinvolved
   global $endticks; // time mission ended
   global $Kline; //  lines that define kills
   global $TID; // target ID
   global $ID; // object ID
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $POS; // position x,y,z
   global $numgobjects; // number of game objects involved
   global $GOline; // lines defining game objects
   global $objectname; // object name from PID/AID/TID
   global $numkills; // number of kills
   global $Killticks; // ticks when killed
   global $Killpos; // position where killed
   global $countryid; // country id
   global $Kcountryid; // country id of a killed object
   global $CoalID; // coalition ID
   global $numententelosses; // number of entente losses
   global $numcplosses; // number of central powers losses

   $numentenelosses = 0;
   $numcplosses = 0;
   // loop through gameobjects using the $GOline index
   for ($i = 0; $i < $numgobjects; ++$i) {
      $Kill[$i] = "0";
      $j = $GOline[$i];
      $Killticks[$i] = $endticks;
      // now loop through kills, looking for targetID match to playersID
      for($k = 0; $k < $numkills ; ++$k) {
         $l = $Kline[$k];
         // if TID matches PID, player/game object is dead... record details
         
         // aha... ID is not unique!  Need to make sure each object only dies once.
         if (($TID[$l] == $ID[$j]) && ($Kill[$i] == "0"))   {
            $Kill[$i] = "1";
            $Killticks[$i] = $Ticks[$l];
            $Killpos[$i] = $POS[$l];
//            echo "KILLS: Object $i, Ticks = $Killticks[$i], Position = $Killpos[$i] <br>\n";
            OBJECTCOUNTRYNAME($ID[$j],$Ticks[$l]);
//            echo "countryid = $countryid <br>\n";
              $Kcountryid[$k] = $countryid;
              COALITION($countryid);
           if ($CoalID == 1) {
              ++$numententelosses;
            }
            elseif ($CoalID == 2) {
              ++$numcplosses;
            }
            else {
               echo "Non-Entente or Central Powers KILLS<br>\n";
               echo "KILLS: Object $i, Ticks = $Killticks[$i], Position = $Killpos[$i] <br>\n";
            }
         }
      }
   }
//   echo "KILLS: numentenete losses = $numententelosses numcplosses = $numcplosses <br>\n";
}


function ENDS($numplayers) {
// record player mission end events (where available)
   global $Pline;  // lines that define players
   global $numends; // number of mission ends
   global $Eline; // lines that define mission ends
   global $PLID; // player plane id 
   global $BUL; // # of bullets
   global $BOMB; // # of bombs
   global $End; // player ended (or not)
   global $EndBUL; // unexpended bullets
   global $EndBOMB; // undropped bombs

   // loop through players
   for ($i = 0; $i < $numplayers; ++$i) {
      $End[$i] = "0";
      $EndBUL[$i] = "0";
      $EndBOMB[$i] = "0";
      $j = $Pline[$i];
      // loop through ends
      for ($k = 0; $k < $numends; ++$k) {
         $l = $Eline[$k];
         if ( $PLID[$j] == $PLID[$l] ) {
            $End[$i] = "1";
            $EndBUL[$i] = $BUL[$l];
            $EndBOMB[$i] = $BOMB[$l];
         }
      }
      if ("$End[$i]" == "0") {
         $EndBUL[$i] = $BUL[$j]; 
         $EndBOMB[$i] = $BOMB[$j];
      }  
   }
}

function HITSTATS($numplayers) {
// record hit statistics for each player
// note that turrets are recorded separately from player-pilots
   global $Pline;  // lines that define players
   global $BUL; // # of bullets
   global $BOMB; // # of bombs
   global $numhits; // number of hits
   global $Hline; // lines that define hits
   global $EndBUL; // unexpended bullets
   global $EndBOMB; // undropped bombs
   global $ShotBUL; // shot bullets
   global $DroppedBOMB; // dropped bombs
   global $HitBOMB; // bomb hits
   global $HitBUL; // bullet hits
   global $PLID; // player plane id 
   global $AID; // attacker ID in this context
   global $AMMO; // what hit

//   echo "HITSTATS:<br>\n";
   // loop through players
   for ($i = 0; $i < $numplayers; ++$i) {
      // work out how many bullets and bombs were expended
      $HitBOMB[$i] = 0;
      $HitBUL[$i] = 0;
      $j = $Pline[$i];
      $ShotBUL[$i] = $BUL[$j] - $EndBUL[$i];
      $DroppedBOMB[$i] = $BOMB[$j] - $EndBOMB[$i];
      // now total hits from bombs if any were carried.
      if ($BOMB[$j]) {
         // loop through hits, looking for explosions
         for ($k = 0; $k < $numhits; ++$k) {
            $l = $Hline[$k];
            if (($PLID[$j] == $AID[$l]) && ($AMMO[$l] == "explosion")) {
               ++$HitBOMB[$i];
            }
         }
         // avoid accuracy > 100%
         if ($HitBOMB[$i] > $DroppedBOMB[$i]) {
            $HitBOMB[$i] = $DroppedBOMB[$i];
         }
      }
      // loop through hits again (for all players this time), looking for BULLET hits
      for ($k = 0; $k < $numhits; ++$k) {
         $l = $Hline[$k];
         // look for bullet hits as attacker
         if (($PLID[$j] == $AID[$l]) && (substr($AMMO[$l],0,6) == "BULLET")) {
            ++$HitBUL[$i];
         }
      }
      // avoid accuracy > 100%
      if ($HitBUL[$i] > $ShotBUL[$i]) {
         $HitBUL[$i] = $ShotBUL[$i];
      }
   }
}

function LASTHIT($numhits) {
// track last game object/player to hit another game object
// this is used to attribute delayed kills from engine damage, fire, etc.
// in the future consider expanding this to record assists
   global $Hline; // lines that define hits
   global $numgobjects; // number of game objects involved
   global $GOline; // lines defining game objects
   global $ID; // object ID
   global $TID; // target ID
   global $AID; // attacker ID in this context
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $TYPE; // gameobject type in this context
   global $objecttype; // object type from PID/AID/TID
   global $playername; // player name from PLID
   global $Lasthitbyid; // ID of last attacker to hit player
   global $Lasthitby; // name or type of last attacker to hit player
   global $Killticks; // ticks when killed
   
//   echo "LASTHIT<br>\n";
   // loop through gameobjects 
   for ($i = 0; $i < $numgobjects; ++$i) {
      $j = $GOline[$i];
      $Lasthitbyid[$i] = "0";
      $LHTick[$i] = "0";
      // loop through hits
      for ($k = 0; $k < $numhits; ++$k) {
         $l = $Hline[$k];
         // look for last hit - but not after object already killed
         //if (($ID[$j] == $TID[$l]) && ($LHTick[$i] < $Ticks[$l])) {
         if (($ID[$j] == $TID[$l]) && ($LHTick[$i] < $Ticks[$l]) && ($Killticks[$i] >= $Ticks[$l])) {
            // ignore "Intrinsic" hits
            if ($AID[$l] > -1) {
               $Lasthitbyid[$i] = $AID[$l]; 
               $LHTick[$i] = $Ticks[$l];
            }
         }
      }
      $Lasthitby[$i] = "";
      if ($Lasthitbyid[$i] > 0) {
         OBJECTTYPE($Lasthitbyid[$i],$LHTick[$i]);
         playername($Lasthitbyid[$i],$LHTick[$i]);
//         echo "Object $i: Lasthitbyid[$i] = $Lasthitbyid[$i], LHtick[$i] = $LHTick[$i],<br>Ticks[j] =$Ticks[$j], TYPE[l] = $TYPE[$l], TYPE[j] = $TYPE[$j] Killticks[$i] = $Killticks[$i] objecttype = $objecttype playername = $playername<br>\n";
         if ($playername == "Vehicle") {
           $Lasthitby[$i] = $objecttype;
         } else {
           $Lasthitby[$i] = $playername;
         }
      }
   }
}

function WOUNDS($numplayers) {
// record damage to surviving players
   global $endticks; // time mission ended
   global $Pline;  // lines that define players
   global $numdamage; // number of damage events
   global $Dline;  // lines that define damage events
   global $TID; // target ID
   global $PID; // plane ID (whether bot or player)
   global $Ticks; // time since start of mission in 1/50 sec ticks - begins each log line
   global $POS; // position x,y,z
   global $DMG; // damage
   global $objectname; // object name from PID/AID/TID
   global $Wound; // array holding severity of wound
   global $Woundticks; // ticks when last wounded
   global $Woundpos; // position where last wounded

   // loop through players using $Pline index
//   echo "WOUNDS: (out of $numdamage lines)<br>\n";
   for ($i = 0; $i < $numplayers; ++$i) {
      $Wound[$i] = "0";
      $Woundticks[$i] = $endticks;
      $j = $Pline[$i];
      // now loop through damage, looking for targetID match to playersID
      for($k = 0; $k < $numdamage ; ++$k) {
         $l = $Dline[$k];
         // if TID matches PID, player is wounded... record details
         if ($TID[$l] == $PID[$j]) {
            $Wound[$i] = $Wound[$i] + $DMG[$l];
            $Woundticks[$i] = $Ticks[$l];
            $Woundpos[$i] = $POS[$l];
         } 
      }
//      echo "Player $i total wounds = $Wound[$i]<br>\n";
   }
}
// end of FUNCTIONS called by PROCESS

// FUNCTIONS called by OUTPUT (in alphabetical order):
// ACCURACY, ANORA, CLOCKTIME, COUNTRYNAME, CRASHED, DEAD, FATES,
// FLYING, GUNNER, LOSSES, OBJECTCOUNTRYNAME, OBJECTNAME, OBJECTTYPE,
// PLAYERNAME, TOFROM, WHERE,  WHOSEGUNNER, XYZ

function ACCURACY($i, $j) {
   // $i is playernumber
   // $j is linenumber defining that player.
   global $End; // player ended (or not)
   global $NAME; // player profile name
   global $TYPE; // gameobject type in this context
   global $ShotBUL; // shot bullets
   global $HitBUL; // bullet hits
   global $BOMB; // # of bombs
   global $DroppedBOMB; // dropped bombs
   global $HitBOMB; // bomb hits

   if (!$End[$i]) { // we have no end data
   echo "Note: $NAME[$j] has no player-mission-end data, so assume no ammo expended<br>\n";
   }
   if (preg_match('/^Turret/',$TYPE[$j])) { // player is a gunner
      if (!$ShotBUL[$i]) { // no bullets shot
         echo "gunner $NAME[$j] shot no bullets<br>\n";
      } else { // can calculate bullet stats
         $bulletaccuracy = ( 100 * $HitBUL[$i] / $ShotBUL[$i] );
         $bulletaccuracy = sprintf("%.2f",$bulletaccuracy);
         echo "gunner $NAME[$j] shot $ShotBUL[$i] bullets with $HitBUL[$i] hits for $bulletaccuracy% accuracy<br>\n";
      }
   }
   elseif (!$BOMB[$j]) { // no bombs aboard
      if (!$ShotBUL[$i]) { // no bullets shot
         echo "pilot $NAME[$j] shot no bullets and carried no bombs<br>\n";
      } else { // can calculate bullet stats
         $bulletaccuracy = ( 100 * $HitBUL[$i] / $ShotBUL[$i] );
         $bulletaccuracy = sprintf("%.2f",$bulletaccuracy);
         echo "pilot $NAME[$j] shot $ShotBUL[$i] bullets with $HitBUL[$i] hits for $bulletaccuracy% accuracy<br>\n";
      }
   } elseif (!$ShotBUL[$i]) { // no bullets shot
      if (!$DroppedBOMB[$i]) { // carried bombs, but didn't drop them
         echo "pilot $NAME[$j] shot no bullets and dropped no bombs<br>\n";
      } else { // can calculate bomb stats
      $bombaccuracy = ( 100 * $HitBOMB[$i] / $DroppedBOMB[$i] );
      $bombaccuracy = sprintf("%.2f",$bombaccuracy);
      echo "pilot $NAME[$j] shot no bullets but dropped $DroppedBOMB[$i] bombs with $HitBOMB[$i] hits for $bombaccuracy% accuracy<br>\n"; }
   } elseif (!$DroppedBOMB[$i]) { // shot but didn't drop
      $bulletaccuracy = ( 100 * $HitBUL[$i] / $ShotBUL[$i] );
      $bulletaccuracy = sprintf("%.2f",$bulletaccuracy);
      echo "pilot $NAME[$j] shot $ShotBUL[$i] bullets with $HitBUL[$i] hits for $bulletaccuracy% accuracy and dropped no bombs.<br>\n";
   } else { // shot and dropped
      $bulletaccuracy = ( 100 * $HitBUL[$i] / $ShotBUL[$i] );
      $bulletaccuracy = sprintf("%.2f",$bulletaccuracy);
      $bombaccuracy = ( 100 * $HitBOMB[$i] / $DroppedBOMB[$i] );
      $bombaccuracy = sprintf("%.2f",$bombaccuracy);
      echo "pilot $NAME[$j] shot $ShotBUL[$i] bullets with $HitBUL[$i] hits for $bulletaccuracy% accuracy and dropped $DroppedBOMB[$i] bombs with $HitBOMB[$i] hits for $bombaccuracy% accuracy<br>\n";
   }
}


function ANORA($word) {
// select proper article: "", "an" or "a"
// easy to extend if needed
   global $anora; // an or a

   // hacks to avoid an article with certain player names
   if ((substr($word,0,3) == "=69") || (substr($word,0,3) == "242") ||
     (substr($word,0,3) == "OZA") || (substr($word,0,3) == "Evi") ||
     (substr($word,0,3) == "Wil") || (substr($word,0,3) == "Tus") ||
     (substr($word,0,3) == "Cha") || (substr($word,0,3) == "JZ-") ||
     (substr($word,0,3) == "J18") || (substr($word,0,3) == "cro") ||
     (substr($word,0,3) == "_st") || (substr($word,0,3) == "VON") ||
     (substr($word,0,3) == "col") || (substr($word,0,3) == "vol") ||
     (substr($word,0,3) == "tyr") || (substr($word,0,3) == "LvA") ||
     (substr($word,0,3) == "=IR") || (substr($word,0,3) == "lew") ||
     (substr($word,0,3) == "hq ") || (substr($word,0,3) == "hq_") ||
     (substr($word,0,3) == "Duc") || (substr($word,0,3) == "WH_") ||
     (substr($word,0,3) == "K_L") || (substr($word,0,3) == "Hq_") ||
     (substr($word,0,3) == "LVA") || (substr($word,0,5) == "HeadT") ||
     (substr($word,0,3) == "=CA") || (substr($word,0,4) == "bfly") ||
     (substr($word,0,4) == "Otto") || (substr($word,0,5) == "Night") ||
     (substr($word,0,3) == "AB1") || (substr($word,0,5) == "Tozzi") ||
     (substr($word,0,3) == "LM_") || (substr($word,0,3) == "JaV") ||
     (substr($word,0,3) == "-NW") || (substr($word,0,3) == "Alg") ||
     (substr($word,0,3) == "Tan") || (substr($word,0,3) == "BSS") ||
     (substr($word,0,3) == "_BT") || (substr($word,0,3) == "BT_") ||
     (substr($word,0,3) == "The") || (substr($word,0,3) == "RED") ||
     (substr($word,0,4) == "Lord") || (substr($word,0,3) == "C6-") ||
     (substr($word,0,3) == "act") || (substr($word,0,4) == "N561") ||
     (substr($word,0,3) == "BH ") || (substr ($word,0,3) == "BH_") ||
     (substr($word,0,4) == "John") || (substr($word,0,3) == "=VA") ||
     (substr($word,0,3) == "Ron") || (substr($word,0,3) == "J2_") ||
     (substr($word,0,5) == "=III=") || (substr($word,0,4) == "Izra") ||
     (substr($word,0,4) == "Zach") || (substr($word,0,4) == "Star") ||
     (substr($word,0,4) == "zerw") || (substr($word,0,4) == "Rood") ||
     (substr($word,0,4) == "TeeK") || (substr($word,0,4) == "Robi") ||
     (substr($word,0,4) == "Baro") || (substr($word,0,4) == "Lark") ||
     (substr($word,0,4) == "Spa ") || (substr($word,0,4) == "Last") ||
     (substr($word,0,4) == "Par2") || (substr($word,0,4) == "Last") ||
     (substr($word,0,3) == "JG1") || (substr($word,0,3) == "J5_") ||
     (substr($word,0,3) == "CaK") || (substr($word,0,4) == "Flak") ||
     (substr($word,0,4) == "= CA")) {
        $anora = "";
   // by sound
   } elseif ((substr($word,0,1) == "A") || (substr($word,0,2) == "S." ) ||
     (substr($word,0,2) == "LM") || (substr($word,0,1) == "E") ||
     (substr($word,0,3) == "HMS") || (substr($word,0,3) == "R.E")) {
         $anora = "an";
   } else {
         $anora = "a";
   }
}

function CLOCKTIME($ticks) {
// convert $Ticks into 24 hr game time
   global $Startticks; // game start time in number of ticks since midnight
   global $clocktime; // 24 hr time
   
   // use a 24 hr clock
   if (($Startticks + $ticks) > 4320000) {
      $Totalticks = ($Startticks + $ticks) - 4320000;
   }  else {
      $Totalticks = $Startticks + $ticks;
   }
   $hr = (int)(($Totalticks) / 180000);
   $min = (int)((($Totalticks) - ($hr * 180000)) / 3000);
   $sec = (int)((($Totalticks) - ($hr * 180000) - ($min * 3000)) / 50);
   $clocktime = sprintf("%02d",$hr) . ":" . sprintf("%02d",$min) . ":" . sprintf("%02d",$sec); 
}

function COALITION($ckey) {
// look up coalitionID from country ID#
   global $CoCoal; // array of coalition names
   global $COUNTRY; // country ID
   global $CoalID; // coalition ID

   $CoalID = "";
   asort ($CoCoal);
   while (list ($key, $val) = each ($CoCoal)) {
      if ($ckey == $key) {
         $CoalID = $val;
      }
   }
}

function COALITIONNAME($ckey) {
// look up coalition name from country ID#
   global $Coalitions; // array of coalition names
   global $Coalitionname; // this coalition name 

   $coalitionname = "";
   asort ($Coalitions);
   while (list ($key, $val) = each ($Coalitions)) {
      if ($ckey == $key) {
         $Coalitionname = $val;
      }
   }
}

function COUNTRYNAME($ckey) {
// look up country name from ID#
// and also report the adjective form
   global $Countries;  // countries
   global $countryname; // country name
   global $countryadj;  // adjective form of country name  
   
   $countryname = "";
   $found = 0;

   asort ($Countries);
   while (list ($key, $val) = each ($Countries)) {
      if ($ckey == $key) {
         $countryname = $val;
         $found = 1;
      }
   }
   if ($ckey == "000") { $countryadj = "neutral";}
   if ($ckey == "101") { $countryadj = "French";}
   if ($ckey == "102") { $countryadj = "British";}
   if ($ckey == "103") { $countryadj = "American";}
   if ($ckey == "104") { $countryadj = "Italian";}
   if ($ckey == "105") { $countryadj = "Russian";}
   if ($ckey == "501") { $countryadj = "German"; }
   if ($ckey == "502") { $countryadj = "Austro-Hungarian";}
   if ($ckey == "600") { $countryadj = "Future";}
   if ($ckey == "610") { $countryadj = "War Dogs";}
   if ($ckey == "620") { $countryadj = "Mercenaries";}
   if ($ckey == "630") { $countryadj = "Knights";}
   if ($ckey == "640") { $countryadj = "Corsairs";}
   if (!$found) {
      $countryname = "Unknown Country";
   }
//   echo "ckey = $ckey, countryname = $countryname, countryadj = $countryadj<br>\n";
}

function CRASHED($pid,$ticks) {
// determine if a player's plane has crashed by a given time
   global $numkills; // number of kills
   global $Kline; //  lines that define kills
   global $TID; // target ID
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $crashed; // player's plane has crashed, true or false

   $crashed = 0;
   for ($i = 0; $i < $numkills; ++$i) {
      $j = $Kline[$i];
      // if kill TID matches pid and time is at or after crash plane is crashed/destroyed at this time
//      echo "pid = $pid, ticks = $ticks, TID = $TID[$j], Ticks[$j] = $Ticks[$j]<br>\n";
      if (($TID[$j] == $pid) && ($ticks >= ($Ticks[$j] - 50))) { // 1 sec fudge-factor
         $crashed = 1;
      }
   }
//   echo "pid = $pid, ticks = $ticks, crashed = $crashed<br>\n";
}

function DEAD($pid,$ticks) {
// determine if a player is dead at a given time
   global $Death; // dead players numbers
   global $Deathticks; // ticks when died
   global $numplayers; // number of players
   global $numgobjects; // number of gameobjects
   global $Pline;  // lines that define players
   global $GOline; // lines defining game objects
   global $PID; // plane ID (whether bot or player)
   global $PLID; // player plane id 
   global $ID; // object ID
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $dead; // true or false

   $dead = 0;
   for ($i = 0; $i < $numgobjects; ++$i) {
      $j = $GOline[$i];
      for ($k = 0; $k < $numplayers; ++$k) {
         $l = $Pline[$k];
         if ($ID[$j] == $pid) { // if object ID matches plane ID
            // if playerplane ID matches plane ID and time is at or after death
            // player is dead at this time
            if (($PLID[$l] == $pid) && ($Deathticks[$k] == 0 )) {
               $dead = 0;
            } elseif (($PLID[$l] == $pid) && ($ticks >= $Deathticks[$k])) {
//            echo "pid = $pid, ticks = $ticks, dead = $dead, ID[$j] = $ID[$j], PLID[$l] = $PLID[$l], deathticks = $Deathticks[$k]<br>\n";
               $dead = 1;
            }
         }
      }
   }
//   echo "pid = $pid, ticks = $ticks, dead = $dead<br>\n";
}

function FATES($i,$j) {
   // $i is playernumber
   // $j is linenumber defining that player.
   global $COUNTRY; // country ID
   global $countryname; // country name
   global $CoalID; // coalition ID
   global $TYPE; // type of plane in this context
   global $anora; // an or a
   global $Gunner; // gunner type, if set
   global $Gunnerticks; // time became gunner
   global $Wound; // array holding severity of wound
   global $Woundticks; // ticks when last wounded
   global $Woundpos; // position where last wounded
   global $clocktime; // 24 hr time
   global $Death; // dead players numbers
   global $Deathticks; // ticks when died
   global $Deathpos; // position where died
   global $Eline; // lines that define mission ends
   global $numends; // number of mission ends
   global $posx; // X coordinate
   global $posz; // Z coordinate
   global $where; // position in english
   global $NAME; // player profile name
   global $numlandings; // number of landings
   global $numtakeoffs; // number of takeoffs
   global $Lline;  // landing lines
   global $Tline;  // takeoff lines
   global $PID; // plane ID (whether bot or player)
   global $PLID; // player plane id
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $POS; // position x,y,z
   global $FinishFlightonlylanded; // true or false setting

   // get "", "a" or "an" right for the plane
   ANORA($TYPE[$j]);
   $a = $anora;
   // get player's country name
   COUNTRYNAME($COUNTRY[$j]);
   // is this player a pilot or gunner?
   GUNNER($j);

   // if gunner, ignore wounds acquired before becoming this gunner
   if ($Gunner) {
      ANORA($Gunner);
      $ag = $anora;
      if ($Woundticks[$i] < $Gunnerticks) {
         $Wound[$i] = 0;
      }
   }

   // now print out the fate of the player
   if ($Death[$i]) { // player has been killed
      CLOCKTIME($Deathticks[$i]);
      XYZ($Deathpos[$i]);
      WHERE($posx,$posz,0);
      if ($Gunner) { //G1:
//         echo "Woundticks[$i] = $Woundticks[$i], Gunnerticks = $Gunnerticks<br>\n";
         echo "$NAME[$j] as $ag $Gunner for $countryname was killed at $clocktime $where<br>\n";
      } else { // not gunner so must be pilot
         echo "$NAME[$j] piloting $a $TYPE[$j] for $countryname was killed at $clocktime $where<br>\n";
      }
   } elseif ($Wound[$i]) { // player is alive but has been wounded
      CLOCKTIME($Woundticks[$i]);
      XYZ($Woundpos[$i]);
      WHERE($posx,$posz,0);
      // how seriously wounded?
      if ($Wound[$i] > .66) {
         $injuries = "critical injuries";
      }
      else if ($Wound[$i] > .33) {
         $injuries = "serious injuries";
      } else {
         $injuries = "minor injuries";
      }
      if ($Gunner) { //G2:
//         echo "Woundticks[$i] = $Woundticks[$i], Gunnerticks = $Gunnerticks<br>\n";
         echo "$NAME[$j] as $ag $Gunner for $countryname suffered $injuries at $clocktime $where<br>\n";
      } else { // not gunner so must be pilot
         echo "$NAME[$j] piloting $a $TYPE[$j] for $countryname suffered $injuries at $clocktime $where<br>\n";
      }
   }  else { // player is unwounded
      // loop through landings to report landing
//      echo "landing loop<br>\n";
      $landed = "";
      for ($k = 0; $k < $numlandings; ++$k) {
         $l = $Lline[$k];
         if ($PLID[$j] == $PID[$l]) {
            CLOCKTIME($Ticks[$l]);
            XYZ($POS[$l]);
            WHERE($posx,$posz,0);
//            echo "PID[$j] = $PID[$j], PLID[$j] = $PLID[$j], PID[$l] = $PID[$l]<br>\n";
            $landed = "landed at $clocktime $where";
         }
      } // end landing check

      if (($landed == "") && ($FinishFlightonlylanded)) {
//      echo "FD check<br>\n";
      // this is the "FD" check.  Twice in a row his landings were not reported.
      // loop through reported finishes... only possible if landed.
         for ($k = 0; $k < $numends; ++$k) {
            $l = $Eline[$k]; 
            if ($PLID[$j] == $PLID[$l]) {
               CLOCKTIME($Ticks[$l]);
               XYZ($POS[$l]);
               WHERE($posx,$posz,0);
//               echo "PLID[$j] = $PLID[$j], PLID[$j] = $PLID[$j]<br>\n";
               $landed = "landed at $clocktime $where";
            }
         }
      } // end FinishFlightonlylanded landing check

      if ($landed == "") { // player never landed
         // loop through takeoffs to make sure player took off
         $tookoff = 0;
         for ($k = 0; $k < $numtakeoffs; ++$k) {
            $l = $Tline[$k];
            if ($PLID[$j] == $PID[$l]) {
              $tookoff = 1;
            }
         }
         if ($tookoff == 0) { // player never took off
            $landed = "did not take off, surviving to fight another day";
         } else {
            $landed = "did not land";
         }
      } // end takeoff check

      if ($Gunner) { // gunners do not take off or land independently G3:
         echo "$NAME[$j] as $ag $Gunner for $countryname survived safe and sound<br>\n";
      } else { //  pilot player took off and landed
         echo "$NAME[$j] piloting $a $TYPE[$j] for $countryname $landed<br>\n";
      }
   } // end unwounded
} // end function

function FLYING($pid,$ticks) {
// determine whether a plane has taken off, etc
   global $numtakeoffs; // number of takeoffs
   global $Tline;  // takeoff lines
   global $PID; // plane ID (whether bot or player)
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $numlandings; // number of landings
   global $Lline;  // landing lines
   global $flying;  // on ground, flying, crashing, or already landed/crashed
   
   $flying = 0; // 0 hasn't moved, 1 flying, 2 crashing, 3 already landed/crashed
   // if plane hasn't taken off yet, or has already landed, it isn't flying
   // loop through takeoffs
   for ($i = 0; $i < $numtakeoffs; ++$i) {
      $j = $Tline[$i]; 
      if (($PID[$j] == $pid) && ($Ticks[$j] < $ticks)) { // plane has already taken off
         // loop through landings
         for ($k = 0; $k < $numlandings; ++$k) {
            $l = $Lline[$k];
            if (($PID[$j] == $pid) && ($Ticks[$j] > $ticks)) { // still in air
               $flying = 1;
            } elseif (($PID[$j] == $pid) && ($Ticks[$j] == $ticks)) { // landing/crashing
               $flying = 2;
            } elseif (($PID[$j] == $pid) && ($Ticks[$j] < $ticks)) { // landed/crashed
               $flying = 3;
            }
         } 
      }
   } 
//   echo "FLYING: $flying<br>\n";
// This only works for player pilots, not AI (takeoffs not recorded in log).
}

function GUNNER($j){
// given linenumber, determine if gunner and if so, what description to use
   global $TYPE; // type of plane, object, or objective - primary or secondary
   global $Ticks; // time since start of mission in 1/50 sec ticks - begins each log line
   global $numgobjects; // number of gameobjects
   global $GOline; // lines defining game objects
   global $PLID; // player plane id 
   global $ID; // object ID
   global $countryadj;  // adjective form of country name  
   global $Gunner; // gunner type, if set
   global $Gunnerticks; // time became gunner
   global $Log; // log lines (for debugging this one)
   $Gunner = ""; 
   $Gunnerticks = "";
   if (($TYPE[$j] == "TurretGothaG5_1") ||
      ($TYPE[$j] == "BotGunnerG5_1")) { // used in DFW also
      OBJECTCOUNTRYNAME($TID[$j],$Ticks[$j]);
      $Gunner = "$countryadj gunner";
   } elseif (($TYPE[$j] == "TurretGothaG5_2") ||
      ($TYPE[$j] == "BotGunnerG5_2")) { // used in DFW also
      $Gunner = "$countryadj gunner";
   } elseif (($TYPE[$j] == "TurretGothaG5_2_WM_Twin_Parabellum") ||
      ($TYPE[$j] == "TurretGothaG5_1_WM_Becker_AP")) { 
      $Gunner = "Gotha G.V gunner";
   } elseif ($TYPE[$j] == "BotGunnerBacker") { // is this used elsewhere?
      $Gunner = "Gotha G.V gunner";
   } elseif ($TYPE[$j] == "BotGunnerBW12") {
      $Gunner = "Brandenburg W12 gunner";
   } elseif ($TYPE[$j] == "TurretHalberstadtCL2_1") {
      $Gunner = "Halberstadt CL.II gunner";
   } elseif ($TYPE[$j] == "TurretHalberstadtCL2au_1_WM_TwinPar") {
      $Gunner = "Halberstadt CLIIau gunner";
   } elseif ($TYPE[$j] == "BotGunnerHCL2") {
      $Gunner = "Halberstadt CL.II gunner";
   } elseif ($TYPE[$j] == "BotGunnerDavis") {
      $Gunner = "$countryadj Davis gunner";
   } elseif (($TYPE[$j] == "TurretHP400_1") || ($TYPE[$j] == "TurretHP400_1_WM") ||
      ($TYPE[$j] == "BotGunnerHP400_1")) { // just a guess as to which gunner is which - edit if needed
      $Gunner = "Handley Page 0/400 nose gunner";
   } elseif (($TYPE[$j] == "TurretHP400_2") ||
      ($TYPE[$j] == "BotGunnerHP400_2") || ($TYPE[$j] == "BotGunnerHP400_2_WM") || ($TYPE[$j] == "TurretHP400_2" ) || ($TYPE[$j] == "TurretHP400_2_WM")) { // just a guess as to which gunner is which - edit if needed
      $Gunner = "Handley Page 0/400 dorsal gunner";
   } elseif (($TYPE[$j] == "TurretHP400_3") ||
      ($TYPE[$j] == "BotGunnerHP400_3")) { // just a guess as to which gunner is which - edit if needed
      $Gunner = "Handley Page 0/400 ventral gunner";
   } elseif ($TYPE[$j] == "TurretDFWC_1") {
      $Gunner = "DFW C.V gunner";
   } elseif ($TYPE[$j] == "TurretDFWC_1_WM_Twin_Parabellum") {
      $Gunner = "DFW C.V gunner";
   } elseif ($TYPE[$j] == "TurretDFWC_1_WM_Becker_HEAP") {
      $Gunner = "DFW C.V gunner";
   } elseif (($TYPE[$j] == "TurretBreguet14_1") ||
      ($TYPE[$j] == "BotGunnerBreguet14_1")) { // also used in Bristol and F.E.2b
      $Gunner = "$countryadj Breguet 14.B2 gunner";
   } elseif ($TYPE[$j] == "TurretBristolF2B_1") {
      $Gunner = "Bristol F2.B gunner";
   } elseif ($TYPE[$j] == "TurretBristolF2BF2_1_WM2") {
      $Gunner = "Bristol F2.B gunner";
   } elseif ($TYPE[$j] == "TurretBristolF2BF3_1_WM2") {
      $Gunner = "Bristol F2.B gunner";
   } elseif ($TYPE[$j] == "TurretRE8_1") {
      $Gunner = "R.E.8 gunner";
   } elseif ($TYPE[$j] == "TurretRE8_1_WM2") {
      $Gunner = "R.E.8 gunner";
   } elseif ($TYPE[$j] == "TurretDH4_1_WM") {
      $Gunner = "D.H.4 gunner";
   } elseif ($TYPE[$j] == "TurretDH4_1") {
      $Gunner = "D.H.4 gunner";
   } elseif ($TYPE[$j] == "TurretFelixF2A_2") {
      $Gunner = "Felixstowe F2A gunner";
   } elseif ($TYPE[$j] == "TurretFelixF2A_3") {
      $Gunner = "Felixstowe F2A gunner";
   } elseif ($TYPE[$j] == "TurretFelixF2A_3_WM") {
      $Gunner = "Felixstowe F2A gunner";
   } elseif ($TYPE[$j] == "BotGunnerFelix_top-twin") {
      $Gunner = "Felixstowe F2A top gunner";
   } elseif ($TYPE[$j] == "TurretBW12_1_WM_Twin_Parabellum") {
      $Gunner = "Brandenburg W12 gunner";
   } elseif ($TYPE[$j] == "TurretRolandC2a_1_WM_TwinPar") {
      $Gunner = "Roland C.IIa gunner";
   } elseif ($TYPE[$j] == "BotGunnerRE8") {
      OBJECTCOUNTRYNAME($TID[$j],$Ticks[$j]);
      $Gunner = "$countryadj gunner";
   }
   if ($Gunner) { 
   // Gunnerticks may not be doing what it is expected to.  May need to redefine it.
   // yes.. should use time from GAMEOBJECTINVOLVED, not current time.
   // match PLID of current PLAYERPLANE line to ID of GAMEOBJECTINVOLVED
   // and take time from there
   // still not convinced I have it right.
//      $Gunnerticks = $Ticks[$j]; 
//      echo "GUNNER: $Log[$j]<br>\n";
      for ($i = 0; $i < $numgobjects; ++$i) {
         $k = $GOline[$i];
         if ($PLID[$j] == $ID[$k]) { // if player ID matches gameobject ID
            $Gunnerticks = $Ticks[$k];
// echo "GUNNER: TYPE[$j] = $TYPE[$j], Ticks[$j] = $Ticks[$j], GUNNER = $Gunner, PLID[$j] = $PLID[$j], ID[$k] = $ID[$k], Gunnerticks = $Gunnerticks<br>\n";
         }
      }
   }

//    echo "GUNNER: linenum = $j TYPE = $TYPE[$j], Gunner = $Gunner<br>\n";
}

function LANDINGSIDE($pid,$posx,$posz){
// determine if player landed on friendly or enemy territory
   global $PLID; // player plane id 
   global $numplayers; // number of players
   global $Pline;  // lines that define players
   global $COUNTRY; // country ID
   global $CoalID; // coalition ID
   global $numiaheaders; // number of influence area headers
   global $IAHline; // lines defining Influence Area Headers
   global $numB; // number of boundary definitions
   global $Bline; // lines defining area boundaries
   global $AID; // area ID in this context
   global $BoundaryArray; // array of point pairs defining a boundary
   global $side; // "friendly", "enemy" or "neutral"

   // format location the way the pointLocation class needs
   $location = "$posx $posz";

   // get player's country from PLAYERPLANE lines
   // loop through PLAYERPLANE lines to get the country.
   for ($i = 0; $i < $numplayers; ++$i) {
      $j = $Pline[$i];
      if ( $pid == $PLID[$j] ) {
        $pcountry = $COUNTRY[$j];
      }
   }

   // get playerplane's coalition
   COALITION($pcountry); 
   $pcoalition = $CoalID;
//   echo "LANDINGSIDE A: pcountry = $pcountry, pcoalition = $pcoalition<br>\n";

   // get influence areas' countries and coalitions 
   // get  country of each area
   for ($i = 0; $i < $numB; ++$i) {
      $j = $Bline[$i];
      for ($k = 0; $k < $numB; ++$k) {  // peek at first two IAHlines
         $l = $IAHline[$k];
//         echo "LANDINGSIDE B0: i = $i, AreaID[$i] = $AID[$j]<br>\n";
//         echo "LANDINGSIDE B0.1:  IAHeader AreaID[$i] = $AID[$l]<br>\n";
         if ($AID[$j] == $AID[$l]) {
//            echo "LANDINGSIDE B0.2:   $AID[$j] = $AID[$l]<br>\n";
//            echo "LANDINGSIDE B0.3:   k=$k, l=$l, COUNTRY[l]=$COUNTRY[$l]<br>\n";
            if (isset($COUNTRY[$l])) {
               @$acountry[$k] == $COUNTRY[$l]; // @ suppresses notices
               COALITION($COUNTRY[$l]);   
               $acoalition[$k] = $CoalID;
            }
         }
      }
//      echo "LANDINGSIDE B1: i = $i, AreaID[$i] = $AID[$j]<br>\n";
//      echo "LANDINGSIDE B2: areacountry[$i] = $acluntry[$i], acoalition[$i] = $acoalition[$i]<br>\n";
   }

   // New logic
   // loop through defined boundaries using the $numB index

   for ($i = 0; $i < $numB; ++$i) {
      // define the current polygon
      $polygon = $BoundaryArray[$i];

      // Now test whether landed inside this polygon
// in situ test: "20 20" is "inside" this polygon
//$polygon = array("10 0", "0 10", "0 20", "10 30", "20 30", "30 20", "30 10", "20 0", "10 0");
//$location = "20 20";
      $pointLocation = new pointLocation();
 //              echo "($location) is " . $pointLocation->pointInPolygon($location, $polygon) . "<br>";
      $place = $pointLocation->pointInPolygon($location, $polygon);

      // interpret result
      if ($place == "inside") {
//         echo "i = $i ,LANDINGSIDE reports inside.<br>\n";
         if ($pcoalition == $acoalition[$i]) {
            $side = "friendly"; 
            $i = $numB; // we are done
            $k = $numB; // we are done
         } else {
            $side = "enemy"; 
            $i = $numB; // we are done
            $k = $numB; // we are done
         }
      } else { // if not in either area, must be neutral
            $side = "neutral"; // but keep checking until done
      }
   }
//   echo "LANDINGSIDE reports $side<br>\n";
}

function LOSSES($i) {
   // $i is kill number
   global $Kline; //  lines that define kills
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $clocktime; // 24 hr time
   global $ID; // object ID
   global $TID; // target ID
   global $AID; // attacker ID in this context
   global $POS; // position x,y,z
   global $objecttype; // object type from PID/AID/TID
   global $objectname; // object name from PID/AID/TID
   global $playername; // player name from PLID
   global $countryadj;  // adjective form of country name
   global $anora; // an or a
   global $numgobjects; // number of game objects involved
   global $GOline; // lines defining game objects

   $j = $Kline[$i];
   CLOCKTIME($Ticks[$j]);
   OBJECTTYPE($TID[$j],$Ticks[$j]);
   PLAYERNAME($TID[$j],$Ticks[$j]);
   OBJECTNAME($TID[$j],$Ticks[$j]);
   OBJECTCOUNTRYNAME($TID[$j],$Ticks[$j]);
//   echo "$i in line # $j, $AID[$j] $TID[$j] in $POS[$j]<br>\n";
//   echo "objecttype = $objecttype, playername = $playername, objectname = $objectname<br>\n";
   ANORA($countryadj);
   $a = $anora;
   // get objectnumber for target object
   for ($k = 0; $k < $numgobjects; ++$k) {
      $l = $GOline[$k];
      if ($ID[$l] == $TID[$j]) {
         $tonum = $k;
      }
   }
//   echo "flying = $flying<br>\n";
//   echo "attackertype = $attackertype, attackerobject = $attackerobject, aplayername= $aplayername, objecttype = $objecttype, playername = $playername, objectname = $objectname<br>\n";
   if ("$objectname" == "Common Bot")  {
      $objectname = $playername;
      echo ("$clocktime  $countryadj pilot $objectname<br>\n");
   } elseif ($objecttype == "BotGunnerG5_1") { // used in DFW also
      echo ("$clocktime $a $countryadj gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerG5_2") { // used in DFW also
      echo ("$clocktime $a $countryadj gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerDavis") { // used in HP and Felixstow
      echo ("$clocktime $a $countryadj Davis gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerHP400_1") {
      echo ("$clocktime $a $countryadj nose gunner ($playername)<br>\n"); // also used in Felixstowe F2A
   } elseif ($objecttype == "BotGunnerBacker") {
      echo ("$clocktime $a $countryadj Gotha G.V gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerBW12") {
      echo ("$clocktime $a $countryadj Brandenburg W12 gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerHCL2") {
      echo ("$clocktime $a $countryadj Halberstadt CL.II gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerHP400_2") {
      echo ("$clocktime $a $countryadj Handley Page 0/400 dorsal gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerHP400_2_WM") {
      echo ("$clocktime $a $countryadj Handley Page 0/400 dorsal gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerHP400_3") {
      echo ("$clocktime $a $countryadj Handley Page 0/400 ventral gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerBreguet14") { // also used in Bristol F2B and F.E.2b
      echo ("$clocktime $a $countryadj gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerRE8") {
      echo ("$clocktime $a $countryadj gunner ($playername)<br>\n");
   } elseif ($objecttype == "BotGunnerFelix_top-twin") {
      echo ("$clocktime $a $countryadj Felixstowe F2A top gunner ($playername)<br>\n");
   } elseif (preg_match('/^British/',$objecttype)) { // don't need $countryadj
   echo ("$clocktime $a $objecttype ($objectname)<br>\n");
   } elseif (preg_match('/^German/',$objecttype)) { // don't need $countryadj
   echo ("$clocktime $a $objecttype ($objectname)<br>\n");
   } elseif ($objecttype == "ship_stat_pass") {
   echo ("$clocktime $a stationary $countryadj passenger ship ($objectname)<br>\n");
   } elseif ($objecttype == "GER submarine") {
   echo ("$clocktime $a surfaced $countryadj submarine ($objectname)<br>\n");
   } elseif ($objecttype == "GER Ship Searchlight") {
   echo ("$clocktime $a $countryadj ship searchlight ($objectname)<br>\n");
   } elseif ($objecttype == "GBR Searchlight") {
   echo ("$clocktime $a $countryadj searchlight ($objectname)<br>\n");
   } elseif ($objecttype == "ship_stat_cargo") {
   echo ("$clocktime $a stationary $countryadj cargo ship ($objectname)<br>\n");
   } elseif ($objecttype == "ship_stat_tank") {
   echo ("$clocktime $a stationary $countryadj tanker ship ($objectname)<br>\n");
   } elseif ($objecttype == "ger_med") {
   echo ("$clocktime $a $countryadj airfield ($objectname)<br>\n");
   } else {
   echo ("$clocktime $a $countryadj $objecttype ($objectname)<br>\n");
   }
//  echo ("C* $clocktime $attackertype $attackername $aplayername $objecttype $objectname $playername<br>\n");
}

function OBJECTCOUNTRYNAME ($id,$ticks) {
// given ID, find a game object's country name
   global $numgobjects; // number of game objects involved
   global $GOline; // lines defining game objects
   global $ID; // object ID
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $COUNTRY; // country ID
   global $countryid; // country id

   for ($i = 0; $i < $numgobjects; ++$i) {
      $j = $GOline[$i];
      if (($ID[$j] == $id ) && ($Ticks[$j] <= $ticks)) {
         $countryid = $COUNTRY[$j];
      }
//      echo "id = $id, ID[$j] = $ID[$j], ticks = $ticks, Ticks[$j] = $Ticks[$j]<br>\n";
   }
   COUNTRYNAME($countryid);
}

function OBJECTNAME ($id,$ticks) {
// given ID, find an object's name
   global $numlines; // number of log lines
   global $AType; // category of information contained in this line
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $ID; // object ID
   global $NAME; // player profile name
   global $TYPE; // type object in this context
   global $objectname; // object name from PID/AID/TID
   global $numgobjects; // number of game objects involved
   global $GOline; // lines defining game objects
   
   // T:36590 AType:12 ID:223250 TYPE:Albatros D.III COUNTRY:501 NAME:Plane PID:-1^M
   $found = "0";
   $objectname = "";
   if ( $id == "-1") {
      $objectname = "Intrinsic";
      $found = "1";
   }
   else { 
      for ($i = 0; $i < $numgobjects; ++$i) {
         $j = $GOline[$i];
         if (("$AType[$j]" == "12") && ("$ID[$j]" == "$id") && ($Ticks[$j] <= $ticks)) {
            $objectname = "$NAME[$j]";
            $found = "1";
            if ($NAME[$j] == "") { 
               $objectname = "$TYPE[$j]";
//               echo "OBJECTNAME: blank NAME, objectname = $objectname<br>\n";
            }
//            echo "OBJECTNAME: id = $id, Ticks[$j] = $Ticks[$j], ticks = $ticks, NAME = $NAME[$j], objectname = $objectname, found = $found<br>\n";
         }
      }
   }
   if (!$found) {
      $objectname = "";
   }
}

function OBJECTTYPE ($id,$ticks) {
// get object TYPE from ID
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $ID; // object ID
   global $TYPE; // type of object in this context
   global $objecttype; // object type from PID/AID/TID
   global $numgobjects; // number of game objects involved
   global $GOline; // lines defining game objects
   
   // T:36590 AType:12 ID:223250 TYPE:Albatros D.III COUNTRY:501 NAME:Plane PID:-1^M
   $objecttype = "";
   $found = "0";
   for ($i = 0; $i < $numgobjects; ++$i) {
      $j = $GOline[$i];
      if (("$ID[$j]" == "$id") && ($Ticks[$j] <= $ticks)) {
         $objecttype = $TYPE[$j];
         $found = "1";
      }
   }
   if ( $id == "-1") {
      $objecttype = "Intrinsic";
   } elseif (!$found) {
      $objecttype = "Uknown Object";
   }
}

function PLAYERNAME($plid,$ticks) {
// get player's name from PLID
// may call OBJECTNAME
   global $PLID; // player plane id 
   global $PID; // plane ID (whether bot or player)
   global $NAME; // player profile name
   global $numplayers; // number of players
   global $Pline;  // lines that define players
   global $objectname; // object name from PID/AID/TID
   global $playername; // player name from PLID

   $playername = "";
   $found = "0";
   if ($plid == "-1") {
      $playername = "Intrinsic";
      $found = "1";
//      echo "PLAYERNAME 1- playername = $playername<br>\n"; 
   } else {
      for ($i = 0; $i < $numplayers; ++$i) {
         $j = $Pline[$i];
//         if ("$PLID[$j]" == "$plid")) {
         if (("$PLID[$j]" == "$plid") || ("$PID[$j]" == "$plid")) {
            $playername = $NAME[$j];
            $found = "1";
//            echo "PLAYERNAME 2- playername = $playername<br>\n"; 
         }
      }
   }
   if (!$found) {
      $objectname = "";
      OBJECTNAME($plid,$ticks);
      $playername = $objectname;
      $found = "1";
//      echo "PLAYERNAME 3- playername = $playername<br>\n"; 
   }
}

function TOFROM($where) {
// massage $where string to show takeoff "from" rather than "at" or "next to"
   global $where; // position in english

   $where = preg_replace("/^at/", "from", $where);
   $where = preg_replace("/next to/", "from", $where);
}

function WHERE($x,$z,$fieldonly) {
// find closest location and vaguely describe distance from it
// if $fieldonly is 1 check airfields only
   global $Locs; // locations
   global $playername; // player name from PLID
   global $numlocs; // number of locations
   global $LID; // location ID
   global $LX; // location X coordinate
   global $LZ; // location Z coordinate
   global $LName; // location name
   global $where; // position in english
   global $SHOWAF; // Show airfield names (binary)

   $mindist = 100000;
   $minname = "";
   $mintype = 0;
   $minfield = "";

//   echo "X = $x, Z = $z<br>\n";
   // echo "numlocs = $numlocs<br>\n";
   // find closest location using brute force... only 660/743 locations to check.  :)
   // Tried to see if restricting calculations to a certain square helped any.  It didn't :)
   for ($i=0; $i<$numlocs; $i++) {
      if ($fieldonly) {
         if (( $LID[$i] == "10" ) || ( $LID[$i] == "20" )) {
            $distance[$i] = sqrt(pow($x -$LX[$i],2) + pow($z - $LZ[$i],2));
            if ( $mindist > $distance[$i]) {
               $mintype = $LID[$i];
               $mindist = $distance[$i];
               $minname = $LName[$i];
//               echo "$mindist from $LName[$i]<br>\n";
            }
         }
// try to speed things up a bit here --- but it is no help at all!
//      } elseif ( abs ($x -$LX[$i]) > 20000 ) { ; // skip calculation
//      } elseif ( abs ($z -$LZ[$i]) > 20000 ) { ; // skip calculation
      } else {
         $distance[$i] = sqrt(pow($x -$LX[$i],2) + pow($z - $LZ[$i],2));
         if ( $mindist > $distance[$i]) {
            $mintype = $LID[$i];
            $mindist = $distance[$i];
            $minname = $LName[$i];
//            echo "$mindist from $LName[$i]<br>\n";
         }
      }
   }
   //echo "$mindist from $minname<br>\n";
   // translate distances into appropriate but vague modifiers
   if ($mindist < 750) { $desc = "at"; }
   elseif ($mindist < 1500.0) { $desc = "next to"; }
   elseif ($mindist < 2500.0) { $desc = "near"; }
   elseif ($mindist < 5000.0) { $desc = "within sight of"; }
   elseif ($mindist < 10000.0) { $desc = "a good way from"; }
   elseif ($mindist < 20000.0) { $desc = "far from"; }
   else { $desc = "in the middle of nowhere"; }
   // if small airfield or regular airfield add airfield to location name
   if ( $mindist >= 20000.0 ) {
     $where = $desc;
   } elseif (( $mintype == "10" ) || ( $mintype == "20" )) {
     if ($SHOWAF) { 
       $where = $desc . " " . $minname . "airfield";
     } else {
       $where = $desc . " " . "an undisclosed airfield";
     }
   } else {
     $where = $desc . " " . $minname;
   }
//   echo "$desc<br>";
}

function WHOSEGUNNER($id) {
// no longer used?
// given gunner id, find player name
   global $ID; // object ID
   global $PID; // plane ID (whether bot or player)
   global $PLID; // player plane id 
   global $NAME; // player profile name
   global $numgobjects; // number of game objects involved
   global $GOline; // lines defining game objects
   global $numplayers; // number of players
   global $Pline;  // lines that define players
   global $Whosegunner; // player piloting this gunner
   
   $pid = 0;
   $Whosegunner = "";
   for ($i = 0; $i < $numgobjects; ++$i) {
      $j = $GOline[$i];
      if ($id == $ID[$j]) {
         $pid = $PID[$j];
         for ($k = 0; $k < $numplayers; ++$k) {
            $l = $Pline[$k];
            if ($pid == $PLID[$l]) {
               $Whosegunner = "$NAME[$l]'s";
            }
         } 
      }
   }
   if ($Whosegunner == "")
      {
         $Whosegunner = "an unpiloted plane's AI";
      }
//  echo "WHOSEGUNNER: ID=$id, PID = $pid whosegunner = $Whosegunner<br>\n";
}

function XYZ($POS) {
// Break POS into X, Y and Z.
   global $posx; // X coordinate
   global $posy; // Y coordinate (altitude)
   global $posz; // Z coordinate

   // remove slashes and spaces
   $POS = preg_replace("/\(/","",$POS);
   $POS = preg_replace("/\)/","",$POS);
   $POS = preg_replace("/ /","",$POS);
   $Part = explode(",",$POS,3); // split into X, Y and Z at ","
   $posx = trim($Part[0]);
   $posy = trim($Part[1]);
   $posz = trim($Part[2]);
}
// end of FUNCTIONS called by OUTPUT



function OUTPUT() {
// temporarily output simple text report
// eventually this will be a web page
// what follows is an almost complete collection of global variables
// some of these variables are needed just for the debugging section
// others may not be needed here at all
   global $DEBUG; // are we debugging?
   global $Log; // log lines
   global $numlines; // number of log lines
   global $Ticks; // time since start of mission in 1/50 sec ticks
   global $Startticks; // time mission started (expected to be 0)
   global $numstart; // number of starts (hopefully just 1)
   global $Sline; // line number for each start
   global $endticks; // time mission ended
   global $Part; // array to hold parts of log lines
   global $AType; // category of information contained in this line
   global $GDate; // game date at start of mission e.g. 1917.9.23
   global $GTime; // game time at start of mission e.g. 6:30:0
   global $MFile; // mission file location and name
   global $MID; // unknown - perhaps a mission ID?
   global $GType; // game type 0 = single, 1 = coop, 2 = dogfight, 3 = custom
   global $CNTRS; // countries and their coalitions as a string
   global $SETTS; // game settings where 0 = off 1 = on
   global $MODS; // mods 0 = off, 1 = on
   global $MissionID; // mission ID (name-date-time)
   global $PLID; // player plane id 
   global $PID; // plane ID (whether bot or player)
   global $BUL; // # of bullets
   global $SH; // unknown
   global $BOMB; // # of bombs
   global $RCT; // unknown
   global $POS; // position x,y,z
   global $IDS; // player profile ID/ plane ID
   global $LOGIN; // player account ID
   global $NAME; // player profile name
   global $TYPE; // type of plane, object, or objective - primary or secondary
   global $COUNTRY; // country ID
   global $CoalID; // coalition ID
   global $FORM; // unknown - perhaps formation?
   global $FIELD; // unknown - perhaps type of field?
   global $INAIR; // unknown - perhaps airstart?
   global $PARENT; // unkown
   global $ID; // object ID
   global $OBJID; // objective ID
   global $COAL; // coalition ID
   global $RES; // result - objective achieved or not
   global $AMMO; // what hit
   global $AID; // attacker ID or airfield ID or area ID
   global $TID; // target ID
   global $DMG; // damage
   global $ENABLED; // influence area enabled or not
   global $BC; // By Coalition inflight count of planes in area
   global $BP; // boundary points
   global $VER; // version (of what?)
   global $Startticks; // game start time in number of ticks since midnight
   global $clocktime; // 24 hr time
   global $playername; // player name from PLID
   global $objectname; // object name from PID/AID/TID
   global $objecttype; // object type from PID/AID/TID
   global $countryname; // country name
   global $countryid; // country id
   global $countryadj;  // adjective form of country name  
   global $Coalitions; // array of coalition names
   global $CoCoal; // array of countries and their coalitions
   global $Coalitionname; // this coalition name 
   global $posx; // X coordinate
   global $posz; // Z coordinate
   global $where; // position in english
   global $numevents; // number of mission events
   global $EVline; // lines that define mission events
   global $numtakeoffs; // number of takeoffs
   global $Tline;  // takeoff lines
   global $numlandings; // number of landings
   global $Lline;  // landing lines
   global $numplayers; // number of players
   global $Pline;  // lines that define players
   global $numgroups; // total number of groups
   global $Gline; // lines defining groups
   global $numgobjects; // number of game objects involved
   global $GOline; // lines defining game objects
   global $numkills; // number of kills
   global $Kline; //  lines that define kills
   global $numhits; // number of hits
   global $Hline; // lines that define hits
   global $numdamage; // number of damage events
   global $Dline;  // lines that define damage events
   global $numends; // number of mission ends
   global $Eline; // lines that define mission ends
   global $Death; // dead players numbers
   global $Deathticks; // ticks when died
   global $Deathpos; // position where died
   global $dead; // true or false
   global $crashed; // player's plane has crashed, true or false
   global $End; // player ended (or not)
   global $EndBUL; // unexpended bullets
   global $EndBOMB; // undropped bombs
   global $ShotBUL; // shot bullets
   global $DroppedBOMB; // dropped bombs
   global $HitBOMB; // bomb hits
   global $HitBUL; // bullet hits
   global $Lasthitbyid; // ID of last attacker to hit player
   global $Lasthitby; // name or type of last attacker to hit player
   global $Wound; // array holding severity of wound
   global $Woundticks; // ticks when last wounded
   global $Woundpos; // position where last wounded
   global $flying;  // on ground, flying, crashing, or already landed/crashed
   global $anora; // an or a
   global $Gunner; // gunner type, if set
   global $Gunnerticks; // time became gunner
   global $Whosegunner; // player piloting this gunner
   global $Kcountryid; // country id of a killed object
   global $numententelosses; // number of entente losses
   global $numcplosses; // number of central powers losses
   global $GID; // group ID
   global $LID; // lead plane ID
   global $FinishFlightonlylanded; // true or false setting
   global $LOCATIONSFILE; // which map locations are we using?
   global $numiaheaders; // number of influence area headers
   global $IAHline; // lines defining Influence Area Headers
   global $Bline; // lines defining area boundaries
   global $side; // "friendly", "enemy" or "neutral"

   echo "<p><b>REPORT OF SELECTED RESULTS:</b></p>\n"; 

   echo ("<p>Mission ID = $MissionID</p>\n");

   echo "<p>Lines in mission log: $numlines</p>\n";

   // for the moment assume only one start but warn if different
   if ($numstart != 1) {
      echo "WARNING: Have $numstart start lines!<br>\n";
   }
   // present in same order as in current version settings page
   // updated and verified correct as of version 1.030b
   $anyon = 0; // are any settings ON?
   echo "SETTINGS:<br>\n";
   if (substr($SETTS,0,1)) { echo "Show Objects icons: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,1,1)) { echo "Navigation icons: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,2,1)) { echo "Far objects icons on map: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,4,1)) { echo "Aiming Help: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,5,1)) { echo "Padlock: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,3,1)) { echo "Simple gauges: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,23,1)) { echo "Allow Spectators: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,24,1)) { echo "Subtitles: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,19,1)) { echo "Simplified physics: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,20,1)) { echo "No wind: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,18,1)) { echo "No misfire: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,17,1)) { echo "Safety collisions: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,16,1)) { echo "Invulnerability against weapons: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,22,1)) { echo "Unlimited fuel: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,21,1)) { echo "Unlimited ammo: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,14,1)) { echo "No engine overflow: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,15,1)) { echo "Warmed up engine: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,13,1)) { echo "Easy piloting: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,6,1)) { echo "Autorudder: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,11,1)) { echo "Cruise control: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,8,1)) { echo "Autopilot: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,12,1)) { echo "Automatic RPM limiter: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,7,1)) { echo "Automatic mixture: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,9,1)) { echo "Automatic radiator: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,10,1)) { echo "Automatic engine start: ON<br>\n"; $anyon = 1; } 
   if (substr($SETTS,25,1)) { echo "UNKNOWN SETTING: ON<br>\n"; $anyon = 1; } 

   if ($anyon == 0) { echo "All settings reported in log: OFF<br>&nbsp;<br>\n";
   } else {
   echo "All other settings reported in log: OFF<br>&nbsp<br>\n";
   }
   if ($FinishFlightonlylanded) { echo "Finish Flight only landed: ON<br>\n"; }
   if ($LOCATIONSFILE == "RoF_locations.csv") {echo "Map: Western Front<br>&nbsp;<br>\n";}
   if ($LOCATIONSFILE == "Verdun_locations.csv") {echo "Map: Verdun<br>&nbsp;<br>\n";}
   if ($LOCATIONSFILE == "Lake_locations.csv") {echo "Map: Lake<br>&nbsp;<br>\n";}
   // Players
   echo "=-=-=-=-= Players and Their Fates =-=-=-=-=-=<br>\n";
   echo "There were $numplayers player positions.<br>&nbsp;<br>\n";
   // count each coalition's pilots
   $numentente = 0;
   $numcentralpowers = 0;
   // loop through those players using $Pline index
   for ($i = 0; $i < $numplayers; ++$i) {
      $j = $Pline[$i];
      COALITION($COUNTRY[$j]);
      if ($CoalID == 1) {
        ++$numentente;
      }
      elseif ($CoalID == 2) {
        ++$numcentralpowers;
      }
      else {
         echo "Player #$i is neither Entente nor Central Powers!<br>\n";
      }
   }
   // first show Entente players
   COALITIONNAME(1);
   echo "$numentente $Coalitionname:<br>\n";	
   // loop through all players using $Pline index
   for ($i = 0; $i < $numplayers; ++$i) {
      $j = $Pline[$i];
      COALITION($COUNTRY[$j]);
      if ($CoalID == 1) {
         FATES($i,$j);
      }
   }

   // now show Central Powers players
   COALITIONNAME(2);
   echo "<br>$numcentralpowers $Coalitionname:<br>\n";	
   // loop through all players using $Pline index
   for ($i = 0; $i < $numplayers; ++$i) {
      $j = $Pline[$i];
      COALITION($COUNTRY[$j]);
      if ($CoalID == 2) {
         FATES($i,$j);
      }
   }

   // Shooting and Bombing Accuracy
   echo "<br>=-=-=-=-=-= Shooting and Bombing Accuracy =-=-=-=-=-=<br>\n";
   echo "There were $numplayers player positions.<br>&nbsp;<br>\n";
   // first show Entente players
   COALITIONNAME(1);
   echo "$numentente $Coalitionname:<br>\n";	
   // loop through all players using $Pline index
   for ($i = 0; $i < $numplayers; ++$i) {
      $j = $Pline[$i];
      COALITION($COUNTRY[$j]);
      if ($CoalID == 1) {
         ACCURACY($i,$j);
      }
   }

   // now show Central Powers players
   COALITIONNAME(2);
   echo "<br>$numcentralpowers $Coalitionname:<br>\n";	
   // loop through all players using $Pline index
   for ($i = 0; $i < $numplayers; ++$i) {
      $j = $Pline[$i];
      COALITION($COUNTRY[$j]);
      if ($CoalID == 2) {
         ACCURACY($i,$j);
      }
   }

   // Losses
   echo "<br>=-=-=-=-=-= Losses =-=-=-=-=-=<br>\n";
   echo "There were $numkills losses.<br>&nbsp;<br>\n";
   // first show Entente losses
   COALITIONNAME(1);
   if ($numententelosses == 1){
      echo "The $Coalitionname suffered a single loss:<br>\n";	
   }
   else {
         echo "The $Coalitionname suffered $numententelosses losses:<br>\n";	
   }
   // loop through kills
   for ($i = 0; $i < $numkills; ++$i) {
      COALITION(@$Kcountryid[$i]); // @ suppresses notices
      if ($CoalID == 1) {
         LOSSES($i);
      }
   }
   // then show Central Powers losses
   COALITIONNAME(2);
   if ($numcplosses == 1){
      echo "The $Coalitionname suffered a single loss:<br>\n";	
   }
   else {
      echo "<br>The $Coalitionname suffered $numcplosses losses:<br>\n";	
   }
   // loop through kills
   for ($i = 0; $i < $numkills; ++$i) {
      COALITION(@$Kcountryid[$i]);  // @ supresses notices
      if ($CoalID == 2) {
            LOSSES($i);
      }
   } 
//   if ($CoalID  != 1 ) && ($CoalID != 2 ) {
//      echo "Other Losses<br>/n";
//      LOSSES($i);
//   }

   // Mission Event Chronology
   echo "<br>=-=-=-=-=-= Mission Event Chronology =-=-=-=-=-=<br>\n";
   echo "There were $numevents Notable events during this mission.<br>Here are all except any landings by dead pilots:<br>&nbsp;<br>\n";
   // loop through mission events using EVline index
   for ($i = 0; $i < $numevents; ++$i) {
      $j = $EVline[$i];
      CLOCKTIME($Ticks[$j]);
// for debugging missing events
//      echo "EVENT $i, line $j, $Ticks[$j], Type $AType[$j]<br>\n";
      if ($AType[$j] == "0") { // START
         echo "$clocktime Mission Start<br>\n";
      } elseif ($AType[$j] == "3") { // KILL
//         echo "$clocktime KILL<br>\n";
         CLOCKTIME($Ticks[$j]);
         OBJECTTYPE($AID[$j],$Ticks[$j]);
         $attackertype = $objecttype;
         OBJECTNAME($AID[$j],$Ticks[$j]);
         $attackerobject = $objectname;
         PLAYERNAME($AID[$j],$Ticks[$j]);
         $aplayername = $playername;
         OBJECTTYPE($TID[$j],$Ticks[$j]);
         PLAYERNAME($TID[$j],$Ticks[$j]);
         OBJECTNAME($TID[$j],$Ticks[$j]);
         OBJECTCOUNTRYNAME($TID[$j],$Ticks[$j]);
         FLYING($TID[$j],$Ticks[$j]);
         XYZ($POS[$j]);
         WHERE($posx,$posz,0);
//         echo "$i in line # $j, $AID[$j] $TID[$j] in $POS[$j]<br>\n";
//         echo "attackertype = $attackertype, attackerobject = $attackerobject, aplayername= $aplayername, objecttype = $objecttype, playername = $playername, objectname = $objectname<br>\n";
         ANORA($objecttype);
         $a = $anora;
         ANORA($countryadj);
         $ca = $anora;
         // get objectnumber for target
	 // NOTE: gameobject ID is NOT NECESSARILY UNIQUE.  See AT13 -
	 // Caquot destroyed at beginning and a Camel have the same
	 // ID!  So the ID is unique at any particular time, but not
	 // over all mission time.  So the following fails in that
	 // case...  reporting a Camel rather than a Caquot.
         // need to add a time factor to make it correct.
	 // Ah... use time of death for object.
         for ($k = 0; $k < $numgobjects; ++$k) {
            $l = $GOline[$k];
            //if (($ID[$l] == $TID[$j]) && ($Deathticks[$l] >= $Ticks[$j])) {
            if (($ID[$l] == $TID[$j])) {
               $tonum = $k;
//               echo "j = $j k = $k l = $l ID[l] = $ID[$l] TID[j] = $TID[$j] Ticks[l] = $Ticks[$l] Ticks[j] = $Ticks[$j]<br>\n";
            }
         }
         if (($AID[$j] == "-1") || ($aplayername == "Intrinsic")) { // AI attacker
//            echo "Lasthitby[$tonum] = $Lasthitby[$tonum]<br>\n";
//            echo "flying = $flying<br>\n";
            if ($Lasthitby[$tonum] == "" ) { // self-inflicted?
               if ($objecttype == "Common Bot") {
                  echo ("$clocktime $playername was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerG5_1") { // used in DFW also GK1:
                  echo ("$clocktime $ca $countryadj gunner ($playername) was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerG5_2") { //GK2:
                  echo ("$clocktime $ca $countryadj gunner ($playername) was killed $where<br>\n"); // used in DFW also
               } elseif ($objecttype == "BotGunnerDavis") { //GK2:
                  echo ("$clocktime $ca $countryadj gunner ($playername) was killed $where<br>\n"); // used in HP and Felixstow 
               } elseif ($objecttype == "BotGunnerBacker") { 
                  echo ("$clocktime $ca $countryadj Gotha G.V gunner ($playername) was killed $where<br>\n"); // is this particular Becker used elsewhere?
               } elseif ($objecttype == "BotGunnerBW12") { 
                  echo ("$clocktime $ca $countryadj Brandenburg W12 gunner ($playername) was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerHCL2") { 
                  echo ("$clocktime $ca $countryadj Halberstadt CL.II gunner ($playername) was killed $where<br>\n"); // is this particular Becker used elsewhere?
               } elseif ($objecttype == "BotGunnerHP400_1") {
                  echo ("$clocktime a Handley Page 0/400 nose gunner ($playername) was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerHP400_2") {
                  echo ("$clocktime a Handley Page 0/400 dorsal gunner ($playername) was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerHP400_2_WM") {
                  echo ("$clocktime a Handley Page 0/400 dorsal gunner ($playername) was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerHP400_3") {
                  echo ("$clocktime a Handley Page 0/400 ventral gunner ($playername) was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerBreguet14") { // also used in Bristol F2B and F.E.2b
                  echo ("$clocktime $ca $countryadj Breguet 14.B2 gunner ($playername) was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerRE8") {
                  echo ("$clocktime $ca $countryadj gunner ($playername) was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerFelix_top-twin") {
                  echo ("$clocktime a FelixStowe F2A top gunner ($playername) was killed $where<br>\n");
               } elseif ($objecttype == "BotGunnerFe2_sing") {
                  echo ("$clocktime an F.E.2b gunner ($playername) was killed $where<br>\n");

               } elseif ($objectname == "Plane") {
                  if ($flying == 2) { $action = "crashed";}
                  elseif ($flying == 1) { $action = "crashed";}
                  elseif ($flying == 0) { $action = "crashed on takeoff";}
                  elseif ($flying == 3) { $action = "crashed";}
                  echo ("$clocktime $playername's $objecttype $action $where<br>\n");
               } elseif (($objectname == "Aerostat") || ($objectname == "Train" ) ||
                  ($objectname == "Vehicle") || ($objectname == "Wagon")) {
                  echo ("$clocktime $a $objecttype ($objectname) was destroyed $where<br>\n");
               } else { // U1:
                  echo ("$clocktime $playername's $objecttype ($objectname) was rendered unserviceable $where<br>\n");
               }
            } else {
               if ($objecttype == "Common Bot") {
                  // A:
                  ANORA($Lasthitby[$tonum]);
                  $a3 = $anora;
                  echo ("$clocktime $a3 $Lasthitby[$tonum] killed $playername $where<br>\n");
               } elseif ($objecttype == "BotGunnerG5_1") { // used in DFW also
                  // B0:
                  echo ("$clocktime $Lasthitby[$tonum] killed $ca $countryadj gunner ($playername) $where<br>\n");
               } elseif ($objecttype == "BotGunnerG5_2") { // used in DFW also
                  // B1:
                  echo ("$clocktime $Lasthitby[$tonum] killed $ca $countryadj gunner($playername)  $where<br>\n");
               } elseif ($objecttype == "BotGunnerBacker") {
                  // B1b1:
                  echo ("$clocktime $Lasthitby[$tonum] killed a Gotha G.V gunner ($playername) $where<br>\n");
               } elseif ($objecttype == "BotGunnerBW12") {
                  // B1b2:
                  echo ("$clocktime $Lasthitby[$tonum] killed a Brandenburg W12 gunner ($playername) $where<br>\n");
               } elseif ($objecttype == "BotGunnerHCL2") {
                  // B1c:
                  echo ("$clocktime $Lasthitby[$tonum] killed a Halberstadt CL.II gunner ($playername) $where<br>\n");
               } elseif ($objecttype == "BotGunnerHP400_1") {
                  // B2:
                  echo ("$clocktime $Lasthitby[$tonum] killed a Handley Page 0/400 nose gunner ($playername) $where<br>\n");
               } elseif ($objecttype == "BotGunnerHP400_2") {
                  // B3a:
                  echo ("$clocktime $Lasthitby[$tonum] killed a Handley Page 0/400 dorsal gunner ($playername) $where<br>\n");
               } elseif ($objecttype == "BotGunnerHP400_2_WM") {
                  // B3b:
                  echo ("$clocktime $Lasthitby[$tonum] killed a Handley Page 0/400 dorsal gunner ($playername) $where<br>\n");
               } elseif ($objecttype == "BotGunnerHP400_3") {
                  // B4:
                  echo ("$clocktime $Lasthitby[$tonum] killed a Handley Page 0/400 ventral gunner ($playername) $where<br>\n");
               } elseif ($objecttype == "BotGunnerFelix_top-twin") {
                  // B5:
                  echo ("$clocktime $Lasthitby[$tonum] killed a Felixstow F2A top gunner ($playername) $where<br>\n");
               } elseif ($objecttype == "BotGunnerFe2_sing") {
                  // B5:
                  echo ("$clocktime $Lasthitby[$tonum] killed an F.E.2b gunner ($playername) $where<br>\n");
               } else {
//                  echo "flying = $flying<br>\n";
                  ANORA($Lasthitby[$tonum]);
                  $a2 = $anora;
                  if ($flying == 2) { $action = "shot down";}
                  elseif ($flying == 1) { $action = "shot down";}
                  elseif ($flying == 0) { $action = "destroyed";}
                  elseif ($flying == 3) { $action = "shot down";}
                  if ($TID[$j] == $Lasthitbyid[$tonum]) { $action = "crashed";}
                  if (preg_match("/^Turret/",$Lasthitby[$tonum])) { // a gunner
                     WHOSEGUNNER($Lasthitbyid[$tonum]);
                     if (($objectname == "Plane") || ($objectname == $objecttype)) { // C1:
                        echo ("$clocktime $Whosegunner gunner $action $a $objecttype $where<br>\n");
                     } else { // D1
                        echo ("$clocktime $Whosegunner gunner $action $a $objecttype ($objectname) $where<br>\n");
                     }
                  } elseif ($objectname == "Plane") { // C2:
                     echo ("C2: $clocktime $a2 $Lasthitby[$tonum] $action $a $objecttype $where<br>\n");
//                     echo ":$i in line # $j, $AID[$j] $TID[$j] in $POS[$j]<br>\n";
                   } elseif ($objectname == $objecttype) { // C3:
                     if ($objecttype == "BotGunnerG5_1") { // used in DFW also
                       echo ("$clocktime $a2 $lasthitby[$tonum] $action $ca $countryadj gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerG5_2") { // used in DFW also
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action $ca $countryadj gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerBacker") {
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action a Gotha G.V gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerBW12") {
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action a Brandenburg W12 gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerHCL2") {
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action a Halberstadt CL.II gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerHP400_1") {
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action a Handley Page 0/400 nose gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerHP400_2") {
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action a Handley Page 0/400 dorsal gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerHP400_2_WM") {
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action a Handley Page 0/400 dorsal gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerHP400_3") {
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action a Handley Page 0/400 ventral gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerBreguet14") { // also used in Bristol F2B and F.E.2b
                     } elseif ($objecttype == "BotGunnerFelix_top-twin") {
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action a Felixstowe F2A top gunner ($playername)<br>\n");
                     } elseif ($objecttype == "BotGunnerFe2_sing") {
                       echo ("$clocktime $a2 $Lasthitby[$tonum] $action an F.E.2b gunner ($playername)<br>\n");
                     } else { // D1:
                     echo ("$clocktime $a2 $Lasthitby[$tonum] $action $a $objecttype $where<br>\n");
                     }
                  } else { // D2:
                     echo ("$clocktime $a2 $Lasthitby[$tonum] $action $a $objecttype ($objectname) $where<br>\n");
//            echo "Lasthitby[$tonum] = $Lasthitby[$tonum]<br>\n";
//            echo "TID flying = $flying<br>\n";
                  }
               }
            }
         } else {
//             echo "flying = $flying<br>\n";
//             echo "attackertype = $attackertype, attackerobject = $attackerobject, aplayername= $aplayername, objecttype = $objecttype, playername = $playername, objectname = $objectname<br>\n";
            // if flying or if objectname is aerostat, "shot down" else destroyed
            if ($objecttype == "BotGunnerG5_1") { $objecttype = "$countryadj gunner"; } // used in DFW also
            elseif ($objecttype == "BotGunnerG5_2") { $objecttype = "$countryadj gunner"; } // used in DFW also
            elseif ($objecttype == "BotGunnerHP400_1") { $objecttype =  "$countryadj nose gunner";} // also used in Felixstowe F2A
            elseif ($objecttype == "BotGunnerHP400_2") { $objecttype =  "Handley Page 0/400 dorsal gunner";}
            elseif ($objecttype == "BotGunnerHP400_2_WM") { $objecttype =  "Handley Page 0/400 dorsal gunner";}
            elseif ($objecttype == "BotGunnerHP400_3") { $objecttype =  "Handley Page 0/400 ventral gunner";}
            elseif ($objecttype == "BotGunnerBreguet14") { $objecttype = "$countryadj gunner"; } // also used in Bristol F2B and F.E.2b
            elseif ($objecttype == "BotGunnerFelix_top-twin") { $objecttype = "Felixstowe F2A top gunner"; } 
            elseif ($objecttype == "BotGunnerBW12") { $objecttype = "Brandenburg W12 gunner"; } 
            elseif ($objecttype == "BotGunnerHCL2") { $objecttype = "Halberstadt CL.II gunner"; } 
            ANORA($objecttype);
            $a = $anora;
            if (($flying == 2) || ($objectname == "Aerostat")) { $action = "shot down";}
            elseif ($flying == 1) { $action = "shot down";}
            elseif ($flying == 0) { $action = "destroyed";}
            elseif ($flying == 3) { $action = "shot down";}
            if ("$aplayername" == "Vehicle") { $aplayername = $attackertype;} 
            if ($aplayername == "TurretDH4_1") {$aplayername = "D.H.4 gunner";}
            if ($aplayername == "TurretDH4_1_WM") {$aplayername = "D.H.4 gunner";}
            if ($aplayername == "TurretDFWC_1") {$aplayername = "DFW C.V gunner";}
            if ($aplayername == "TurretDFWC_1_WM_Twin_Parabellum") {$aplayername = "DFW C.V gunner";}
            if ($aplayername == "TurretDFWC_1_WM_Becker_HEAP") {$aplayername = "DFW C.V gunner";}
            if ($aplayername == "TurretRE8_1") {$aplayername = "R.E.8 gunner";}
            if ($aplayername == "TurretRE8_1_WM2") {$aplayername = "R.E.8 gunner";}
            if ($aplayername == "TurretHalberstadtCL2_1") {$aplayername = "Halberstadt CL.II gunner";}
            if ($aplayername == "TurretHalberstadtCL2au_1_WM_TwinPar") {$aplayername = "Halberstadt CLIIau gunner";}
            if ($aplayername == "TurretBristolF2B_1") {$aplayername = "Bristol F2.B gunner";}
            if ($aplayername == "TurretBristolF2BF2_1_WM2") {$aplayername = "Bristol F2.B gunner";}
            if ($aplayername == "TurretBristolF2BF3_1_WM2") {$aplayername = "Bristol F2.B gunner";}
            if ($aplayername == "TurretFelixF2A_2") {$aplayername = "Felixstowe F2A gunner";}
            if ($aplayername == "TurretFelixF2A_3") {$aplayername = "Felixstowe F2A gunner";}
            if ($aplayername == "TurretFelixF2A_3_WM") {$aplayername = "Felixstowe F2A gunner";}
            if ($aplayername == "TurretBW12_1_WM_Twin_Parabellum") {$aplayername = "Brandenburg W12 gunner";}
            ANORA($aplayername);
            ANORA($aplayername);
            $a1 = $anora;

            if ("$objectname" == "Common Bot") {
               $objectname = $playername;
               $action = "killed";
               // E:
               echo ("$clocktime $a1 $aplayername $action $objectname $where<br>\n");
            } else {
            ANORA($aplayername);
            $a3 = $anora;
            if (preg_match('/ship/',$objecttype)) { $action = "sank";
               if ($objecttype == "ship_stat_pass") {$objecttype = "stationary passenger ship";}
               if ($objecttype == "ship_stat_tank") {$objecttype = "stationary tanker ship";}
               if ($objecttype == "ship_stat_cargo") {$objecttype = "stationary cargo ship";}
            }
            if ($objecttype == "HMS submarine") { $action = "sank";
            $objecttype = "surfaced $countryadj submarine";}
            if ($objecttype == "GER submarine") { $action = "sank";
            $objecttype = "surfaced $countryadj submarine";}
            if ($objecttype == "ger_med") {
            $objecttype = "$countryadj airfield";}
            if ($objecttype == "GER Ship Searchlight") {$objecttype = "$countryadj ship searchlight";}
            if ($objecttype == "HMS Ship Searchlight") {$objecttype = "$countryadj ship searchlight";}
            if ($objecttype == "GBR Searchlight") {$objecttype = "$countryadj searchlight";}
            // F:
            // F:
            echo ("$clocktime $a3 $aplayername $action $a $objecttype ($objectname) $where<br>\n");
            }
         }
//         echo ("G: $clocktime $attackertype $attackername $aplayername $objecttype $objectname $playername $where<br>\n");
      } elseif ($AType[$j] == "5") { // TAKEOFF
//         echo "$clocktime TAKEOFF<br>\n";
         OBJECTTYPE($PID[$j],$Ticks[$j]);
         PLAYERNAME($PID[$j],$Ticks[$j]);
         ANORA($playername);
         $a = $anora;
         XYZ($POS[$j]);
         WHERE($posx,$posz,1);
         TOFROM($where);
//         echo "$clocktime TAKEOFF<br>\n"; //T1:
         echo "$clocktime $a $playername took off $where<br>\n";
      } elseif ($AType[$j] == "6") { // LANDING
         // T:71580 AType:6 PID:223245 POS(243148.469, 24.424, 57384.961)
         DEAD($PID[$j],$Ticks[$j]);
         if (!$dead) {
            CLOCKTIME($Ticks[$j]);
            OBJECTTYPE($PID[$j],$Ticks[$j]);
            PLAYERNAME($PID[$j],$Ticks[$j]);
            ANORA($playername);
            $a = $anora;
            XYZ($POS[$j]);
            WHERE($posx,$posz,0);
            CRASHED($PID[$j],$Ticks[$j]);
            LANDINGSIDE($PID[$j],$posx,$posz);
            if (!$crashed) { // L1:
               echo ("$clocktime $a $playername landed $where in $side territory<br>\n");
            } else {
               echo ("$clocktime $playername survived a forced/crash landing $where in $side territory<br>\n");
            }
         } else {
// SPECIAL DEBUGGING
//            echo ("pilot is dead<br>\n");
         }
      } elseif ($AType[$j] == "7") { // MISSION_END
         echo "$clocktime Mission End<br>\n";
      }
   }

if ($DEBUG) {
   echo "<p>DUMP OF SLIGHTLY PROCESSED PARSING RESULTS BY AType<br>DEBUG = $DEBUG</p>\n";
}

if ($DEBUG == 1 || $DEBUG == 100) {
   // from START AType:0
   echo ("<p>AType:0 START<br>Ticks, Game Date, Game Time, Mission File, MID(null),<br>Game type, Coalitions, Settings, Mods</p>\n");
   echo ("0 $GDate $GTime $MFile $MID<br>$GType $CNTRS $SETTS $MODS<br>\n");
}

if ($DEBUG == 1 || $DEBUG == 101) {
   // from HIT AType:1
      echo ("<p>AType:1 HIT<br>Ticks, AMMO, AID, TID<br>Clock Time, AMMO, Attacker Type, Attacker Name,
      Attacker Pilot, Target Type, Target Name, Target Pilot</p>\n");
   for ($i = 0; $i < $numhits; ++$i) {
      $j = $Hline[$i];
      CLOCKTIME($Ticks[$j]);
      OBJECTTYPE($AID[$j],$Ticks[$j]);
      $attackertype = $objecttype;
      OBJECTNAME($AID[$j],$Ticks[$j]);
      $attackername = $objectname;
      PLAYERNAME($AID[$j],$Ticks[$j]);
      $aplayername = $playername;
      OBJECTTYPE($TID[$j],$Ticks[$j]);
      PLAYERNAME($TID[$j],$Ticks[$j]);
      OBJECTNAME($TID[$j],$Ticks[$j]);
      echo ("$Ticks[$j] $AMMO[$j] $AID[$j] $TID[$j]<br>\n");
      echo ("$clocktime $AMMO[$j] $attackertype $attackername $aplayername $objecttype $objectname $playername<br>\n");
   }
}
 

if ($DEBUG == 1 || $DEBUG == 102) {
   // from DAMAGE AType:2
      echo ("<p>AType:2 DAMAGE<br>Ticks, DMG, AID, TID, POS<br>Clock Time, Damage, Attacker Type,
      Attacker Name, Attacker Pilot, Target Type, Target Name, Target Pilot, Position</p>\n");
   for ($i = 0; $i < $numdamage; ++$i) {
      $j = $Dline[$i];
      CLOCKTIME($Ticks[$j]);
      OBJECTTYPE($AID[$j],$Ticks[$j]);
      $attackertype = $objecttype;
      OBJECTNAME($AID[$j],$Ticks[$j]);
      $attackername = $objectname;
      PLAYERNAME($AID[$j],$Ticks[$j]);
      $aplayername = $playername;
      OBJECTTYPE($TID[$j],$Ticks[$j]);
      PLAYERNAME($TID[$j],$Ticks[$j]);
      OBJECTNAME($TID[$j],$Ticks[$j]);
      XYZ($POS[$j]);
      WHERE($posx,$posz,0);
      echo ("$Ticks[$j] $DMG[$j] $AID[$j] $TID[$j] $POS[$j]<br>\n");
      echo ("$clocktime $DMG[$j] $attackertype $attackername $aplayername $objecttype $objectname $playername $where<br>\n");
   }
}

if ($DEBUG == 1 || $DEBUG == 103) {
   // from KILL AType:3
      echo ("<p>AType:3 KILL<br>Ticks, AID, TID, POS<br> Clock Time, Attacker Type, Attacker Name,
      Attacker Pilot, Target Type, Target Name, Target Pilot, Position</p>\n");

   for ($i = 0; $i < $numkills; ++$i) {
      $j = $Kline[$i];
      CLOCKTIME($Ticks[$j]);
      OBJECTTYPE($AID[$j],$Ticks[$j]);
      $attackertype = $objecttype;
      OBJECTNAME($AID[$j],$Ticks[$j]);
      $attackername = $objectname;
      PLAYERNAME($AID[$j],$Ticks[$j]);
      $aplayername = $playername;
      OBJECTTYPE($TID[$j],$Ticks[$j]);
      PLAYERNAME($TID[$j],$Ticks[$j]);
      OBJECTNAME($TID[$j],$Ticks[$j]);
      XYZ($POS[$j]);
      WHERE($posx,$posz,0);
      echo ("$Ticks[$j] $AID[$j] $TID[$j] $POS[$j]<br>\n");
      echo ("$clocktime $attackertype $attackername $aplayername $objecttype $objectname $playername $where<br>\n");
   }
} // close DEBUGKILL wrapper

if ($DEBUG == 1 || $DEBUG == 104) {
   // from PLAYER_MISSION_END AType:4
      echo ("<p>AType:4 PLAYER_MISSION_END<br>Ticks, PLID, PID, Bullets, Bombs, Position<br>
                            Clock Time, Plane, Pilot, Bullets left, Bombs left, Position</p>\n");
   for ($i = 0; $i < $numends; ++$i) {
      $j = $Eline[$i];
      CLOCKTIME($Ticks[$j]);
      PLAYERNAME($PLID[$j],$Ticks[$j]);
      OBJECTTYPE($PLID[$j],$Ticks[$j]);
      XYZ($POS[$j]);
      WHERE($posx,$posz,0);
      echo ("$Ticks[$j] $PLID[$j] $PID[$j] $BUL[$j] $BOMB[$j] $POS[$j]<br>\n");
      echo ("$clocktime $objecttype $playername $BUL[$j] $BOMB[$j] $where<br>\n");
   }
}

if ($DEBUG == 1 || $DEBUG == 105) {
   // from TAKEOFF AType:5
      echo ("<p>AType:5 TAKEOFF<br>Ticks, PID, POS<br>
             Clock Time, Plane, Pilot, Position </p>\n");
   for ($i = 0; $i < $numtakeoffs; ++$i) {
      $j = $Tline[$i];
      CLOCKTIME($Ticks[$j]);
      OBJECTTYPE($PID[$j],$Ticks[$j]);
      PLAYERNAME($PID[$j],$Ticks[$j]);
      XYZ($POS[$j]);
      WHERE($posx,$posz,1);
      echo ("$Ticks[$j] $PID[$j] $POS[$j]<br>\n");
      echo ("$clocktime $objecttype $playername $where<br>\n");
   }
}

if ($DEBUG == 1 || $DEBUG == 106) {
   // from LANDING AType:6
   echo ("<p>AType:6 LANDING<br>Ticks, PID, POS<br>
             Clock Time, Plane, Pilot, Position</p>\n");
   for ($i = 0; $i < $numlandings; ++$i) {
      $j = $Lline[$i];
      CLOCKTIME($Ticks[$j]);
      OBJECTTYPE($PID[$j],$Ticks[$j]);
      PLAYERNAME($PID[$j],$Ticks[$j]);
      XYZ($POS[$j]);
      WHERE($posx,$posz,0); // the "0" specifies airfields only
      echo ("$Ticks[$j] $PID[$j] $POS[$j]<br>\n");
      echo ("$clocktime $objecttype $playername $where<br>\n");
   }
}

if ($DEBUG == 1 || $DEBUG == 107) {
   // from MISSION_END AType:7
   echo ("<p>AType:7 MISSION_END</p>\n");
   CLOCKTIME($endticks);
   echo ("endticks clocktime<br>\n");
   echo ("$endticks $clocktime<br>\n");
}

if ($DEBUG == 1 || $DEBUG == 108) {
   // from MISSION_OBJECTIVE AType:8
   echo ("<p>AType:8 MISSION_OBJECTIVE<br>Ticks, Objective ID, Position, Coalition, Type, Result</p>\n");
   for ($i = 0; $i < $numlines; ++$i) {
      if ("$AType[$i]" == "8") {
         XYZ($POS[$i]);
         WHERE($posx,$posz,0);
         echo ("$Ticks[$i] $OBJID[$i] $POS[$i] $COAL[$i] $TYPE[$i] $RES[$i]<br>\n");
         echo ("$Ticks[$i] $OBJID[$i] $where $COAL[$i] $TYPE[$i] $RES[$i]<br>\n");
      }
   }
}

if ($DEBUG == 1 || $DEBUG == 109) {
   // from AIRFIELD AType:9
   echo ("<p>AType:9 AIRFIELD<br>Airfield ID, Country, Position, IDs of players stationed here?</p>\n");
   for ($i = 0; $i < $numlines; ++$i) {
      if ("$AType[$i]" == "9") {
         XYZ($POS[$i]);
         WHERE($posx,$posz,0);
         COUNTRYNAME($COUNTRY[$i]); 
         echo ("$AID[$i] $COUNTRY[$i] $POS[$i] $IDS[$i]<br>\n");
         echo ("$AID[$i] $countryname $where $IDS[$i]<br>\n");
      }
   }
}

if ($DEBUG == 1 || $DEBUG == 110) {
   // from PLAYERPLANE AType:10
   //   echo ("<p>PLAYERPLANE # $Ticks[$i] $PLID[$i] $PID[$i] $BUL[$i] $SH[$i] $BOMB[$i] $RCT[$i] $POS[$i] $IDS[$i] $LOGIN[$i] $NAME[$i] $TYPE[$i] $COUNTRY[$i] $FORM[$i] $FIELD[$i] $INAIR[$i] $PARENT[$i]<p>\n");
   echo ("<p>AType:10 PLAYERPLANE, # $numplayers Players<br># Clock Time, Player Name, PLID, PID, BUL, BOMB, Plane Type, Country</p>\n");
   for ($i = 0; $i < $numplayers; ++$i) {
      $j = $Pline[$i];
      CLOCKTIME($Ticks[$j]);
      COUNTRYNAME($COUNTRY[$j]); 
//      echo ("$i $clocktime $NAME[$j] $PLID[$j] $PID[$j] $BUL[$j] $BOMB[$j] $TYPE[$j] $COUNTRY[$j]<br>\n");
      echo ("$i $clocktime $NAME[$j] $PLID[$j] $PID[$j] $BUL[$j] $BOMB[$j] $TYPE[$j] $countryname<br>\n");
   }
}

if ($DEBUG == 1 || $DEBUG == 111) {
   // from GROUPINIT AType:11
   echo ("<p>AType:11 GROUPINIT<br># Clock Time  GID  IDS  LID</p>\n");
   for ($i = 0; $i < $numgroups; ++$i) {
      $j = $Gline[$i]; 
      CLOCKTIME($Ticks[$j]);
      echo ("<p>$i $clocktime $GID[$j] $IDS[$j] $LID[$j]</p>\n");
   }
}

if ($DEBUG == 1 || $DEBUG == 112) {
   // from GAMEOBJECTINVOLVED AType:12
   echo ("<p>AType:12 GAMEOBJECTINVOLVED<br># Clock Time, ID, TYPE, NAME, Country, PID</p>\n");
   for ($i = 0; $i < $numgobjects; ++$i) {
      $j = $GOline[$i]; 
      CLOCKTIME($Ticks[$j]);
      COUNTRYNAME($COUNTRY[$j]); 
      OBJECTTYPE($ID[$j],$Ticks[$j]); 
      OBJECTNAME($ID[$j],$Ticks[$j]);
      echo ("$i $clocktime $ID[$j] $TYPE[$j] $NAME[$j] $countryname $PID[$j]<br>\n");
   }
}

if ($DEBUG == 1 || $DEBUG == 113) {
   // from INFLUENCEAREA_HEADER AType:13
   echo ("<p>AType:13 INFLUENCEAREA_HEADER<br>Clock Time, Area ID, Country, Enabled, By Coalition Inflight Count <br>(Neutral,Entente,Central Powers,War Dogs,Mercenaries,Corsairs,Future)</p>\n");
   for ($i = 0; $i < $numlines; ++$i) {
      if ("$AType[$i]" == "13") {
         CLOCKTIME($Ticks[$i]);
         echo ("$clocktime $AID[$i] $COUNTRY[$i] $ENABLED[$i] $BC[$i]<br>\n");
      }
   }
}

if ($DEBUG == 1 || $DEBUG == 114) {
   // from INFLUENCEAREA_BOUNDARY AType:14
   echo ("<p>AType:14 INFLUENCEAREA_BOUNDARY<br>Ticks, Area ID, Boundary Points</p>\n");
   for ($i = 0; $i < $numlines; ++$i) {
      if ("$AType[$i]" == "14") {
      echo ("$Ticks[$i] $AID[$i] $BP[$i]<br>\n");
      }
   }
}

if ($DEBUG == 1 || $DEBUG == 115) {
   // from VERSION AType:15
   echo ("<p>AType:15 VERSION<br>Ticks, Version</p><p>0 15 (over and over again) :)</p>\n");
//   for ($i = 0; $i < $numlines; ++$i) {
//      if ("$AType[$i]" == "15") {
//      echo ("$Ticks[$i] $VER[$i]<br>\n");
//      }
//   }
} // end if $DEBUG 
}
?>
