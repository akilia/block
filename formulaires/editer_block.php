<?php
/**
 * Gestion du formulaire de d'édition de block
 *
 * @plugin     Blocks
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Blocks\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/actions');
include_spip('inc/editer');

/* Gestion des fichiers à uploader  (plugin CVT upload) */
function formulaires_editer_block_fichiers() {
	return array('logo');
}


/**
 * Identifier le formulaire en faisant abstraction des paramètres qui ne représentent pas l'objet edité
 *
 * @param int|string $id_block
 *     Identifiant du block. 'new' pour un nouveau block.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param string $associer_objet
 *     Éventuel `objet|x` indiquant de lier le block créé à cet objet,
 *     tel que `article|3`
 * @param int $lier_trad
 *     Identifiant éventuel d'un block source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du block, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return string
 *     Hash du formulaire
 */
function formulaires_editer_block_identifier_dist($id_block = 'new', $retour = '', $associer_objet = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	return serialize(array(intval($id_block), $associer_objet));
}

/**
 * Chargement du formulaire d'édition de block
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @uses formulaires_editer_objet_charger()
 *
 * @param int|string $id_block
 *     Identifiant du block. 'new' pour un nouveau block.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param string $associer_objet
 *     Éventuel `objet|x` indiquant de lier le block créé à cet objet,
 *     tel que `article|3`
 * @param int $lier_trad
 *     Identifiant éventuel d'un block source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du block, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Environnement du formulaire
 */
function formulaires_editer_block_charger_dist($id_block = 'new', $retour = '', $associer_objet = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$valeurs = formulaires_editer_objet_charger('block', $id_block, '', $lier_trad, $retour, $config_fonc, $row, $hidden);

	// Forcer ici la valeur par défaut du type de contenu (DIV, SECTION, etc.)
	// en attendant la résolution de https://contrib.spip.net/Reference-des-saisies#comment500006
	if (!is_numeric($id_block)) {
		$valeurs['block_element'] = 'div';
	}

	// Récupérer la compo : permete ensuite de lire le tag  xml 'configuration' pour savoir si il y a des champs à masquer
	$compo = compositions_determiner('block', $id_block);
	$valeurs['compo'] = $compo;

	return $valeurs;
}

/**
 * Vérifications du formulaire d'édition de block
 *
 * Vérifier les champs postés et signaler d'éventuelles erreurs
 *
 * @uses formulaires_editer_objet_verifier()
 *
 * @param int|string $id_block
 *     Identifiant du block. 'new' pour un nouveau block.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param string $associer_objet
 *     Éventuel `objet|x` indiquant de lier le block créé à cet objet,
 *     tel que `article|3`
 * @param int $lier_trad
 *     Identifiant éventuel d'un block source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du block, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Tableau des erreurs
 */
function formulaires_editer_block_verifier_dist($id_block = 'new', $retour = '', $associer_objet = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$erreurs = array();

	$erreurs = formulaires_editer_objet_verifier('block', $id_block, array('titre'));

	return $erreurs;
}

/**
 * Traitement du formulaire d'édition de block
 *
 * Traiter les champs postés
 *
 * @uses formulaires_editer_objet_traiter()
 *
 * @param int|string $id_block
 *     Identifiant du block. 'new' pour un nouveau block.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param string $associer_objet
 *     Éventuel `objet|x` indiquant de lier le block créé à cet objet,
 *     tel que `article|3`
 * @param int $lier_trad
 *     Identifiant éventuel d'un block source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du block, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Retours des traitements
 */
function formulaires_editer_block_traiter_dist($id_block = 'new', $retour = '', $associer_objet = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	
	// Nardin de checkbox html !!!
	count(_request('masquer_titre')) > 0 ? set_request('masquer_titre','oui') : set_request('masquer_titre','non');

	$retours = formulaires_editer_objet_traiter('block', $id_block, '', $lier_trad, $retour, $config_fonc, $row, $hidden);

	$id_block = $retours['id_block'];

	// Un lien a prendre en compte ?
	if ($associer_objet and $id_block = $retours['id_block']) {
		list($objet, $id_objet) = explode('|', $associer_objet);

		if ($objet and $id_objet and autoriser('modifier', $objet, $id_objet)) {
			include_spip('action/editer_liens');
			
			objet_associer(array('block' => $id_block), array($objet => $id_objet));

			// Compaibilité avec le plugin Composition : ’nourrir’ le champ id_parent
			sql_updateq('spip_blocks', array('id_parent' => $id_objet), 'id_block='.intval($id_block));
			
			if (isset($retours['redirect'])) {
				$retours['redirect'] = parametre_url($retours['redirect'], 'id_lien_ajoute', $id_block, '&');
			}
		}
	}

	// Traitement des upload de fichier : récupérer le tableau des données correspondants aux fichiers uploadés ou non
	$fichiers = _request('_fichiers');

	if (is_array($fichiers) AND count($fichiers)) {

		// charger la fonction de chargement de document du plugin Medias
		$ajouter_documents = charger_fonction('ajouter_documents', 'action');
		foreach ($fichiers as $key => $value) {
			$fichiers[$key][0]['mode'] = 'logoon';
			$id_doc = $ajouter_documents('new', $fichiers[$key], 'block', $id_block, 'auto');
		}
	}

	return $retours;
}
