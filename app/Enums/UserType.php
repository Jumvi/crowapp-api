<?php

namespace App\Enums;

enum UserType : string 
{
    case ADMIN = 'admin';
    case PORTEUR_PROJET = 'porteur_projet';
    case INVESTISSEUR = 'investisseur';
}