<?php

if (!isset($website) ) { header('HTTP/1.1 404 Not Found'); die; }

     $gameid = safeEscape( (int) $_GET["game"]);
	 $MenuClass["games"] = "active";
	 $c=0;
	 
	 //first check if this is custom map
	 $W3mmd = Get_w3mmdplayers($gameid);
	 $GameData = array();
	
     //Custom map	
	 if ( !empty($W3mmd) ) {
	 $GameData =  $W3mmd ;
	 $HomeTitle = ($GameData[0]["gamename"]);
	 $HomeDesc = os_strip_quotes($GameData[0]["gamename"]);
	 $HomeKeywords = strtolower( os_strip_quotes($GameData[0]["gamename"])).','.$HomeKeywords;
	 $BestPlayer = "";
	 $PlayerAssists = "";
	 $PlayerDeaths  = "";
	 $PlayerCK  = "";
	 $PlayerCD  = "";
	 $PlayerKills = "";
	 $update_view = $db->exec("UPDATE ".OSDB_GAMES." SET views = views + 1 WHERE id = '".(int)$gameid ."' ");
	 //REPLAY
	 $duration = "";
	 $durationTime = "";
     $replayDate =  "";  //3*3600 = +3 HOURS,   +0 minutes.
     $replayDate = "";
     $gametimenew = "";
	 $gid = $gameid;
	 $gamename = $GameData[$c]["gamename"];
	 require_once('inc/get_replay.php');
	 if ( file_exists($replayloc) ) $GameData[$c]["replay"]  = $replayloc;
	 } else {
	 
	 /////////////////////////////////////////////////////////////////////
	 //DOTA, LOD
	 $sth = $db->prepare(  getSingleGame( (int)$gameid ) );
	 $result = $sth->execute();

	 if ( $sth->rowCount()<=0 ) {
     require_once(OS_PLUGINS_DIR.'index.php');
     os_init();
	 header('location: '.OS_HOME.'?404'); die; 
	 }
	 
	 
	 
	 $update_view = $db->exec("UPDATE ".OSDB_GAMES." SET views = views + 1 WHERE id = '".(int)$gameid ."' ");
	 
	 $row = $sth->fetch(PDO::FETCH_ASSOC);
	 
	 $GameData[$c]["creatorname"]  = ($row["creatorname"]);
	 $GameData[$c]["duration"]  = secondsToTime($row["duration"]);
	 $GameData[$c]["datetime"]  = date($DateFormat,strtotime($row["datetime"]));
	 $GameData[$c]["dt"]  = ($row["datetime"]);
	 $GameData[$c]["gamename"]  = ($row["gamename"]);
	 $GameData[$c]["winner"]  = ($row["winner"]);
     $GameData[$c]["views"]  = ($row["views"]);
	 //SET META INFORMATION AND PAGE NAME
	 $HomeTitle = ($row["gamename"]);
	 $HomeDesc = os_strip_quotes($row["gamename"]);
	 $HomeKeywords = strtolower( os_strip_quotes($row["gamename"])).','.$HomeKeywords;
	 
	 //REPLAY
	 $duration = secondsToTime($row["duration"]);
	 $durationTime = $row["duration"];
     $replayDate =  strtotime($row["datetime"]);  //3*3600 = +3 HOURS,   +0 minutes.
     $replayDate = date("Y-m-d H:i",$replayDate);
     $gametimenew = substr(str_ireplace(":","-",date("Y-m-d H:i",strtotime($replayDate))),0,16);
	 $gid = $gameid;
	 $gamename = $GameData[$c]["gamename"];
	 require_once('inc/get_replay.php');
	 
	 if ( file_exists($replayloc) ) $GameData[$c]["replay"]  = $replayloc;
	 //END REPLAY
	 
	 $temp_points  = 0;
	 $temp_kills   = 0;
	 $temp_assists = 0;
	 $temp_deaths  = 0;
	 $temp_ck      = 0;
	 $temp_cd      = 0;
	 $counter = 0;
	 $ScourgeRow = 0;
     $SentinelRow = 0;
  
	 if ( file_exists("inc/geoip/geoip.inc") ) {
	 include("inc/geoip/geoip.inc");
	 $GeoIPDatabase = geoip_open("inc/geoip/GeoIP.dat", GEOIP_STANDARD);
	 $GeoIP = 1;
	 }
	 
	$sth = $db->prepare(  getGameInfo( (int) $gameid)  );
	$result = $sth->execute();
	while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	$GameData[$c]["hideElement"]  = "";
	if( $row["newcolour"] <= 5 ) $GameData[$c]["counter"] = $row["newcolour"];
	if( $row["newcolour"] > 5 ) $GameData[$c]["counter"] = $row["newcolour"]-1;
	$GameData[$c]["side"] = "";
	if ( $row["newcolour"] >5 AND $ScourgeRow == 0 ) { $GameData[$c]["side"] = "scourge"; $ScourgeRow = 1; }
	if ( $row["newcolour"] <=5 AND $SentinelRow == 0 ) { $GameData[$c]["side"] = "sentinel"; $SentinelRow = 1;  }
	
	
	if ($GeoIP == 1 ) {
	$GameData[$c]["letter"]   = geoip_country_code_by_addr($GeoIPDatabase, $row["ip"]);
	$GameData[$c]["country"]  = geoip_country_name_by_addr($GeoIPDatabase, $row["ip"]);
	}
	if ($GeoIP == 1 AND empty($GameData[$c]["letter"]) ) {
                if( strlen($row["spoofedrealm"]) <= 2) {
                        $GamesData[$c]["letter"] = "GAR";
                        $GamesData[$c]["country"] = "Garena";
                } else {
                        if( strtolower($row["spoofedrealm"]) == "europe.battle.net" ) {
                                $GamesData[$c]["letter"] = "EU";
                                $GamesData[$c]["country"] = "Europe";
                        }
                        else if( strtolower($row["spoofedrealm"]) == "uswest.battle.net" OR strtolower($row["spoofedrealm"]) == "useast.battle.net" ) {
                                $GamesData[$c]["letter"] = "US";
                                $GamesData[$c]["country"] = "USA";
                        }
                        else if( strtolower($row["spoofedrealm"]) == "asia.battle.net" ) {
                                $GamesData[$c]["letter"] = "CN";
                                $GamesData[$c]["country"] = "Asia";
                        } else {
                                $GamesData[$c]["letter"] = "A1";
                                $GamesData[$c]["country"] = "Unknown";
                        }
                }
	}
	 
	 $GameData[$c]["heroid"]  = ($row["hero"]);
	 $GameData[$c]["hero_link"]  = 1;
	 
	 $GameData[$c]["userid"]  = ($row["userid"]);
	 //if user is NOT ranked (stats updated) show username instead of user ID
	 if ( empty($row["userid"]) )
	 $GameData[$c]["userid"]  = $row["name"];
	 $GameData[$c]["alias_id"]  = $row["alias_id"];
     $GameData[$c]["kills"]  = ($row["kills"]);
	 $GameData[$c]["deaths"]  = ($row["deaths"]);
	 $GameData[$c]["assists"]  = ($row["assists"]);
	 $GameData[$c]["creepkills"]  = ($row["creepkills"]);
	 $GameData[$c]["creepdenies"]  = ($row["creepdenies"]);
	 $GameData[$c]["neutralkills"]  = ($row["neutralkills"]);
	 $GameData[$c]["towerkills"]  = ($row["towerkills"]);
	 $GameData[$c]["raxkills"]  = ($row["raxkills"]);
	 $GameData[$c]["courierkills"]  = ($row["courierkills"]);
	 $GameData[$c]["spoofedrealm"]  = ($row["spoofedrealm"]);
	 $GameData[$c]["level"]  = ($row["level"]);
	 $GameData[$c]["gold"]  = ($row["gold"]);
	 $GameData[$c]["item1"]  = ($row["item1"]);
	 $GameData[$c]["item2"]  = ($row["item2"]);
	 $GameData[$c]["item3"]  = ($row["item3"]);
	 $GameData[$c]["item4"]  = ($row["item4"]);
	 $GameData[$c]["item5"]  = ($row["item5"]);
	 $GameData[$c]["item6"]  = ($row["item6"]);
	 
	 $GameData[$c]["itemname1"]  = os_strip_quotes($row["itemname1"]);
	 $GameData[$c]["itemname2"]  = os_strip_quotes($row["itemname2"]);
	 $GameData[$c]["itemname3"]  = os_strip_quotes($row["itemname3"]);
	 $GameData[$c]["itemname4"]  = os_strip_quotes($row["itemname4"]);
	 $GameData[$c]["itemname5"]  = os_strip_quotes($row["itemname5"]);
	 $GameData[$c]["itemname6"]  = os_strip_quotes($row["itemname6"]);
	 
	 $GameData[$c]["description"]  = os_strip_quotes($row["description"]);
	 
	 if ( empty($row["name"]) ) $row["name"] = '&nbsp;';
	 
	 if ( !isset($MostCD)  )       { $MostCD = ($row["name"]); $temp_cd = $row["creepdenies"]; $PlayerCD = $row["creepdenies"]; $MostCDID = ($row["userid"]); }
	 if ( !isset($MostCK)  )       { $MostCK = ($row["name"]); $temp_ck = $row["creepkills"]; $PlayerCK = $row["creepkills"]; $MostCKID = ($row["userid"]); }
	 if ( !isset($MostDeaths)  )   { $MostDeaths = ($row["name"]); $temp_deaths = $row["deaths"]; $PlayerDeaths = $row["deaths"]; }
	 if ( !isset($MostAssists)  )  { $MostAssists = ($row["name"]); $temp_assists = $row["assists"]; $PlayerAssists = $row["assists"]; $MostAssistsID = ($row["userid"]); }
	 
	 if ( !isset($MostKills)  )    { 
	 $MostKills = ($row["name"]); $temp_kills = $row["kills"]; $PlayerKills = $row["kills"]; 
	 $MostKillsID = ($row["userid"]);
	 }
	 
	 if ( !isset($BestPlayer)  )   {  $BestPlayer = ($row["name"]); $BestPlayerID = ($row["userid"]); }
	
	if ( $row["creepdenies"] > $temp_cd ) {
	   $MostCD = ($row["name"]); $PlayerCD = $row["creepdenies"]; $temp_cd= $row["creepdenies"];
	   $MostCDID = ($row["userid"]);
	 }
	
	if ( $row["creepkills"] > $temp_ck ) {
	   $MostCK = ($row["name"]); $PlayerCK = $row["creepkills"]; $temp_ck= $row["creepkills"]; 
	   $MostCKID = ($row["userid"]);
	 }
	
	if ( $row["deaths"] > $temp_deaths ) {
	   $MostDeaths = ($row["name"]); $PlayerDeaths = $row["deaths"]; $temp_deaths= $row["deaths"];
	   $MostDeathsID = ($row["userid"]);
	 }
	 
	 if ( $row["assists"] > $temp_assists ) {
	   $MostAssists = ($row["name"]); $PlayerAssists = $row["assists"]; $temp_assists = $row["assists"];
	   $MostAssistsID = ($row["userid"]);
	 }
	 
	 if ( $row["kills"] > $temp_kills ) {
	   $MostKills = ($row["name"]); $PlayerKills = $row["kills"]; $temp_kills = $row["kills"];
	   $MostKillsID = ($row["userid"]);
	 }
	 
	 $score_points = ($row["kills"] -  $row["deaths"]) + ($row["assists"]*0.3);
	 if ( $score_points > $temp_points ) {
	 $BestPlayer = ($row["name"]);
	 $BestPlayerID = ($row["userid"]);
	 $temp_points = $score_points;
	 }
	 
	 if (!empty($row["hero"]) ) $GameData[$c]["hero"]  = ($row["hero"].".$HeroFileExt");
	 else  $GameData[$c]["hero"]  = "blank.gif";
	 
	 if (!empty( $row["itemicon1"] ) ) $GameData[$c]["itemicon1"]  = ($row["itemicon1"]);
	 else $GameData[$c]["itemicon1"] = "empty.gif";
	 
	 if (!empty( $row["itemicon2"] ) ) $GameData[$c]["itemicon2"]  = ($row["itemicon2"]);
	 else $GameData[$c]["itemicon2"] = "empty.gif";
	 if (!empty( $row["itemicon3"] ) ) $GameData[$c]["itemicon3"]  = ($row["itemicon3"]);
	 else $GameData[$c]["itemicon3"] = "empty.gif";
	 if (!empty( $row["itemicon4"] ) ) $GameData[$c]["itemicon4"]  = ($row["itemicon4"]);
	 else $GameData[$c]["itemicon4"] = "empty.gif";
	 if (!empty( $row["itemicon5"] ) ) $GameData[$c]["itemicon5"]  = ($row["itemicon5"]);
	 else $GameData[$c]["itemicon5"] = "empty.gif";
	 if (!empty( $row["itemicon6"] ) ) $GameData[$c]["itemicon6"]  = ($row["itemicon6"]);
	 else $GameData[$c]["itemicon6"] = "empty.gif";
	 
	 $GameData[$c]["left"]  = secondsToTime($row["left"]);
	 $GameData[$c]["leftreason"]  = ($row["leftreason"]);
	 $ScoreGain = "";
	 if ($row["newcolour"]<=5 AND $row["winner"] == 1)  $ScoreGain=1; else
	 if ($row["newcolour"]>5  AND $row["winner"] == 2)  $ScoreGain=1; else
	 if ($row["newcolour"]<=5 AND $row["winner"] == 2)  $ScoreGain=2; else
	 if ($row["newcolour"]>5  AND $row["winner"] == 1)  $ScoreGain=2; else
	 if ($row["newcolour"]>5  AND $row["winner"] == 0)  $ScoreGain=0;
	 if ($row["newcolour"]<=5  AND $row["winner"] == 0)  $ScoreGain=0;
	 
	 if ( $row["left"] <= ($durationTime - $MinDuration) ) {
	 if ($row["newcolour"]<=5 AND $row["winner"] == 1) $ScoreGain= 0; else
	 if ($row["newcolour"]>5  AND $row["winner"] == 2) $ScoreGain= 0; else 
	 if ($row["newcolour"]<=5 AND $row["winner"] == 2) $ScoreGain=2; else
	 if ($row["newcolour"]>5  AND $row["winner"] == 1) $ScoreGain=2;
	 $GameData[$c]["leaver"] = '1'; 
	 $GameData[$c]["leftreason"]."<div>".$lang["leaver"]."</div>";
	 } else $GameData[$c]["leaver"] = '';
	 
	 if ( $ScoreGain == 1 ) { $GameData[$c]["score_points"] = "+".$ScoreWins; $GameData[$c]["class"] = ' won'; }
	 if ( $ScoreGain == 2 ) { $GameData[$c]["score_points"] = "-".$ScoreLosses; $GameData[$c]["class"] = ' lost'; }
     if ( $ScoreGain == 0 ) { $GameData[$c]["score_points"] = 0; $GameData[$c]["class"] = ' draw'; }
	 
	 if ( $row["left"] <= ( $durationTime - $LeftTimePenalty) ) {
	 $GameData[$c]["score_points"] = "-".$ScoreDisc;
	 $GameData[$c]["class"] = ' lost';
	 }

	 $GameData[$c]["banname"]  = ($row["banname"]);
	 $GameData[$c]["name"]  = ($row["name"]);
	 
	 $GameData[$c]["banned"]  = ($row["banned"]);
	 $GameData[$c]["admin"]  = ($row["admin"]);
     //$GameData[$c]["safelist"]  = ($row["safelist"]);
	 //CHECK IF USER IS BANNED
	 if ( strtolower($row["name"]) == strtolower($row["banname"]) ) {
	    $GameData[$c]["full_name"]  = '<span class="banned">'.($row["name"])."</span>";
	 } 
	 else 
	 $GameData[$c]["full_name"]  = ($row["name"]);
	 
	//Don't show ban on safelisted user, because they are not really banned!
	if ( $row["admin"]>1 ) {
	$GameData[$c]["banned"] = 0;
	$GameData[$c]["banname"] ="";
	}
	 
	 if ( $HideEmptySlots == 1 AND (strlen($row["name"])<=2 OR $row["left"]<=0) ) 
	 $GameData[$c]["hideslot"] = 'hiddenslot'; else $GameData[$c]["hideslot"] = "";
	 
	 if ($GameData[0]["winner"] == 1) { 
	 $GameData[$c]["display_winner"] = $lang["sent_winner"];
	 $GameData[$c]["display_loser"]  = $lang["scou_loser"];
	 } else
	 if ($GameData[0]["winner"] == 2) {  
	 $GameData[$c]["display_winner"] = $lang["sent_loser"]; 
	 $GameData[$c]["display_loser"]  = $lang["scou_winner"]; 
	 }  else
	 if ($GameData[0]["winner"] == 0) {  
	 $GameData[$c]["display_winner"] = $lang["draw_game"]; 
	 $GameData[$c]["display_loser"] = $lang["draw_game"]; 
	 }
	 
	 
	 $GameData[$c]["newcolour"]  = ($row["newcolour"]);
	 $GameData[$c]["gameid"]  = ($row["gameid"]);
	 $GameData[$c]["banname"]  = ($row["banname"]);
	 $GameData[$c]["ip"]  = ($row["ip"]);
	 $GameData[$c]["newcolour"]  = ($row["newcolour"]);
	 $c++;
	}

	if ( isset($GeoIP) AND $GeoIP == 1) geoip_close($GeoIPDatabase);
		 
	}
	 //Lobby/game log
	 $sth2 = $db->prepare("SELECT * FROM oh_lobby_game_logs WHERE gameid = '".(int)$gameid ."' ");
	 $result = $sth2->execute();
	 $GameLogData = array();
	 $i = 0;
	 while ($row2 = $sth2->fetch(PDO::FETCH_ASSOC)) {
	   $GameLogData[$i]["gameid"]  = ($row2["gameid"]);
	   $GameLogData[$i]["botid"]  = ($row2["botid"]);
	   $GameLogData[$i]["gametype"]  = ($row2["gametype"]);
	   $GameLogData[$i]["lobbylog"]  = ($row2["lobbylog"]);
	   $GameLogData[$i]["lobbylog"] = preg_replace('~\!pw (.+?)<\/div>~is' , '</div>', $GameLogData[$i]["lobbylog"]);
	   $GameLogData[$i]["gamelog"]  = ($row2["gamelog"]);
	   $GameLogData[$i]["gamelog"] = str_replace('<a ', '<a target="_blank" ', $GameLogData[$i]["gamelog"] );
	   $i++;
	 }

?>
