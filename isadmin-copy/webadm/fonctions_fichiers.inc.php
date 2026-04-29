<?php
/**
 * FFFFFF IIIIII  CCCC  H    H IIIIII EEEEEE RRRRR   SSSS
 * FF       II   C    C H    H   II   EE     R    R S   
 * FFFFF    II   C      HHHHHH   II   EEEEE  RRRRR   SSSS
 * FF       II   C    C H    H   II   EE     R RR        S
 * FF     IIIIII  CCCC  H    H IIIIII EEEEEE R   RR  SSSS
 **/

define('DOSSIER_FICHIERS_DEFAUT', 'img/upload');
define('TAILLE_FICHIER_DEFAUT', '4096');
define('KO', '1024');
define('MO', '1048576');
define('GO', '1073741824');
define('TO', '1099511627776');
define('FICHIER_IMAGE', 'image');
define('FICHIER_PDF', 'pdf');
define('FICHIER_TEXTE', 'texte');
define('FICHIER_WORD', 'word');
define('FICHIER_EXCEL', 'excel');
define('FICHIER_POWERPOINT', 'powerpoint');
define('FICHIER_OPENOFFICE', 'openoffice');
$Extensions_Valides = array(FICHIER_IMAGE => array('jpg' , 'jpeg' , 'gif' , 'png'),
                            FICHIER_PDF => array('pdf'),
                            FICHIER_TEXTE => array('txt'),
                            FICHIER_WORD => array('doc', 'docx'),
                            FICHIER_EXCEL => array('xls', 'xlsx'),
                            FICHIER_POWERPOINT => array('pps', 'ppsx', 'ppt', 'pptx'),
                            FICHIER_OPENOFFICE => array('odt', 'ods'));
$UPLOAD_ERRORS = array(UPLOAD_ERR_OK=>"Aucune", UPLOAD_ERR_INI_SIZE=>"La taille du fichier d&eacute;passe celle admise par le serveur",
                       UPLOAD_ERR_FORM_SIZE=>"La taille du fichier d&eacute;passe celle admise par le site", UPLOAD_ERR_PARTIAL=>"Le t&eacute;l&eacute;chargement ne s'est pas termin&eacute;",
                       UPLOAD_ERR_NO_FILE=>"Aucun fichier n'a &eacute;t&eacute; t&eacute;l&eacute;charg&eacute;", UPLOAD_ERR_NO_TMP_DIR=>"Un dossier temporaire est manquant",
                       UPLOAD_ERR_CANT_WRITE=> "Le fichier n'a pas pu &ecirc;tre &eacute;crit sur le serveur", UPLOAD_ERR_EXTENSION=>"Une extension de PHP bloque l'envoi");

/**
 * Retourne la liste des fichiers du dossier $dir, dans un tableau de valeurs. Chaque fichier est associ&eacute; &agrave; un index num&eacute;rique d&eacute;butant par 1.
 * L'index 0 du tableau contient le nombre de fichiers retourn&eacute;s.
 * @param   string  $dossier        Dossier dans lequel rechercher les fichiers
 * @param   string  $typeFichier    Si $typeFichier est une chaîne, elle doit correspondre &agrave; une constante FICHIER_xxxx, cl&eacute; du tableau $Extensions_Valides.
 * @param   array   $typeFichier    Si $typeFichier est un tableau, il doit correspondre &agrave; une liste d'extensions accept&eacute;es.
 * @param   boolean $pointS         si vaut TRUE, garde les fichiers d&eacute;butants par un "." (par d&eacute;faut FALSE)
 **/
function listeFichiers($dossier, $typeFichier='TOUS', $pointS=FALSE) {
    $Extensions_Valides = $GLOBALS['Extensions_Valides'];
    $tousFichiers = scandir($dossier);
    if (is_array($typeFichier)) {    $extensionsOK = $typeFichier;   }
    elseif (is_string($typeFichier)) {
        if ( array_key_exists($typeFichier, $Extensions_Valides) ) {
            $extensionsOK = $Extensions_Valides[$typeFichier];
            $toutesExtensions = FALSE;
        } else {
            $extensionsOK = array();
            $toutesExtensions = TRUE;
        }
    }
    $tableau = array(0=>0);
    foreach ($tousFichiers as $unFichier) {    // Selectionne seulement les images
        $conserver = TRUE;
        $partNomFichier = pathinfo($unFichier);
		if (!array_key_exists('extension', $partNomFichier)) {	$partNomFichier['extension'] = ''; }
        if ( ( ($pointS) or (substr($unFichier,1,1)<>'.') )   // on garde les "." ou le fichier ne commence pas par un point
         and ( ($toutesExtensions) or (in_array($partNomFichier['extension'], $Extensions_Valides[FICHIER_IMAGE])) ) ) {   // on garde tout si * ou si l'extension est accept&eacute;e
            $tableau[] = $unFichier;
        }
    }
    $tableau[0] = count($tableau)-1;
    return $tableau;
}

/**
 * Renvoi le nom du fichier sous la forme dossier/fichier.php au lieu de ////dossier/fichier.php
 **/
function nomFichier($fichier) {
    return pathinfo($fichier, PATHINFO_FILENAME);
}

function extensionFichier($fichier) {	// Renvoi l'extension du fichier (les caract&egrave;res situ&eacute;s &agrave; droite du dernier ".", chaine vide si pas de point...)
    return pathinfo($fichier, PATHINFO_EXTENSION);
}
/** Renvoie VRAI si l'extension du fichier indiqu&eacute; correspond au type d'extension indiqu&eacute;.
 * $typeFichier peut &ecirc;tre :
 * - une chaîne correspondante &agrave; une constante FICHIER_xxxx
 * - une chaine correspondante &agrave; une seule extension cherch&eacute;e
 * - un tableau de chaînes correspondant &agrave; une liste d'extensions cherch&eacute;es
 * Si des extensions ne sont pas du bon type, elles sont ignor&eacute;es.
 * Si aucune extension valide n'est indiqu&eacute;e, renvoie FALSE syst&eacute;matiquement
 * Toute extension qui ne d&eacute;buterait pas par un POINT, sera pr&eacute;c&eacute;d&eacute;e d'un point.
 * Attention car seule la derni&egrave;re extension est regard&eacute;e (test.inc.php renverra TRUE si on cherche l'extension PHP, mais pas INC)
 * */
function extensionFichierValide($fichier, $typeFichier) {
    $Extensions_Valides = $GLOBALS['Extensions_Valides'];
    if (is_array($typeFichier)) {    $extensionsOK = $typeFichier;   }
    elseif (is_string($typeFichier)) {
        if ( array_key_exists($typeFichier, $Extensions_Valides) ) {
            $extensionsOK = $Extensions_Valides[$typeFichier];
        } else {
            $extensionsOK = array($typeFichier);
        }
    }
    $typeFichier = array();
    foreach ($extensionsOK as $extension) {
        if (is_string($extension)) {
            $typeFichier[] = ((substr($extension, 1, 1)!='.') ? '.' : '' ).$extension;
        }
    }
    $retour = FALSE;
    if (count($typeFichier)>0) {
        $retour = in_array('.'.pathinfo($fichier, PATHINFO_EXTENSION), $typeFichier);
    }
    return $retour;
}
function cheminFichier($fichier) {	// Renvoi le chemin du fichier (les caract&egrave;res situ&eacute;s &agrave; gauche du dernier "/", chaine vide si pas de slash...)
    $position = strripos($fichier,'/');
    return ( ($position!==false) ? substr(strtolower($fichier),1,$position) : "" );
}
function nomSeulFichier($fichier) {	// Renvoi le nom du fichier tout seul, aucun dossier (les caract&egrave;res situ&eacute;s &agrave; droite du dernier "/", chaine compl&egrave;te si pas de slash...)
    $position = strripos($fichier,'/');
    return ( ($position!==false) ? substr(strtolower($fichier),$position+1) : $fichier );
}

/**
 * R&eacute;cup&egrave;re un fichier dans les variables $_FILES
 * Retourne le nom du fichier tel qu'on peut le trouver sur le serveur (&agrave; mettre dans une balise <IMG SRC="">)
 * Retourne la valeur par d&eacute;faut s'il n'y a pas de fichier &agrave; charger
 * Retourne un message d'erreur d&eacute;butant par # s'il y a une erreur
 * @param   string  $nomFichier         Nom de la variable contenant le fichier
 * @param   string  $tailleMaximum      =TAILLE_FICHIER_DEFAUT, Taille maximum pour le fichier
 * @param   string  $typeFichier        =FICHIER_IMAGE, Type de fichier, parmi : FICHIER_IMAGE, FICHIER_PDF, FICHIER_TEXTE, FICHIER_WORD,
 *                                                                              FICHIER_EXCEL, FICHIER_POWERPOINT, FICHIER_OPENOFFICE.
 * @param   string  $dossierDestination =DOSSIER_FICHIERS_DEFAUT, Dossier de destination par d&eacute;faut (sera pr&eacute;c&eacute;d&eacute; de $Root automatiquement)
 * @param   string  $defaut             =NULL, Valeur de retour si le fichier n'est pas r&eacute;cup&eacute;r&eacute;
 *
function getFichier($nomFichier, $tailleMaximum=TAILLE_FICHIER_DEFAUT, $typeFichier=FICHIER_IMAGE, $dossierDestination=DOSSIER_FICHIERS_DEFAUT, $defaut=NULL) {
    global $Extensions_Valides;
    if (isset($_FILES[$nomFichier])) { // Le fichier est indiqu&eacute; !
        if ( is_string($tailleMaximum) ) {  // G&egrave;re les multilicateurs de $tailleMaximum (k, m et g)
            $posK = stripos($tailleMaximum,"k");
            $posM = stripos($tailleMaximum,"m");
            $posG = stripos($tailleMaximum,"g");
            $tailleMaximum = (int)$tailleMaximum * ( (max($posG, $posM, $posK)) ? GO : ( (max($posM, $posK)) ? MO : ( ($posK>0) ? KO : 1 ) ) );
        }
        $fichier = strAccents(htmlentities(basename($_FILES[$nomFichier]['name']),ENT_COMPAT,'MacRoman'), ACCENTS_HTML, SANS_ACCENTS);
        $fichierComplet = $dossierDestination.'/'.$fichier;
        if (move_uploaded_file($_FILES[$nomFichier]['tmp_name'], $fichierComplet)) { //Si la fonction renvoie TRUE, c'est que &ccedil;a a fonctionn&eacute;...
            // V&eacute;rifie code d'erreur
            switch ($_FILES[$nomFichier]['error']) {
                case UPLOAD_ERR_OK :	// Pas d'erreur de transfert, faire d'autres contr&ocirc;les !   
                    if ( !array_key_exists($typeFichier, $Extensions_Valides) ) { // Le type de fichier est inconnu
                        $retour = "#Type de fichier inconnu (".$typeFichier.")";
                    } elseif ( !in_array(extensionFichier($fichier), $Extensions_Valides[$typeFichier]) ) { // Mauvaise extension = refus !
                        $retour = "#Extension invalide (".extensionFichier($fichier)." au lieu de ".array2str($Extensions_Valides[$typeFichier]).")";
                    } elseif ( $_FILES[$nomFichier]['size']>$tailleMaximum ) {   // Taille trop importante
                        $retour = "#Taille excessive (".filesize($_FILES[$nomFichier]['tmp_name'])." au lieu de ".$tailleMaximum.")";
                    } else { // Pas d'erreur rencontr&eacute;e !
                        $retour = $fichierComplet;
                    }
                break;
                case UPLOAD_ERR_NO_FILE :
                    $retour = '#Fichier manquant';
                break;
                case UPLOAD_ERR_INI_SIZE : case UPLOAD_ERR_FORM_SIZE :
                    $retour = '#Fichier d&eacute;passant la taille maximum ('.$tailleMaximum.')';
                break;
                case UPLOAD_ERR_PARTIAL :
                    $retour = '#Transfert incomplet';
                break;
                default :
                    $retour = '#Erreur inconnue';
                break;
            }
        } else { //Sinon (la fonction renvoie FALSE).
            $retour = '#Echec de l\'upload !';
        }
    }
    return $retour;
}*/

/**
 *  Cr&eacute;e une miniature de l'image source, retourne le nom de l'image miniature en cas de succ&egrave;s ou FALSE si une erreur survient.
 * @param string    $image_src      Chemin vers l'image source.
 * @param string    $image_dest     Le chemin de destination. S'il n'est pas d&eacute;fini ou s'il vaut NULL,
 *                                  le nom de l'image sera utilis&eacute; en pr&eacute;c&eacute;dant "thumb_xxx_" avant le nom (xxx repr&eacute;sentant la taille maximum indiqu&eacute;e).
 * @param numeric   $max_size       La taille maximale (largeur ou hauteur) de l'image de destination. Ce param&egrave;tre optionnel a pour valeur par d&eacute;faut 100 ;
 * @param boolean   $expand         Si ce param&egrave;tre vaut TRUE, imagethumb() pourra &eacute;ventuellement agrandir l'image pour atteindre la taille max_size ;
 * @param boolean   $square         Si ce param&egrave;tre vaut TRUE, la miniature g&eacute;n&eacute;r&eacute;e sera carr&eacute;e ;
 * @param boolean   $force          Si ce param&egrave;tre vaut TRUE, et que la miniature demand&eacute;e existe d&eacute;j&agrave;, elle sera supprim&eacute;e et recr&eacute;&eacute;e.
 **/
function imageThumb( $image_src , $image_dest = NULL , $max_size = 100, $expand = FALSE, $square = FALSE, $force = FALSE ) {
	if ( !file_exists($image_src) ) return FALSE;   // Le fichier source n'existe pas !
        if ( is_null($image_dest) ) {   // Le nom du fichier de l'image destination n'est pas indiqu&eacute;
            $image_src_decomposee = pathinfo($image_src);
            $image_dest = $image_src_decomposee['dirname'].'/thumb_'.$max_size.'_'.$image_src_decomposee['basename'];
        }
        if (file_exists($image_dest)) {   // Le fichier miniature existe d&eacute;j&agrave;
            if ($force) { unlink($image_dest); }  // On le supprime et on continue
            else { return $image_dest; }  // On le garde et on ne fait rien d'autre !
        }
	// R&eacute;cup&egrave;re les infos de l'image
	$fileinfo = getimagesize($image_src);
	if( !$fileinfo ) return FALSE;
	$width     = $fileinfo[0];
	$height    = $fileinfo[1];
	$type_mime = $fileinfo['mime'];
	$type      = str_replace('image/', '', $type_mime);
	if( !$expand && max($width, $height)<=$max_size && (!$square || ($square && $width==$height) ) ) {	// L'image est plus petite que max_size
		if($image_dest) {
			return copy($image_src, $image_dest);
		} else 	{
			header('Content-Type: '. $type_mime);
			return (boolean) readfile($image_src);
		}
	}
	// Calcule les nouvelles dimensions
	$ratio = $width / $height;
	if( $square ) {
		$new_width = $new_height = $max_size;
		if( $ratio > 1 ) {
			// Paysage
			$src_y = 0;
			$src_x = round( ($width - $height) / 2 );
			$src_w = $src_h = $height;
		} else {
			// Portrait
			$src_x = 0;
			$src_y = round( ($height - $width) / 2 );
			$src_w = $src_h = $width;
		}
	} else {
		$src_x = $src_y = 0;
		$src_w = $width;
		$src_h = $height;
		if ( $ratio > 1 ) {
			// Paysage
			$new_width  = $max_size;
			$new_height = round( $max_size / $ratio );
		} else {
			// Portrait
			$new_height = $max_size;
			$new_width  = round( $max_size * $ratio );
		}
	}
	// Ouvre l'image originale
	$func = 'imagecreatefrom' . $type;
	if( !function_exists($func) ) return FALSE;
	$image_src = $func($image_src);
	$new_image = imagecreatetruecolor($new_width,$new_height);
	if ($type=='png') {	// Gestion de la transparence pour les png
		imagealphablending($new_image,false);
		if (function_exists('imagesavealpha')) imagesavealpha($new_image,true);
	} elseif ( ($type=='gif') && (imagecolortransparent($image_src)>=0) ) {	// Gestion de la transparence pour les gif
		$transparent_index = imagecolortransparent($image_src);
		$transparent_color = imagecolorsforindex($image_src, $transparent_index);
		$transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
		imagefill($new_image, 0, 0, $transparent_index);
		imagecolortransparent($new_image, $transparent_index);
	}
	// Redimensionnement de l'image
	imagecopyresampled($new_image, $image_src, 0, 0, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h);
	// Enregistrement de l'image
	$func = 'image'. $type;
	$func($new_image, $image_dest);
	// Lib&eacute;ration de la m&eacute;moire
	imagedestroy($new_image); 
	return TRUE;
}
?>