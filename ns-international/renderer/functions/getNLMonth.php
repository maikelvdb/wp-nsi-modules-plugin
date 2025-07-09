<?php

namespace NSInternational\Functions;

class GetNlMonth implements IFunction
{
    public function getName() { return 'getNLMonth'; }

    public function execute(array $args): string
    {
        if (empty($args)) {
            return '';
        }

        $month = $args[0]->format('m');
        $month = ltrim($month, '0');
        $month = intval($month);
        if ($month < 1 || $month > 12) {
            return '';
        }

        $months = [
            1 => 'januari',
            2 => 'februari',
            3 => 'maart',
            4 => 'april',
            5 => 'mei',
            6 => 'juni',
            7 => 'juli',
            8 => 'augustus',
            9 => 'september',
            10 => 'oktober',
            11 => 'november',
            12 => 'december'
        ];

        return $months[$month];
    }
}