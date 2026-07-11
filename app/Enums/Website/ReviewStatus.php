<?php

namespace App\Enums\Website;

enum ReviewStatus: string
{
    case REJECTED = 'rejected';
    case TRANSFERED = 'transfered';
}
