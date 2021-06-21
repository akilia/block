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
Le plugin Compositions, permet d'exploiter un tag *configuration* dans le fichier XML.

Ce tag *Configuration* est utilisé ici pour intégrer de nouveaux *Fieldset* dans le formulaire, à la demande.


Ces fieldsets sont déjà prévu

* Fieldset Bouton (Label, URL et class)
* Fieldset Mots clés (liste de mots clés à sélectionner sous forme de bouton radion ou case à cocher)(nécéssite le plugin MotsDF)

**Exemple : ajouter dans le formulaire la saisie de la liste des mots-clés de tel groupe**
Note : voir les fichiers  `content/block-mots.html` et `content/block-mots.xml` dans le plugin.

```
<composition>
	<nom>Block de base + liste de mots</nom>
	<description>Titre, soustitre, texte + choisir parmi la liste des mots du groupe n°1</description>
	<icon>images/objet-test.png</icon>
	<configuration>fieldset_mots:oui/id_groupe:1</configuration>
</composition>
```
Ici, dans le tag `<configuration>`, deux paramètres sont renseignés :
1. `fieldset_mots:oui` : pour cette composition, le formulaire d'édition de bloc va proposer en plus la possibilité de liaison avec un groupe de mot clé.
2. … et le groupe appelé est le 1 (id_groupe = 1)

**Syntaxe complète :**
```
<configuration>parametre:valeur</configuration>
ou encore
<configuration>parametre1:valeur1/parametre2:valeur2</configuration>
```

L'affichage dans la partie publique se fait via une boucle MOTS dans le squelette `content/block-mots.html` bien sûr. 



