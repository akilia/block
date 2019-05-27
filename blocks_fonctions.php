<?php
/**
 * Fonctions utiles au plugin Blocks
 *
 * @plugin     Blocks
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Blocks\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Traitement du paramètre "configuration" qui est déjà présent dans Composition mais inutilisé
 * Ici, on l'utilise.
 * Les paramètres transmis servent à cacher ou non des éléments du formulaire inutiles dans telle composition
 *
 *
 * @param string $composition 
 * @param string $type 
 *
 * @return array $tab_config : le tableau des confs spéciales de la composition
 */
function composition_configuration($composition, $type) {
	if ($desc = compositions_decrire($type, $composition)
		and isset($desc['configuration'])
		and strlen($desc['configuration'])) {

		$config_type = explode('/', $desc['configuration']);
		foreach ($config_type as $ss_conf) {
			$key = strstr($ss_conf, ':', true);
			$valeur = substr(strstr($ss_conf, ':'), 1);
			$tab_config[$key] = $valeur;
		}

		return $tab_config;
	}
	return $composition;
}

/**
 * Récupérer les paramètres à transmettre au formulaire d'édition de block
 *
 * @param string $desc avec un format "rubrique|7" par ex.
 * @param string $info laquelle des deux infos  à retourner 
 *
 * @return string
 */

function block_infos_parent($desc, $info) {
	list($parent, $id_parent) = explode('|', $desc);

	if ($info == 'parent') {
		return $parent;
	} else {
		return $id_parent;
	}
}


/**
 * Lister les ids objet associés à l'objet id_objet
 * via la table de lien objet_lien
 *
 *
 * @param string $objet_source
 * @param string $objet
 * @param int $id_objet
 * @param string $objet_lien
 * @return array
 */
function lister_blocks_lies($objet_source, $objet, $id_objet, $objet_lien) {
	include_spip('action/editer_liens');
	$l = array();
	$id_table_objet = id_table_objet($objet_lien);

	// quand $objet == $objet_lien == $objet_source on reste sur le cas par defaut de $objet_lien == $objet_source
	if ($objet_lien == $objet and $objet_lien !== $objet_source) {
		$res = objet_trouver_liens(array($objet => $id_objet), array($objet_source => '*'));
	} else {
		$res = objet_trouver_liens(array($objet_source => '*'), array($objet => $id_objet));
	}
	while ($row = array_shift($res)) {
		$l[] = $row[$id_table_objet];
	}

	return $l;
}

/**
 * Compter les objets associés à l'objet id_objet
 * 
 * @api
 * @param int $id_objet
 * @return int|bool
 */
function lien_compter($id_objet) {
	$res = sql_countsel('spip_blocks_liens', "objet='block' AND id_objet=".intval($id_objet));
	if ($res == 0) {
		$res = false;
	}

	return $res;
}
