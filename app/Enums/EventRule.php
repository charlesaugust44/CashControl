<?php

namespace App\Enums;

enum EventRule: string
{
    case Fixed = 'fixed';
    case Variable = 'variable';
    case MaxLastFiveMonths = 'max_last_five_months';
}
