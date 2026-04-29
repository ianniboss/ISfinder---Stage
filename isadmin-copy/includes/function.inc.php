<?php
//______________________________connexions________________________________________
function connexion()
{
        $host = "localhost";
        $user = "client";
        $bdd = "reseau";
        $password = "client";
        mysql_connect($host,$user,$password) or die("erreur de connexion au serveur");
        mysql_select_db($bdd) or die("erreur de connexion à la base de données");

}
//___________________________________erreur_________________________________
function erreur_sql($res,$requete)
{
        if (!$res)
        {
        $erreur_no = mysql_errno();
        $erreur_txt = mysql_error();
        echo "erreur sql :".$requete."<br>\n";
        echo "erreur :".mysql_errno().":".mysql_error()."<br>\n";
        exit;
        }
}
//________________________executer requete____________________________________


function execute_sql($req){
        $res=mysql_query($req);
        erreur_sql($res,$req);
        return $res;
}
//-----------------chopper les types------------------------------------
function get_types(){
	connexion();
	$res=execute_sql("SELECT * FROM `types` ORDER BY typem");
	while ($tab = mysql_fetch_row($res)) {
                $id=$tab[0];
                $tableau[$id]=$tab[1];
        }
        return $tableau;
}
//--------------chopper les type de proc---------------------------
function get_proc(){
        connexion();
        $res=execute_sql("SELECT * FROM `processeur` ORDER BY processor");
        while ($tab = mysql_fetch_row($res)) {
                $id=$tab[0];
                $tableau[$id]=$tab[1];
        }
        return $tableau;
}
//--------------chopper les equipes---------------------------
function get_equipes(){
        connexion();
        $res=execute_sql("SELECT * FROM `equipes` ORDER BY equipe");
        while ($tab = mysql_fetch_row($res)) {
                $id=$tab[0];
                $tableau[$id]=$tab[1];
        }
        return $tableau;
}

//--------------chopper les switches--------------------------------
function get_switches(){
        connexion();
        $res=execute_sql("SELECT * FROM `switches` ORDER BY idx");
        while ($tab = mysql_fetch_row($res)) {
                $id=$tab[0];
                $tableau[$id]=$tab[1];
        }
        return $tableau;
}
//-----------------chopper un type------------------------------------
function get_machine($idtype){
        connexion();
        $res=execute_sql("SELECT typem FROM `types` WHERE idx = \"$idtype\"");
        $tab = mysql_fetch_row($res);
        $machine=$tab[0];
        
        return $machine;
}
//--------------chopper un OS --------------------------------
function get_os_mach($idos){
        connexion();
        $res=execute_sql("SELECT syst FROM `systemes` WHERE idx=\"$idos\"");
        $tab = mysql_fetch_row($res);
        return $tab[0];
}
//--------------chopper les os-------------------------------------
function get_os(){
        connexion();
        $res=execute_sql("SELECT * FROM `systemes` ORDER BY syst");
        while ($tab = mysql_fetch_row($res)) {
                $id=$tab[0];
                $tableau[$id]=$tab[1];
        }
        return $tableau;
}

		
//----------------------------------------------
function affiche_switch($switch,$texte){

#
#  Catalyst 2950 E au troisieme recherche dans la base des types de machine
#

        $dem = mysql_query("SELECT * from machines WHERE switch like '$switch'") or die ("echec");
        while ($tableau = mysql_fetch_array($dem)){
		$port = $tableau["port"];
                $ipc2950E["$port"] = $tableau["ip"];
		$c2950E["$port"] = $tableau["type"];
                if ($c2950E["$port"] == "Macintosh") {
                  	 $c2950Eim[$port] = "mac";}
                else if ($c2950E["$port"] == "PC assembleur" OR $c2950E["$port"] == "PC Transtec") {
                         $c2950Eim["$port"] = "pc";}
                else if ( $c2950E["$port"] == "PC HP") {
			$c2950Eim["$port"] = "hp";}
		else if ( ereg("ortable",$c2950E["$port"])) {
			$c2950Eim["$port"] = "port";}
		else if ( $c2950E["$port"] == "PC Dell") {
			$c2950Eim["$port"] = "dell";}
		else if ( $c2950E["$port"] == "Imp. Laser" OR $c2950E["$port"] == "Imp. Jet encre" OR  $c2950E["$port"] == "JetDirect") {
                         $c2950Eim["$port"] = "print";}
                else { $c2950Eim["$port"] = "inc";}

	}

	$fix = "192.168.11.";


#
#   Switch du 3eme, affichage des ports
#

    print "<td width='50' rowspan='3'><font size='1'>$texte</font></td>\n";
    print "<td width='150' rowspan='3'><a href='simple.php?ipvar=24'><img src='images/c2950.gif' width='150' height='17' border='0'></a></td>\n";
	for ($ii=1;$ii<=47;$ii=$ii+2){
		$ipport = $ipc2950E[$ii];
		if ($ipport != 0){
        exec("/usr/local/sbin/fping -r1 -t50 $fix$ipport", $state);
	print "$state[1]";
        if (ereg("unreachable" , $state[0] )) {
                $etat[$ii]= 'off';
                }
	$link[$ii]="<a href='simple.php?ipvar=$ipc2950E[$ii]'>";
	$finlink[$ii]="</a>";
	$state=array();
		}
		print "<td>$link[$ii]<img src='images/a$c2950Eim[$ii]$etat[$ii].gif' border='0' title='port $ii'>$finlink[$ii]</td>\n";
	}
	print "</tr><tr>";
	for ($zz=1;$zz<=24;$zz++) {
		print "<td><img src='images/croix.gif'></td>";
	}
	print "</tr><tr>";
        for ($ii=2;$ii<=48;$ii=$ii+2){
                $ipport = $ipc2950E[$ii];
                if ($ipport != 0){
        exec("/usr/local/sbin/fping -r1 -t50 $fix$ipport", $state);
        if (ereg("unreachable" , $state[0] )) {
                $etat[$ii]= 'off';
                }
        $link[$ii]="<a href='simple.php?ipvar=$ipc2950E[$ii]'>";
        $finlink[$ii]="</a>";
        $state=array();

                }
                print "<td>$link[$ii]<img src='images/a$c2950Eim[$ii]$etat[$ii].gif' border='0' title='port $ii'>$finlink[$ii]</td>\n";
        }
}
?>
	
