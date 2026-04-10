<?php

namespace App\Enums;

enum Priority: string
{
    case LOW = 'Low';
    case NORMAL = 'Normal';
    case HIGH = 'High';
    case CRITICAL = 'Critical';
}