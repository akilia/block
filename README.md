# block
Objet éditorial souple et générique lié à une composition HTML et CSS

## Prérequis
* **SPIP 3.3**
* Saisies pour formulaires ≥ 3.18.10,
* Compositions
* Rang sur les auteurs ≥ 1.0.0,
* CVT Upload ≥ 1.16.0, 
* Rôles de documents ≥ 1.2.23

Une feuille de style pour la gestion des CSS.

## Configuration
Vous devez aller dans la page de configuration du plugin Compositions et activer l'utilisation des compositions sur les blocs.

## Les principes qui ont guidés la conception de ce plugin
L'idée est venue lors de la réalisation de plusieurs sites avec Jordan : on se retrouvait souvent avec une page d'accueil composée de sections courtes, elles-mêmes construite en général avec juste un titre, un texte et un lien vers une autre page (un bouton).

Ces sections avaient toutes un look différent.

### Mise en place
La première  chose était d'offrir aux rédacteurs du site la possibilité de modifier/corriger le texte d'une section, mais également de supprimer/désactiver/déplacer une section.

Dans un deuxième temps, il falait proposer un système au créateur du site ainsi qu'aux futurs rédacteurs la possibilité de créer de nouvelles sections. Là, la tâche est plus ardue puisque :

* les sections ayant chacune un look différent, il faut pouvoir gérer simplement le markup HTML (tags + classes CSS) de celles-ci.
* il faut pouvoir réeordonner un groupe de blocs.
* il faut pouvoir réutiliser un même bloc (look + contenu ou juste look)

Pour cela, trois solutions ont été envisagés :
1. créer un bloc ad-hoc depuis l'interface : il faut alors être Webmestre (statut spécial SPIP) et avoir accès aux styles du site pour connaître les classes qui seront utiles.
2. compositions de blocs prêtent à l'emploi : le créateur du site enregistre des compositions spécifiques au site (markup HTML). Il suffit au rédacteur de choisir la composition dans l'interface puis de renseigner le contenu.
3. réutilisation d'un bloc : depuis l'interface, le rdacteur peut choisir un bloc existant. 

## les compositions
### Les compositions de base
Le plugin propose deux compositions de base :

* block : titre, soustitre, texte
* block_bouton : la même chose + trois champs pour définir un bouton :  intitulé, URL, classe CSS

### Créer une nouvelle composition
Voir la doc à ce propos : https://contrib.spip.net/Compositions-2-et-3
Pour l'instant, le principe de l'héritage ne marche pas totalement. En berne donc.

### Nouvelle possibilité sur les compositions
En grattant un peu le code du plugin Compositions, je me suis aperçu qu'il était possible d'exploiter le tag *configuration* dans le fichier XML de la composition.

Je m'en sert pour personnaliser le formulaire "Block" du rédacteur.

Ainsi pour une composition en particulier, il est possible de faire apparaître de nouveaux *Fieldset*.
Ces fieldsets sont déjà là, mais désactivés par défaut.


* Fieldset Bouton 
* Fieldset Mots clés (nécéssite le plugin MotsDF)

**Exemple :**
Préambule : l'exemple ci-dessous nécessite de d'avoir chargé le plugin Motsdf : Mots dans Formulaire.
Son utilisation est prévue dans le plugin **Block**.

```
<composition>
	<nom>Block Image + liste de mots</nom>
	<description>Composition Meric avec image et choix d'une liste de services</description>
	<icon>images/objet-cours.png</icon>
	<configuration>fieldset_mots:oui/id_groupe:1</configuration>
</composition>
```
Dans le tag `<configuration>`, je renseigne deux instructions :
1. `fieldset_mots:oui` : pour cette composition, le formulaire d'édition de bloc va proposer en plus la possibilité de liaison avec un groupe de mot clé.
2. … et le groupe appelé est le 1 (id_groupe = 1)

La syntaxe est donc la suivante :
```
<configuration>parametre:valeur</configuration>
ou encore
<configuration>parametre1:valeur1/parametre2:valeur2</configuration>
```



