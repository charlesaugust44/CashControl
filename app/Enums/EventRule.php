<?php

namespace App\Enums;

enum EventRule: string
{
    case Fixed = 'fixed';
    case MaxLastFiveMonths = 'max_last_five_months';
    case MeanLastFiveMonths = 'mean_last_five_months';
}
