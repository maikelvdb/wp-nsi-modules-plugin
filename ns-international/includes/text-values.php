<?php

class TextValues {

    private const VALUES = [];

    public static function get($key) {
        
        if (!array_key_exists($key, self::VALUES)) {
            return get_option("nsi_text_$key", self::DEFAULTS[$key]);
        }
        
        return self::VALUES[$key];
    }

    public const DEFAULTS = [
        'from' => 'Vertrekstation',
        'to' => 'Aankomststation',
        'date' => 'Vertrek op',
        'search' => 'Zoeken',
        'view_prices' => 'Bekijk prijzen',
        'transfer_amount' => '#x overstappen',
        'show_all' => 'Toon alles',
        'search_tickets' => 'Zoek tickets',
        'error-no-data' => 'Kan geen gegevens ophalen, probeer het later opnieuw.',
        'dl_product_name' => 'Trein van {from} naar {to} op {date}',
        'dl_product_description' => 'Goedkoopste treinkaartje van {from} naar {to} op {date} voor {price}',
        'dayschedule-error-no-data'=> 'Geen gegevens gevonden voor deze reis.',
    ];
}
