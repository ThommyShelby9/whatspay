<?php

namespace App\Consts;

class Util
{

  const JWTKEY = "jIwMjUwOTA5MTkxMDQ0IiwibWFpbCI6InN11989";
  const MAILSENDER_QUEUE = "MailSenderQueue";
  const RESENDMAIL_QUEUE = "ResendMailQueue";
  const LOG_QUEUE = "LogQueue";
  const TYPES_ROLE = [
    'ADMIN' => 'ADMIN',
    'ANNONCEUR' => 'ANNONCEUR',
    'DIFFUSEUR' => 'DIFFUSEUR'
  ];

  const RIGHTS = [
    ['typerole' => self::TYPES_ROLE["ADMIN"], 'code' => 'CREATE_USER', 'description' => 'Creer un utilisateur'],
    ['typerole' => self::TYPES_ROLE["ADMIN"], 'code' => 'UPDATE_USER', 'description' => 'Mette a jour un utilisateur'],
    ['typerole' => self::TYPES_ROLE["ADMIN"], 'code' => 'DELETE_USER', 'description' => 'Supprimer un utilisateur'],
    ['typerole' => self::TYPES_ROLE["ADMIN"], 'code' => 'LIST_USERS', 'description' => 'Voir liste des utilisateurs'],

    ['typerole' => self::TYPES_ROLE["ADMIN"], 'code' => 'VALIDATE_TASK', 'description' => 'Valider une tache'],
    ['typerole' => self::TYPES_ROLE["ADMIN"], 'code' => 'ASSIGN_TASK', 'description' => 'Assigner une tache'],
    ['typerole' => self::TYPES_ROLE["ADMIN"], 'code' => 'VALIDATE_TASK_RESULT', 'description' => 'Traiter les resultats d\'une tache'],

    ['typerole' => self::TYPES_ROLE["DIFFUSEUR"], 'code' => 'DIFFUSEUR_RIGHTS', 'description' => 'Droits de diffuseur'],
    ['typerole' => self::TYPES_ROLE["ANNONCEUR"], 'code' => 'ANNONCEUR_RIGHTS', 'description' => 'Droits d\'annonceur'],
  ];

  const TASKS_STATUSES = [
    'PENDING' => 'PENDING',
    'PAID' => 'PAID',
    'ACCEPTED' => 'ACCEPTED',
    'REJECTED' => 'REJECTED',
    'CLOSED' => 'CLOSED',
  ];

  const ASSIGNMENTS_STATUSES = [
    'ASSIGNED' => 'ASSIGNED',
    'PENDING' => 'PENDING',
    'REJECTED' => 'REJECTED',
    'SUBMITED' => 'SUBMITED',
    'SUBMISSION_ACCEPTED' => 'SUBMISSION_ACCEPTED',
    'SUBMISSION_REJECTED' => 'SUBMISSION_REJECTED',
    'PAID' => 'PAID'
  ];

  const PHONE_STATUSES = [
    'PENDING' => ['label' => 'PENDING', 'badge' => 'warning'],
    'ACTIVE' => ['label' => 'ACTIVE', 'badge' => 'success'],
    'INACTIVE' => ['label' => 'INACTIVE', 'badge' => 'danger']
  ];

  const TASKS_TYPES = [
    'URL' => ['CODE' => 'URL', 'DESCRIPTION' => 'Publication de type Lien accompagnée d\'un texte descriptif', 'UPLOAD' => false, 'MAX_FILE' => 0, 'EXTENSIONS' => '', 'URL' => true],
    'TXT' => ['CODE' => 'TXT', 'DESCRIPTION' => 'Publication de type Texte', 'UPLOAD' => false, 'MAX_FILE' => 0, 'EXTENSIONS' => '', 'URL' => false],
    'IMG' => ['TYPE' => 'IMG', 'DESCRIPTION' => 'Publication de type Image accompagnée d\'un texte descriptif', 'UPLOAD' => true, 'MAX_FILE' => 1, 'EXTENSIONS' => 'image/*', 'URL' => false],
    'VID' => ['TYPE' => 'VID', 'DESCRIPTION' => 'Publication de type Video accompagnée d\'un texte descriptif', 'UPLOAD' => true, 'MAX_FILE' => 1, 'EXTENSIONS' => 'video/*', 'URL' => false],
    'AUD' => ['TYPE' => 'AUD', 'DESCRIPTION' => 'Publication de type Audio accompagnée d\'un texte descriptif', 'UPLOAD' => true, 'MAX_FILE' => 1, 'EXTENSIONS' => '.mp3', 'URL' => false],
  ];
}
