<?php
/**
 * Fichier gérant l'installation et désinstallation du plugin Blocks
 *
 * @plugin     Blocks
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Blocks\Installation
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Fonction d'installation et de mise à jour du plugin Blocks.
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @param string $version_cible
 *     Version du schéma de données dans ce plugin (déclaré dans paquet.xml)
 * @return void
**/
function blocks_upgrade($nom_meta_base_version, $version_cible) {
	$maj = array();

	$maj['create'] = array(array('maj_tables', array('spip_blocks', 'spip_blocks_liens')));

	$maj['1.0.1'] = array(
		array('sql_alter',"TABLE spip_blocks ADD  `bouton_class` VARCHAR(255) NOT NULL DEFAULT '' AFTER bouton_lien"),
	);

	/* nom des champs 'bouton' deviennet 'btn' :  plus international */
	$maj['1.0.2'] = array(
		array('sql_alter',"TABLE spip_blocks CHANGE  `bouton_titre` `btn_titre` text NOT NULL DEFAULT ''"),
		array('sql_alter',"TABLE spip_blocks CHANGE  `bouton_lien` `btn_lien` text NOT NULL DEFAULT ''"),
		array('sql_alter',"TABLE spip_blocks CHANGE  `bouton_class` `btn_class` varchar(255) NOT NULL DEFAULT ''"),
	);

	/* toujours plus international */
	$maj['1.0.3'] = array(
		array('sql_alter',"TABLE spip_blocks CHANGE  `btn_lien` `btn_url` text NOT NULL DEFAULT ''"),
	);

	/* Initialisation du plugin : pourvoir ajouter des blocks au minimum sur des rubriques */
	$maj['1.0.4'] = array(
		array('blocks_init_metas')
	);

	

	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}


/**
 * Depuis la possibilité de gérer les objets sur lesqueles activer Block
 * initialisation du plusgin pour gérer au moins les rubriques
 *
**/
function blocks_init_metas() {
	ecrire_config("blocks/objets", array('spip_rubriques'));
}


/**
 * Fonction de désinstallation du plugin Blocks.
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @return void
**/
function blocks_vider_tables($nom_meta_base_version) {

	sql_drop_table('spip_blocks');
	sql_drop_table('spip_blocks_liens');

	# Nettoyer les liens courants (le génie optimiser_base_disparus se chargera de nettoyer toutes les tables de liens)
	sql_delete('spip_documents_liens', sql_in('objet', array('block')));
	sql_delete('spip_mots_liens', sql_in('objet', array('block')));
	sql_delete('spip_auteurs_liens', sql_in('objet', array('block')));
	# Nettoyer les versionnages et forums
	sql_delete('spip_versions', sql_in('objet', array('block')));
	sql_delete('spip_versions_fragments', sql_in('objet', array('block')));
	sql_delete('spip_forum', sql_in('objet', array('block')));

	effacer_meta($nom_meta_base_version);
}
