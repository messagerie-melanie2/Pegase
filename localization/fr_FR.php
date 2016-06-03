<?php
/**
 * Ce fichier fait parti de l'application de sondage du MEDDE/METL
 * Cette application est un doodle-like permettant aux utilisateurs
 * d'effectuer des sondages sur des dates ou bien d'autres criteres
 *
 * L'application est écrite en PHP5,HTML et Javascript
 * et utilise une base de données postgresql et un annuaire LDAP pour l'authentification
 *
 * @author Thomas Payen
 * @author PNE Annuaire et Messagerie
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Localisation FR
 */
$labels = [];
$labels['Application name'] = 'Pégase';
$labels['Poll'] = 'Sondage';
// Labels Title
$labels['title main'] = 'Accueil';
$labels['title create'] = 'Création nouveau sondage';
$labels['title login'] = 'Connexion à l\'application';
$labels['title edit'] = 'Edition d\'un sondage';
$labels['title show'] = 'Affichage du sondage';
$labels['title edit_end'] = 'Sondage créé';
$labels['title edit_prop'] = 'Edition des propositions';
$labels['title edit_date'] = 'Edition des dates';
// Main Page
$labels['Welcome to doodle of the MEDDE'] = 'Bienvenue sur le service de création de sondage';
$labels['Create new poll'] = 'Création d\'un nouveau sondage';
$labels['New poll'] = 'Nouveau sondage';
$labels['List of your polls'] = 'Vos sondages';
$labels['Your X last polls'] = "Vos %%max_own_polls%% derniers sondages";
$labels['All your polls'] = "Tous vos sondages";
$labels['Last X polls that you have responded'] = "Les %%max_resp_polls%% derniers sondages auxquels vous avez répondu";
$labels['All your responded polls'] = "Tous les sondages auxquels vous avez répondu";
$labels['Show more...'] = "Afficher plus...";
$labels['Hide polls'] = "Masques les sondages";
$labels['List of polls you have responded'] = 'Les sondages auxquels vous participez';
$labels['Clic to view the poll'] = 'Cliquez ici pour voir le sondage';
$labels['Clic to view the poll (Number of responses)'] = 'Cliquez ici pour voir le sondage (Nombre de reponses au sondage)';
$labels['Clic to edit the poll'] = "Cliquez ici pour éditer le sondage";
$labels['Clic to delete the poll'] = "Cliquez ici pour supprimer le sondage";
$labels['Clic to lock the poll'] = "Cliquez ici pour verrouiller ou déverrouiller le sondage";
$labels['Clic to change everybody responses'] = "Cliquez ici pour modifier les réponses de tous les participants au sondage";
$labels['Clic to change poll proposals'] = "Cliquez ici pour modifier les propositions du sondage";
$labels['Clic to get back to the poll modification'] = "Cliquez ici pour retourner sur l'édition des informations du sondage";
$labels['Clic to save the proposals of the poll'] = "Cliquez ici pour enregistrer les propositions du sondage";
$labels['Clic to save your responses'] = "Cliquez ici pour enregistrer votre réponse au sondage";
$labels['Clic to add a new prop'] = 'Cliquez ici pour ajouter une nouvelle proposition au sondage';
$labels['No poll'] = "Pas de sondage";
$labels['List poll'] = "Lister les sondages";
$labels['Notify attendees'] = "Notifier les participants";
$labels['Help'] = "Aide";
$labels['View help of the page'] = "Afficher l'aide de la page";
// Create Page
$labels['Return to the index'] = 'Page principale';
$labels['Create poll page'] = 'Création du sondage';
$labels['Modification poll page'] = 'Modification des informations du sondage';
$labels['Modification poll page, change the dates'] = 'Modification des dates du sondage';
$labels['Create poll page, modify the dates'] = 'Création du sondage, choisissez les dates';
$labels['Modification poll page, change your own dates'] = 'Modification de vos propositions pour le sondage';
$labels['Create poll page, modify your own propositions'] = 'Création du sondage, choisissez vos propositions';
// Login Page
$labels['Log-in to create new poll and list all your polls'] = 'Connectez-vous avec votre compte pour lister vos sondages ou en créer de nouveaux';
$labels['Username'] = 'Identifiant utilisateur';
$labels['Password'] = 'Mot de passe de l\'utilisateur';
$labels['You have to put your username'] = 'Vour devez saisir votre identifiant utilisateur pour vous connecter';
$labels['You have to put your password'] = 'Vous devez saisir votre mot de passe pour vous connecter';
$labels['Connect'] = 'Se connecter à l\'application';
$labels['Connected as'] = 'Connecté en tant que';
$labels['Disconnect'] = 'Se déconnecter';
$labels['Disconnect from the app'] = 'Se déconnecter de l\'application';
$labels['Create a new poll'] = 'Cliquez ici pour créer un nouveau sondage';
$labels['Go back to the main page'] = 'Retourner à la page d\'accueil de l\'application';
$labels['Go back to poll list'] = 'Retourner à la liste des sondages';
$labels['You are disconnected from the app'] = "Vous êtes déconnecté de l'application de sondage";
$labels['Please close the tab and reopen the tab'] = "Vous pouvez fermer puis ré-ouvrir l'onglet";
$labels['Clic here to reconnect'] = "Cliquez-ici pour vous reconnecter";
$labels['Or clic here to reconnect'] = "Ou bien cliquez ici pour vous reconnecter";
// Error Page
$labels['Error page'] = 'Page d\'erreur';
// Errors
$labels['Auth error, bad login or password'] = 'Erreur d\'authentification, nom d\'utilisateur ou mot de passe incorrect.';
$labels['Auth error, please re-login'] = 'Une erreur d\'authentification s\'est produite, veuillez vous reconnecter.';
$labels['The resource does not exist'] = 'La ressource demandée n\'existe pas';
$labels['You have no right to access to this resource'] = 'Vous n\'avez pas les droits pour accéder à cette ressource';
$labels['Error while deleting the poll'] = 'Une erreur s\'est produite pendant la suppression du sondage.';
$labels['Invalid request'] = 'La requête n\'est pas valide. Si l\'erreur persiste, essayez de vous reconnecter.';
$labels['Only auth users can respond to this poll'] = 'Seul les utilisateurs authentifiés peuvent répondre à ce sondage';
$labels['Error while generating the ICS file'] = "Une erreur s'est produite lors de la génération de l'ICS";
$labels['Error while saving the event in your calendar'] = "Une erreur s'est produite lors de la création de l'évènement dans votre calendrier";
// Messages
$labels['Poll has been deleted'] = 'Le sondage vient d\'être supprimé';
// Edit poll
$labels['Created by'] = 'Créé par';
$labels['Created time'] = '';
$labels['Last modification time'] = 'Dernière modification';
$labels['Edit title'] = 'Titre';
$labels['Edit location'] = 'Emplacement';
$labels['Edit description'] = 'Description';
$labels['Edit Poll type'] = 'Type de sondage';
$labels['Poll for only auth user'] = 'Sondage réservé aux utilisateurs authentifiés';
$labels['poll_type_date'] = 'Sondage de date';
$labels['poll_type_prop'] = 'Sondage libre';
$labels['Save and choose propositions'] = 'Enregistrer le sondage et choisir les propositions';
$labels['Save and modify propositions'] = 'Enregistrer le sondage et modifier les propositions';
$labels['Edit proposition'] = 'Saisissez une proposition';
$labels['Edit date'] = 'Saisissez une date';
$labels['Edit date (Y-m-d H:i:s)'] = 'Choisissez une date';
$labels['Save the poll'] = 'Enregistrer les propositions du sondage';
$labels['Return to the edit page of poll'] = 'Retourner à l\'édition du sondage';
$labels['Congratulation, your poll is now created'] = 'Félicitations, votre sondage a correctement été enregistré';
$labels['You can now share this url with your friend'] = 'Vous pouvez désormais partager l\'url ci-dessous avec vos contacts : ';
$labels['You can modify the poll by clicking '] = 'Vous pouvez modifier le sondage en cliquant ';
$labels['You can see the poll by clicking '] = 'Vous pouvez voir le sondage en cliquant ';
$labels['Attention, you have not respond to the poll '] = 'Attention, vous n\'avez pas encore répondu au sondage ';
$labels['clic here to respond'] = 'cliquez ici pour répondre';
$labels['You have to put a title for the poll'] = 'Vous devez saisir un titre pour le sondage';
$labels['here'] = 'ici';
$labels['See the poll'] = 'Voir le sondage';
$labels['Current poll is not defined'] = 'Le sondage n\'existe pas';
$labels['Poll is now unlocked'] = 'Le sondage est maintenant déverrouillé';
$labels['Poll is now locked'] = 'Le sondage est maintenant verrouillé';
$labels['Poll is locked, you can not respond'] = 'Le sondage est verrouillé, vous ne pouvez plus répondre ou modifier votre choix';
$labels['The poll is modified'] = 'Le sondage a correctement été modifié';
$labels['Add'] = 'Ajouter';
$labels['Use your own propositions'] = 'Choisissez vos propres propositions';
$labels['Use propositions dates'] = 'Proposer des dates';
$labels['Poll name'] = 'Titre du sondage';
$labels['Modify poll'] = 'Modifier le sondage';
$labels['Delete poll'] = 'Supprimer le sondage';
$labels['Locked'] = 'Verrouillé';
$labels['Advanced options'] = 'Options avancées';
$labels['Select dates by clicking in the calendar'] = 'Sélectionnez des dates en cliquant dans le calendrier ci-dessous. Vous pouvez les créer sur plusieurs jours en vue Mois ou sur une plage horaire en vue Semaine/Jour. Chaque date peut être déplacée en glissant/déposant et supprimée en cliquant sur la croix blanche. Cliquez sur "Afficher votre agenda" pour visualiser votre agenda personnel.';
$labels['Selected date list'] = 'Liste des dates sélectionnées dans l\'agenda';
$labels['Warning: If you change poll type, proposals previously add (date or free) will be lost'] = 'Attention : si vous changez le type de sondage, les propositions déjà saisies (dates ou libres) seront perdues.';
$labels['Save the poll informations and modify proposals'] = 'Cliquez ici pour enregistrer les informations du sondage et passer à la modification des propositions';
$labels['Title of the poll'] = 'Saisissez le titre du sondage';
$labels['Type of the poll'] = 'Sélectionnez le type de sondage dans la liste';
$labels['Location of the poll'] = 'Saisissez l\'emplacement de la réunion';
$labels['Description of the poll'] = 'Saisissez la description du sondage';
$labels['This poll is only open for auth users'] = 'Cochez cette case pour que seuls les utilisateurs authentifiés puissent répondre au sondage';
$labels['select poll type date'] = 'Sondage de dates, les dates seront à sélectionner dans un calendrier';
$labels['select poll type prop'] = 'Sondage libre, choisissez vous même les propositions du sondage';
$labels['hide calendar'] = 'Masquer votre agenda';
$labels['show calendar'] = 'Afficher votre agenda';
$labels['This poll allows users to use the if needed answer'] = "Cochez cette case pour permettre aux utilisateurs la possibilité d'une troisième réponse : 'Possible'";
$labels['Allow users to use the if needed answer'] = "Permettre aux utilisateurs la possibilité d'une troisième réponse : 'Possible'";
$labels['Anonymous poll, user cannot see others responses'] = "Sondage masqué, les utilisateurs ne peuvent voir les autres réponses";
$labels['Check this for an anonyme poll, user cannot see others responses until the poll is lock'] = "Cochez cette case pour rendre ce sondage masqué, les participants ne pourront pas voir les autres réponses tant que vous ne l'aurez pas verrouillé";
$labels['This poll is anonyme, user cannot see others responses until the poll is lock'] = "Ce sondage est anonyme. Les participants verront les autres réponses lorsque le sondage aura été verrouillé par l'organisateur";
$labels['The date is no more validate, please refresh the poll'] = "La date n'est plus validée par l'organisateur, veuillez rafraichir le sondage.";
$labels['Freebusy'] = "Disponibilités";
$labels['Add calendars freebusy'] = "Rechercher...";
$labels['Add calendars freebusy tooltip'] = "Rechercher une personne pour afficher ses disponibilités dans le calendrier";
$labels['Invit attendees'] = "Inviter des participants";
$labels['You can now invit attendees to answer the poll'] = "Vous pouvez désormais inviter des personnes à participer au sondage :";
$labels['Add attendees'] = "Ajouter des invités";
$labels['Add attendees tooltip'] = "Saisissez le nom pour le rechercher dans l'annuaire ou bien saisissez directement l'adresse e-mail";
$labels['This is not a valid email'] = "Ce n'est pas une adresse mail valide";
$labels['Send invitation'] = "Envoyer l'invitation";
$labels['Invitation has been sent'] = "L'invitation vient d'être envoyée.";
$labels['Sending invitation...'] = "Envoi de l'invitation...";
// Information page
$labels['Poll information page'] = 'Page d\'information sur le sondage';
$labels['Save'] = 'Enregistrer';
$labels['Modify propositions'] = 'Modifier les propositions';
$labels['Modify response'] = 'Modifier';
$labels['Delete response'] = 'Supprimer';
$labels['Lock'] = 'Verrouiller le sondage';
$labels['Unlock'] = 'Déverrouiller le sondage';
$labels['Login, to respond with your account'] = 'Connectez-vous avec votre compte pour répondre';
$labels['Please add your name'] = 'Merci de saisir votre nom';
$labels['URL to the poll'] = 'Lien vers le sondage';
$labels['Empty proposals'] = 'Il n\'y a pas de proposition';
$labels['Proposals with the most responses are '] = 'Les propositions avec le plus de réponses positives sont ';
$labels['Proposal with the most responses is '] = 'La proposition avec le plus de réponses positives est ';
$labels['One of the proposals with the most responses is '] = "Une des propositions avec le plus de réponses positives est ";
$labels['Proposals validate by the organizer are '] = 'Les propositions validées par le créateur du sondage sont ';
$labels['Proposal validate by the organizer is '] = 'La proposition validée par le créateur du sondage est ';
$labels['responses'] = 'réponses';
$labels['response'] = 'réponse';
$labels['Your name'] = 'Votre nom';
$labels['You'] = 'Vous';
$labels['Modify responses'] = 'Modifier les réponses';
$labels['Delete'] = 'Supprimer';
$labels['Cancel'] = 'Annuler';
$labels['Yes'] = 'Oui';
$labels['No'] = 'Non';
$labels['If needed'] = 'Possible';
$labels['delete'] = 'Supprimer';
$labels['Remove'] = 'Enlever';
$labels['Your response has been saved'] = 'Votre réponse a correctement été prise en compte';
$labels['date_label'] = 'le %%l %%d %%F %%Y à %%Hh%%i';
$labels['Are you sure ? Not saved proposals are lost'] = 'Êtes-vous sûr de vouloir changer de type de sondage ? Les propositions non sauvegardees seront perdues.';
$labels['attendee'] = 'participant';
$labels['attendees'] = 'participants';
$labels['Poll attendees list'] = "Liste des participants au sondage";
$labels['Are you sure you want to delete the poll ?'] = 'Etes-vous sur de vouloir supprimer ce sondage ?';
$labels['Are you sure you want to delete your response ?'] = 'Etes-vous sur de vouloir supprimer votre reponse au sondage ?';
$labels['User authenticate'] = 'Utilisateur authentifié';
$labels['User not authenticate'] = 'Utilisateur non authentifié';
$labels['Accept/refuse the proposal'] = 'Accepter ou refuser la proposition';
$labels['Copy this url to share your poll'] = 'Copiez cette url pour partager le sondage (Clic droit, Copier l\'adresse du lien)';
$labels['Choose date on the calendar'] = 'Choisissez une date dans le calendrier';
$labels['Name already exists'] = 'Le nom saisi est deja present dans le sondage';
$labels['This poll only accept auth users'] = 'Ce sondage n\'autorise que les utilisateurs authentifiés à répondre.';
$labels['Clic to delete your response'] = 'Cliquez ici pour supprimer votre réponse';
$labels['Response not deleted'] = 'Une erreur s\'est produite lors de la suppression de votre réponse';
$labels['User not found'] = 'L\'utilisateur n\'a pas été trouvé';
$labels['Your response has been deleted'] = 'Votre réponse a bien été supprimé';
$labels['Clic to connect and respond with your account'] = 'Cliquez ici pour vous connecter avec votre compte et répondre au sondage';
$labels['Clic to refresh the poll'] = "Cliquez ici pour actualiser le sondage";
$labels['Refresh poll'] = 'Actualiser le sondage';
$labels['Poll does not exist'] = "Le sondage n'existe pas";
$labels['Download ICS'] = "Télécharger l'ICS";
$labels['Clic to download ICS of the proposal and add it to your calendar client'] = "Cliquez ici pour télécharger le fichier iCalendar de la proposition et l'ajouter à votre outil d'agenda";
$labels['Add to calendar'] = "Ajouter à l'agenda M2";
$labels['Clic to add this proposal to your calendar'] = "Cliquez ici pour ajouter cette proposition à votre agenda personnel. Les événements en provisoire seront nettoyés";
$labels['Event has been saved in your calendar'] = "L'évènement vient d'être créé dans votre agenda personnel";
$labels['Validate proposal'] = "Valider";
$labels['Clic to validate this proposal'] = "Cliquez-ici pour valider cette proposition";
$labels['Unvalidate proposal'] = "Dévalider";
$labels['Clic to unvalidate this proposal'] = "Cliquez-ici pour ne plus valider cette proposition";
$labels['Proposal has been validate for this poll'] = "La proposition vient d'être validée pour le sondage";
$labels['Proposal has been validate for this poll. E-mail has been sent to attendees.'] = "La proposition vient d'être validée pour le sondage. Le message a été envoyé aux participants.";
$labels['Proposal has been unvalidate for this poll'] = "La proposition n'est plus validée pour le sondage";
$labels['Error while modifying the poll'] = "Une erreur s'est produite lors de la modification du sondage";
$labels['hide attendees'] = "Masquer les participants";
$labels['Clic to hide attendees'] = "Cliquez ici pour masquer les autres participants";
$labels['show attendees'] = "Afficher les participants";
$labels['Clic to show attendees'] = "Cliquez ici pour afficher les participants";
$labels['Add attendee'] = "Ajouter un participant";
$labels['Clic to add an attendee'] = "Cliquez ici pour ajouter manuellement un participant au sondage";
$labels['Check all'] = "Tout cocher";
$labels['Clic to check all checkboxes'] = "Cliquez pour cocher toutes les réponses";
$labels['Uncheck all'] = "Tout décocher";
$labels['Clic to uncheck all checkboxes'] = "Cliquez pour décocher toutes les réponses";
$labels['Clic to check all yes radio'] = "Cliquez pour cocher toutes les réponses à Oui";
$labels['Clic to check all no radio'] = "Cliquez pour cocher toutes les réponses à Non";
$labels['Clic to check all if needed radio'] = "Cliquez pour cocher toutes les réponses à Possible";
$labels['This proposals is already in your calendar'] = "Cette proposition est déjà présente dans votre calendrier";
$labels['Your email'] = "Votre adresse e-mail";
$labels['Email address'] = "Adresse e-mail";
$labels['Put your email if you want to received notifications'] = "Saisissez votre adresse email si vous souhaitez recevoir les notifications";
$labels['Do you want to send a message to the attendees ?'] = "Souhaitez-vous envoyer un message de notification aux participants du sondage ? <br><br>Attention : pensez à renvoyer votre message d'invitation aux personnes n'ayant pas répondu à ce sondage pour les avertir de la date retenue.";
$labels['Your freebusy'] = "Vos disponibilités";
$labels['Your freebusy title'] = "Liste de vos disponibilités basées sur votre agenda personnel";
$labels['None'] = "Libre";
$labels['Tentative'] = "Provisoire";
$labels['Confirmed'] = "Occupé";
$labels['Cancelled'] = "Annulé";
$labels['Would you like to add responses to your calendar as tentative ?'] = "Souhaitez-vous enregistrer vos réponses dans votre agenda en événements provisoires ?";
$labels['This event has status tentative, you can view more information about the poll by opening this link'] = "Cet événement est provisoire, vous pouvez voir plus d'informations sur le sondage en cliquant ici";
$labels['You can delete tentative events of this poll by opening this link'] = "Vous pouvez supprimer les événements provisoires de ce sondage en cliquant ici";
$labels['Delete tentatives'] = "Supprimer les provisoires";
$labels['Clic here to delete tentatives link to this poll'] = "Cliquez ici pour supprimer de votre agenda les événements provisoires liés au sondage";
$labels['Would you like to delete tentatives events of this poll from your calendar ?'] = "Êtes-vous sûr de vouloir supprimer de votre agenda les événements provisoires de ce sondage ?";
$labels['Tentatives correctly deleted'] = "Les événements provisoires ont bien été supprimés";
$labels['Save from freebusy'] = "Cocher selon vos disponibilités";
$labels['Clic here to automaticaly generate your response from your feebusy'] = "Cliquez-ici pour automatiquement cocher les réponses en fonction de vos disponibilités. Vous pourrez modifier votre réponse ensuite.";
$labels['Clic here to participate'] = "Cliquez ici pour confirmer votre participation et ajouter la réunion à votre agenda";
$labels['Clic here to decline participation'] = "Cliquez ici pour refuser la participation";
$labels['Lock the poll'] = "Verrouiller le sondage";
$labels['Remember to lock the poll when it\'s finished'] = "Pensez à verrouiller le sondage quand les réponses vous conviennent.";
$labels['So you can create the meeting'] = "Vous pourrez alors créer automatiquement la réunion dans votre agenda";
$labels['Clic here to lock the poll'] = "Cliquez ici pour verrouiller le sondage";
$labels['Validate one or more prop'] = "Validez vos propositions !";
$labels['Your poll is now lock! You can validate one or more proposal to notify the attendees'] = "Votre sondage est maintenant verrouillé ! Vous pouvez valider une ou plusieurs propositions pour notifier les participants.";
$labels['Validate this proposal'] = "Valider cette proposition";
$labels['Validate your presence'] = "Confirmez votre présence";
$labels['Organizer has validate one or more date, you can now say if you be there or not'] = "L'organisateur du sondage a validé une ou plusieurs date(s). Vous pouvez désormais confirmer ou non votre présence pour cette/ces date(s) :";
$labels['I\'ll be there'] = "Je serai présent";
$labels['I\'ll not be there'] = "Je ne serai pas présent";
$labels['Choose the calendar to use'] = "Choisissez quel agenda utiliser pour la réponse";
$labels['created by'] = 'créé par';
$labels['Keep date'] = 'Date retenue';
// Date information
$labels['Start'] = 'Début';
$labels['End'] = 'Fin';
$labels['All day'] = 'Journée';
$labels['Today'] = 'Aujourd\'hui';
$labels['Month'] = 'Mois';
$labels['Week'] = 'Semaine';
$labels['Day'] = 'Jour';
// Day
$labels['Monday'] = 'Lundi';
$labels['Tuesday'] = 'Mardi';
$labels['Wednesday'] = 'Mercredi';
$labels['Thursday'] = 'Jeudi';
$labels['Friday'] = 'Vendredi';
$labels['Saturday'] = 'Samedi';
$labels['Sunday'] = 'Dimanche';
$labels['Mon'] = 'Lun';
$labels['Tue'] = 'Mar';
$labels['Wed'] = 'Mer';
$labels['Thu'] = 'Jeu';
$labels['Fri'] = 'Ven';
$labels['Sat'] = 'Sam';
$labels['Sun'] = 'Dim';
// Month
$labels['January'] = 'Janvier';
$labels['February'] = 'Février';
$labels['March'] = 'Mars';
$labels['April'] = 'Avril';
$labels['May'] = 'Mai';
$labels['June'] = 'Juin';
$labels['July'] = 'Juillet';
$labels['August'] = 'Août';
$labels['September'] = 'Septembre';
$labels['October'] = 'Octobre';
$labels['November'] = 'Novembre';
$labels['December'] = 'Décembre';
$labels['Jan'] = 'Jan';
$labels['Feb'] = 'Fév';
$labels['Mar'] = 'Mar';
$labels['Apr'] = 'Avr';
$labels['May'] = 'Mai';
$labels['Jun'] = 'Juin';
$labels['Jul'] = 'Juil';
$labels['Aug'] = 'Aoû';
$labels['Sep'] = 'Sep';
$labels['Oct'] = 'Oct';
$labels['Nov'] = 'Nov';
$labels['Dec'] = 'Déc';
// Loading
$labels['Adding prop to your calendar...'] = 'Enregistrement du/des événement(s) dans votre calendrier...';
$labels['Load freebusy...'] = 'Chargement de vos disponibilités';
$labels['Loading your events...'] = 'Chargement des événements de votre agenda...';
$labels['Deleting tentatives...'] = "Suppression des provisoires...";
// Copyright
$labels['copyright'] = 'MEEM/MLHD - PNE annuaire et messagerie';
// Mail
$labels['Mail sent by a robot'] = "Ne répondez pas à ce message, il est envoyé automatiquement par un robot.\r\nSi vous avez des difficultés d'utilisation de l'application, vous pouvez vous rapprocher de votre cellule informatique.";
$labels['Create poll mail subject'] = "%%app_name%% : Le sondage « %%poll_title%% » vient d'être créé";
$labels['Response notification mail subject'] = "%%app_name%% : %%user_shortname%% a répondu à « %%poll_title%% »";
$labels['Validate proposal mail subject'] = "%%app_name%% : « %%validate_proposal%% » vient d'être validé pour « %%poll_title%% »";
$labels['Send invitation mail subject'] = "%%app_name%% : « %%organizer_shortname%% » souhaite que vous répondiez au sondage « %%poll_title%% »";

$labels['Validate proposal organizer mail subject'] = "%%app_name%% : Vous venez de valider « %%validate_proposal%% » pour « %%poll_title%% »";
$labels['Notified attendees list'] = 'Voici la liste des participants qui ont été notifiés :';
$labels['Unnotified attendees list'] = "Voici la liste des participants qui n'ont pas pu être notifiés, car ils n'ont pas renseigné d'adresse e-mail:";
$labels['Attendees were not notified'] = "Les participants n'ont pas été notifiés.";
$labels['Attendees with email address'] = "Liste des participants avec une adresses e-mail:";
$labels['Attendees without email address'] = "Liste des participants qui n'ont pas renseigné d'adresse e-mail:";

$labels['Modify poll mail subject'] = "%%app_name%% : Le sondage « %%poll_title%% » vient d'être modifié";
$labels['Modify proposals mail subject'] = "%%app_name%% : Les propositions du sondage « %%poll_title%% » ont changé";
$labels['Deleted poll mail subject'] = "%%app_name%% : Le sondage « %%poll_title%% » vient d'être supprimé";

$labels['New title'] = 'Nouveau titre';
$labels['New location'] = 'Nouvel emplacement';
$labels['Old location'] = 'Ancien emplacement';
$labels['New description'] = 'Nouvelle description';
$labels['New proposal'] = 'Nouvelle proposition';
$labels['New proposals'] = 'Nouvelles propositions';
$labels['Deleted proposal'] = 'Proposition supprimée';
$labels['Deleted proposals'] = 'Propositions supprimées';
