# loupiots

"Les Loupiots" est une garderie peri-scolaire associative. 

Ce software permet la creation du site permettant aux parents d'inscrire leurs enfants aux differents crenaux de la journee.  
Le cout est mis a jour en direct et les parents peuvent enregistrer leurs paiments.  
L'administrateur peut suivre l'evolution des inscription, valider la reception des paiment et gerer le caladrier.  
Eventuelement, l'administrateur a egalement la possibilite de rajouter des crenaux pour les retards ou les imprevus.

L'animateur a également des droits étendus pour les paiements et les inscriptions. Il peut surtout imprimer les feuilles d'appel permettant de gérer les enfants à la sortie de l'école.


## Paiement

Le paiement apparait sur chaque page du calendrier. Dans la section Réglement de la page.  
Il fait face à la section Facture du mois précédent, donc logiquement, les paiement sont par défaut affecté au mois précédent.  
Le calcul de la facture est donc décalée d'un mois. C'est à dire que sur la page du mois *Novembre*, apparait la facture pour le mois *d'Octobre*.

S'il n'y a pas de paiement tous les mois, les réservations sont reportées sur le mois suivant dans la rubrique du *restant du*.

### Cycle de vie d'un paiement
1. **En attente de réception** Après cération, statut par défaut.
2. **Recu** En cas de paiement par chèque ou espece, l'animateur (ou l'administrateur) qui recoit physiquement le paiement change acquite la reception.
3. **Validé** Le comptable (administrateur) valide le paiement (paiement physique conforme à la déclaration).
4. **Annulé** Le comptable (administrateur) annule le paiement si il n'y a pas eu de paiement physique corespondant.
5. **Comptabilisé** Le 6 de chaque mois, le système comptabilise automatiquement les paiements validés. Ils servent donc au calcul du restant du.

### Calcul du montant du
Le montant du pour le mois ets la somme du  
- Restant du du mois précedent  
- La somme de toutes les réservations du mois facturé (standard + déplacement)  
C'est pour tenir compte des dépacements que les factures sont éditées sur le mois précédent et non sur le mois courant.

### Calcul du restant du
Le restant du du mois précédent est calculé le 6 de chaque mois.
Il prend en compte tous les paiements **validés** au cours du mois quelque soit la date de création du paiement ou le mois payé.  
Ce restant du sert alors de base pour établir la facture du mois suivant.  

### Ajout/modification de paiement
- **Par l'utilisateur:**  
	Dans la page de réservation du mois en cours, clicker sur *ajouter paiement*
	Le paiement est alors par défaut daté du mois précedent ie celui corespondant à la facture affiché dans la page.
	L'utilisateur a la possibilité de dater la facture au mois courant.
- **Par L'animateur:**  
	L'animateur doit cocher que le paiement est recu.
	- A partir de la page d'un mois passé ou il y a deja un paiement, clicker sur *modifier* ou *ajouter paiement*
	- A partir de  la page Administration -> Facturation, choisir le bon mois, puis la famille et clicker sur *modifier* ou *ajouter paiement*
	L'animateur peut aussi créer un nouveau paiement
- **Par l'administrateur:**  
	L'administrateur doit valider un paiement.	
	Les acces sont similaires à ceux de l'animateur.  
La modification d'un paiement permet de changer le montant, le type de paiement et le statut.

	




	