<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case REJECTED = 'rejected';
    case TRANSFERED = 'transfered';
}
