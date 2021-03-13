<?php
/**
 * Déclarations relatives à la base de données
 *
 * @plugin     Blocks
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Blocks\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Déclaration des alias de tables et filtres automatiques de champs
 *
 * @pipeline declarer_tables_interfaces
 * @param array $interfaces
 *     Déclarations d'interface pour le compilateur
 * @return array
 *     Déclarations d'interface pour le compilateur
 */
function blocks_declarer_tables_interfaces($interfaces) {

	$interfaces['table_des_tables']['blocks'] = 'blocks';

	$interfaces['table_des_traitements']['BTN_URL'][] = 'calculer_url(%s)';

	return $interfaces;
}


/**
 * Déclaration des objets éditoriaux
 *
 * @pipeline declarer_tables_objets_sql
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function blocks_declarer_tables_objets_sql($tables) {

	$tables['spip_blocks'] = array(
		'type' => 'block',
		'principale' => 'oui',
		'field'=> array(
			'id_block'           => 'bigint(21) NOT NULL',
			'id_parent'          => 'bigint(21) NOT NULL',
			'block_element'      => 'varchar(255) NOT NULL DEFAULT ""',
			'block_id'           => 'varchar(255) NOT NULL DEFAULT ""',
			'block_class'        => 'varchar(255) NOT NULL DEFAULT ""',
			'container_class'    => 'varchar(255) NOT NULL DEFAULT ""',
			'enfants'            => 'varchar(3) NOT NULL DEFAULT ""',
			'titre'              => 'text NOT NULL DEFAULT ""',
			'masquer_titre'      => 'varchar(3) NOT NULL DEFAULT ""',
			'soustitre'          => 'text NOT NULL DEFAULT ""',
			'texte'              => 'text NOT NULL DEFAULT ""',
			'btn_titre'          => 'text NOT NULL DEFAULT ""',
			'btn_url'            => 'text NOT NULL DEFAULT ""',
			'btn_class'          => 'varchar(255) NOT NULL DEFAULT ""',
			'statut'             => 'varchar(20)  DEFAULT "0" NOT NULL',
			'lang'               => 'VARCHAR(10) NOT NULL DEFAULT ""',
			'langue_choisie'     => 'VARCHAR(3) DEFAULT "non"',
			'id_trad'            => 'bigint(21) NOT NULL DEFAULT 0',
			'maj'                => 'TIMESTAMP'
		),
		'key' => array(
			'PRIMARY KEY'        => 'id_block',
			'KEY lang'           => 'lang',
			'KEY id_trad'        => 'id_trad',
			'KEY statut'         => 'statut',
		),
		'titre' => 'titre AS titre, lang AS lang',
		 #'date' => '',
		'champs_editables'  => array('block_element', 'block_id', 'block_class', 'container_class', 'enfants', 'titre', 'masquer_titre', 'soustitre', 'texte', 'btn_titre', 'btn_url', 'btn_class'),
		'champs_versionnes' => array('titre', 'soustitre', 'texte', 'btn_titre'),
		'rechercher_champs' => array("titre" => 8),
		'tables_jointures'  => array('spip_blocks_liens'),
		'statut_textes_instituer' => array(
			'prepa'    => 'texte_statut_en_cours_redaction',
			'publie'   => 'texte_statut_publie',
			'poubelle' => 'texte_statut_poubelle',
		),
		'statut'=> array(
			array(
				'champ'     => 'statut',
				'publie'    => 'publie',
				'previsu'   => 'publie,prepa',
				'post_date' => 'date',
				'exception' => array('statut','tout')
			)
		),
		'texte_changer_statut' => 'block:texte_changer_statut_block',


	);

	return $tables;
}


/**
 * Déclaration des tables secondaires (liaisons)
 *
 * @pipeline declarer_tables_auxiliaires
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function blocks_declarer_tables_auxiliaires($tables) {

	$tables['spip_blocks_liens'] = array(
		'field' => array(
			'id_block'           => 'bigint(21) DEFAULT "0" NOT NULL',
			'id_objet'           => 'bigint(21) DEFAULT "0" NOT NULL',
			'objet'              => 'VARCHAR(25) DEFAULT "" NOT NULL',
			'vu'                 => 'VARCHAR(6) DEFAULT "non" NOT NULL',
			'rang_lien'          => 'bigint(21) DEFAULT "0" NOT NULL',
		),
		'key' => array(
			'PRIMARY KEY'        => 'id_block,id_objet,objet',
			'KEY id_block'       => 'id_block',
			'KEY id_objet'       => 'id_objet',
			'KEY objet'          => 'objet',
		)
	);

	return $tables;
}
