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

	// sur quels objets activer les blocks
	include_spip('base/objets');
	$tables = array_filter(lire_config('blocks/objets'));
	$objets = array_map('objet_type', $tables);

	if (!$e['edition'] and $objets and in_array($e['type'], $objets)) {

		// si c'est un bloc et qu'il n'est pas déclaré comme pouvant avoir des sous-blocks, on sort
		if ($e['type'] == 'block') {
			$enfants = sql_getfetsel('enfants', 'spip_blocks', 'id_block='.intval($flux['args']['id_block']));
			if ($enfants != 'on') {
				return $flux;
			}
		}

		// si c'est une rubique et qu'il n'est pas autorise de publier dans une rubrique, on sort
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
 * Par défaut, à la création d'un block, celui-ci à le statut 'publie'
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
    if (isset($flux['args']['table']) 
    	and $flux['args']['table'] == 'spip_blocks'
    	and $flux['args']['action'] =='instituer') {

    	$id_objet = $flux['args']['id_objet'];
        $id_rubrique = sql_getfetsel('id_objet', 'spip_blocks_liens', 'id_block='.intval($id_objet).' AND objet='.sql_quote('rubrique'));
        
        if ($id_rubrique) {
        	$modifs['statut'] = 'publie';
        	$statut_ancien_rubrique = sql_getfetsel('statut', 'spip_rubriques', 'id_rubrique='.intval($id_rubrique));
        	include_spip('inc/rubriques');
        	$res = calculer_rubriques_if($id_rubrique, $modifs, $statut_ancien_rubrique);
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
	// $heritages['block'] = 'block';
	return $heritages;
}

/**
 * Compatibilité plugin LIM
 * Pour la gestion des contenus par rubrique, pouvoir intégrer les blocks.
 * En effet Blocks n'ayant pas de champs 'id_rubrique', il est automatiquement exclus. On l'enlève de la liste des exclus.
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


/**
 * Compatibilité plugin MOTSDF
 * Afficher un groupe de mots-clef dans un block si la partie .xml du Block contient la déclaration de l'id_groupe voulu
 *
 * @pipeline motsdf_activer_objet
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
**/
function blocks_motsdf_activer_objet($flux) {
	$exec =  _request('exec');
	$objets = $flux['args']['objets'];

	if ($objets == 'blocks' AND $exec == 'block_edit') {
		$id_block = _request('id_block');
		$compo = compositions_determiner('block', $id_block);
		$config_compo = composition_configuration($compo, 'block');
		if (isset($config_compo['fieldset_mots']) and $config_compo['fieldset_mots'] == 'oui' and isset($config_compo['id_groupe'])) {
			$id_groupe = $config_compo['id_groupe'];
			$select = $flux['args']['select'];
			$flux['data'] = sql_allfetsel($select, 'spip_groupes_mots', 'id_groupe='.intval($id_groupe));
		}
		debug($flux);
	}

	return $flux;
}
