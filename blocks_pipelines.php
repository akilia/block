<?php
/**
 * Utilisations de pipelines par Blocks
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
 * Ajout de contenu sur certaines pages,
 * notamment des formulaires de liaisons entre objets
 *
 * @pipeline affiche_milieu
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function blocks_affiche_milieu($flux) {
	$texte = '';
	$e = trouver_objet_exec($flux['args']['exec']);
	// debug($e);
	// debug($flux['args']);

	// blocks sur les rubriques et blocs
	if (!$e['edition'] and in_array($e['type'], array('rubrique', 'block'))) {

		// si c'est un bloc et qu'il n'est pas déclaré comme pouvant avoir des sous-blocks, on sort
		if ($e['type'] == 'block') {
			$enfants = sql_getfetsel('enfants', 'spip_blocks', 'id_block='.intval($flux['args']['id_block']));
			if ($enfants != 'on') {
				return $flux;
			}
		}

		$texte .= recuperer_fond('prive/objets/editer/liens', array(
			'table_source' => 'blocks',
			'objet' => $e['type'],
			'id_objet' => $flux['args'][$e['id_table_objet']]
		));
	}

	if ($texte) {
		if ($p = strpos($flux['data'], '<!--affiche_milieu-->')) {
			$flux['data'] = substr_replace($flux['data'], $texte, $p, 0);
		} else {
			$flux['data'] .= $texte;
		}
	}

	return $flux;
}


/**
 * Ajout de liste sur la vue d'un auteur
 *
 * @pipeline affiche_auteurs_interventions
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function blocks_affiche_auteurs_interventions($flux) {
	if ($id_auteur = intval($flux['args']['id_auteur'])) {
		$flux['data'] .= recuperer_fond('prive/objets/liste/blocks', array(
			'id_auteur' => $id_auteur,
			'titre' => _T('block:info_blocks_auteur')
		), array('ajax' => true));
	}
	return $flux;
}

/**
 * Optimiser la base de données
 *
 * Supprime les liens orphelins de l'objet vers quelqu'un et de quelqu'un vers l'objet.
 * Supprime les objets à la poubelle.
 *
 * @pipeline optimiser_base_disparus
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function blocks_optimiser_base_disparus($flux) {

	include_spip('action/editer_liens');
	$flux['data'] += objet_optimiser_liens(array('block'=>'*'), '*');

	sql_delete('spip_blocks', "statut='poubelle' AND maj < " . $flux['args']['date']);

	return $flux;
}


/**
 * plugin Compositions
 * déclaration de l'héritage d'un block sur un autre block
 *
 * @pipeline compositions_declarer_heritage
 * @param  string $heritages Données du pipeline
 * @return string       Données du pipeline
**/
function blocks_compositions_declarer_heritage($heritages) {
	$heritages['block'] = 'block';
	return $heritages;
}
