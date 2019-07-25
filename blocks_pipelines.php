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

		// si c'est une rubique et qu'il n'est pas autorise de publier dans une rurbique, on sot
		if ($e['type'] == 'rubrique' and !autoriser('creerblockdans', 'rubrique', $flux['args']['id_rubrique'])) {
			return $flux;
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
 * Par défaut, à la création d'un block, clui-ci à le statut 'publie'
 *
 * @pipeline pre_insertion
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function blocks_pre_insertion($flux) {
    if ($flux['args']['table'] == 'spip_blocks') {
        $flux['data']['statut'] = 'publie';
    }
    return $flux;
}

/**
 * Gestion du statut de la rubrique parente, si le parent est bien une rubrique of course
 *
 * @pipeline pre_edition
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function blocks_post_edition($flux) {
    if (isset($flux['args']['table']) and $flux['args']['table'] == 'spip_blocks' and $flux['args']['action'] =='instituer') {
    	$id_objet = $flux['args']['id_objet'];
        $parent = sql_getfetsel('objet', 'spip_blocks_liens', 'id_block='.intval($id_objet));
        
        if ($parent == 'rubrique') {
        	$id_rubrique = sql_getfetsel('id_objet', 'spip_blocks_liens', 'id_block='.intval($id_objet));
        	$modifs['statut'] = $flux['data']['statut'];
        	$statut_ancien_rubrique = sql_getfetsel('statut', 'spip_rubriques', 'id_rubrique='.intval($id_rubrique));
        	calculer_rubriques_if($id_rubrique, $modifs, $statut_ancien_rubrique);
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
 * Compter les enfants d'un objet
 *
 * @pipeline objets_compte_enfants
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
**/
function blocks_objet_compte_enfants($flux) {
	if ($flux['args']['objet'] == 'rubrique' and $id_rubrique = intval($flux['args']['id_objet'])) {
		// juste les publiés ?
		if (array_key_exists('statut', $flux['args']) and ($flux['args']['statut'] == 'publie')) {
			$flux['data']['blocks'] = sql_countsel('spip_blocks AS B JOIN spip_blocks_liens AS L ON B.id_block=L.id_block', 'objet='.sql_quote('rubrique').' AND id_objet= ' . intval($id_rubrique) . " AND (B.statut = 'publie')");
		} else {
			$flux['data']['blocks'] = sql_countsel('spip_blocks AS B JOIN spip_blocks_liens AS L ON B.id_block=L.id_block', 'objet='.sql_quote('rubrique').' AND id_objet= ' . intval($id_rubrique) . " AND (B.statut = 'publie')");
		}
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

/**
 * plugin LIM
 * Pour la gestion des contenus par rubrique, pouvoir intégrer les blocks
 * objet Blocks n'ayant pas de champs 'id_rubrique', il est automatiquement mis dans la liste des objets exclus. On le retire de la liste.
 * pour des objets avec liaisons on utilise l'autorisation 'associerblocks'
 *
 * @pipeline lim_declare_exclus
 * @param  string $flux Données du pipeline
 * @return string       Données du pipeline
**/
function blocks_lim_declare_exclus($flux) {
	$key = array_search('spip_blocks', $flux);
	unset($flux[$key]);
	
	return $flux;
}
