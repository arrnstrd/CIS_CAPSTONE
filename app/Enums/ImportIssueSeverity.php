<?php

namespace App\Enums;

enum ImportIssueSeverity: string
{
    case Error = 'error';
    case Warning = 'warning';
}
