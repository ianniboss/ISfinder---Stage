<?php
// Creation d'une image png à partir des infos du calendrier- LL-2004 
	header("Content-type: image/png");

// Nécessaire pour avoir la date en francais
	setlocale(LC_TIME,"fr_FR");      

// Pour register globals off
	$jour=$_GET["jour"];
	$mois=$_GET["mois"];
	$annee=$_GET["annee"];
	$id_lieu=$_GET["id_lieu"];
// Variable de l'image, hcase: hauteur de la case, lcase sa largeur, $bord la marge
	$large = 700;
	$haut = 500;
        $bord = 40;
	$hcase=10;
	$lcase=140;
        Include_Once ("../function.inc.php");

#
# creation de l'image 
#
        $im = ImageCreate($haut+$bord*2,$large+$bord*2);

// Allocation des couleurs
        $blanc = ImageColorAllocate($im,255,255,255);
        $noir = ImageColorAllocate($im,0, 0, 0);
	$gris = ImageColorAllocate($im,113,113,113);

// Tracé des lignes verticales principales (heures) puis des 1/4 d'heures (en gris)
	for ($h=0;$h<=10;$h++) {
		Imageline($im,$bord+$h*$hcase*4,$bord,$bord+$h*$hcase*4,$bord+$large,$noir);
		for ($i=1;$i<=3;$i++){
			Imageline($im,$bord+($i+4*$h)*$hcase,$bord,$bord+($i+4*$h)*$hcase,$bord+$large,$gris);
		}
	}
        Imageline($im,$bord+11*$hcase*4,$bord,$bord+11*$hcase*4,$bord+$large,$noir);

// Tracé des lignes horizontales
	for ($j=0;$j<=5;$j++){
	        Imageline($im,$bord,$bord+$j*$lcase,$haut-20,$bord+$j*$lcase,$noir);
	}

// Ecriture des heures
	for ($k=8;$k<=19;$k++) {
        	ImageStringup($im,3,$bord+$hcase*4*($k-8)-5,$large+$bord+15,$k,$noir);
	}
// Affichage des post-it
        connexion();
        $res2=execute_sql("select * from type");

// Récupération des types d'actualité
        while ($table = mysql_fetch_array($res2)){
                $id = $table["id_type"];
                $tabtype["$id"]=$table["type"];
                $color_eve[$id]=$table["id_color"];
        }

// On considère que le jour passé en paramètre ($jour) est le premier de la semaine
       for ($l=0;$l<5;$l++) {

// Des trucs sur les dates :-)
		$jour_entete= strftime("%A %e %B",mktime(12,0,0,$mois,$jour,$annee));
                $jourj = strftime("%Y-%m-%d",mktime(12,0,0,$mois,$jour,$annee));
                $timbre_temps = getdate(mktime(12,0,0,$mois,$jour,$annee));
                $jsem = $timbre_temps['wday'];

// On récupère les événements du jour et du lieu concerné
                $requette = "SELECT * FROM evenements where (date Like '$jourj' and id_lieu = '$id_lieu') order by heure";
                $result=execute_sql($requette);
                if (!$result) {die( "rien");}
                while ($tabeve = mysql_fetch_array($result)){
                        $id_type = $tabeve['id_type'];
                        $type = $tabtype[$tabeve['id_type']];
                        $heure = substr($tabeve["heure"],0,5);
                        $id = $tabeve["id"];
                        $heure_fin = $tabeve['fin'];
                        $nom_contact = $tabeve['nom_contact'];
                        $desc_court = $tabeve['desc_court'];
			$color=$color_eve[$id_type];

// Magouille sur les couleurs assez top!
			$hex_r=hexdec(substr($color,1,2));
			$hex_v=hexdec(substr($color,3,2));
			$hex_b=hexdec(substr($color,5,2));
			$postit_color=Imagecolorallocate($im,$hex_r,$hex_v,$hex_b);

// Heures de début et de fin pour le positionement des postit
       			$h_d = substr($heure,0,2);
        		$m_d = substr($heure,3,2);
        		$h_f = substr($heure_fin,0,2);
        		$m_f = substr($heure_fin,3,2);
			$x1=$bord+(($h_d-8)*4*$hcase)+ (($m_d/15)*$hcase);
			$y1=$bord+$large-(($jsem-1)*$lcase);
			$x2=$bord+(($h_f-8)*4*$hcase)+(($m_f/15)*$hcase);
			$y2=$y1-$lcase;

// On raccourcit certains renseignements pour ne pas qu'ils dépassent du postit
        		if (strlen($desc_court) > 25) {
        			$desc_raccourcie=substr($desc_court,0,22)."...";
        		} else {
        		 	$desc_raccourcie=substr($desc_court,0,25);
        		}
                        if (strlen($nom_contact) > 25) {
                                $contact_raccourci=substr($nom_contact,0,22)."...";
                        } else {
                                $contact_raccourci=substr($nom_contact,0,25);
                        }

// Ecriture des postit, attention, il faut commencer par le haut gauche et finit par le bas droite, d'où la bidouille sur les x et y
			Imagefilledrectangle($im,$x1+1,$y2+1,$x2-1,$y1-1,$postit_color);

// Le texte dans chaque postit
			ImageStringup($im,1,$x1+4,$y1-4,$desc_raccourcie,$noir);
			ImageStringup($im,1,$x1+14,$y1-4,$type,$noir);
			ImageStringup($im,1,$x1+24,$y1-4,$contact_raccourci,$noir);
                }

// On n'oublie pas d'écrire le jour
	        ImageStringup($im,2,$bord-20,$bord+$large-($lcase*$l)-10,$jour_entete,$noir);

// On passe au jour suivant
	$jour=$jour+1;
	}

// Petite précaution, on a écrire en bas de l'image la date du jour d'impression.
	$aujourdhui=strftime("%A %e %B",time());
	if ($id_lieu == 1) {
	$texte_legende="Etat d'occupation de la salle de conférence de l'IEFG le ".$aujourdhui;
	}
	else if ($id_lieu == 2) {
        $texte_legende="Etat d'occupation de la salle de réunion de l'IBCG le ".$aujourdhui;
	}
	Imagestringup($im,1,$bord+$haut-40,$large,$texte_legende,$noir);
#	$img=imagerotate($im,90,$blanc);
// On créé l'image, pour la détruire aussitôt, c'est trop injuste
        ImagePng($im);
        ImageDestroy($im);

// C'est fini!
?>
