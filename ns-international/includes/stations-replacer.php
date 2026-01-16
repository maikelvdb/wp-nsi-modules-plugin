<?php

class StationsReplacer {

    private static $VALUES = [];

    public static function replace($key) {
        
        if (!array_key_exists($key, self::$VALUES)) {
            return get_option("nsi_mapping_$key", StationsReplacer::tryGetDefault($key));
        }
        
        return self::$VALUES[$key];
    }

    public const DEFAULTS = [
        'NLASD' => 'NLASC'
    ];

    public static function tryGetDefault($key) {
        if (array_key_exists($key, self::DEFAULTS)) {
            return self::DEFAULTS[$key];
        }

        return $key;
    }

    public static function getAll() {
        global $wpdb;

        $prefix = 'nsi_mapping_' . '%';
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value 
                FROM $wpdb->options 
                WHERE option_name LIKE %s
                ORDER BY option_id",
                $prefix
            )
        );

        // Convert DB results into key => value pairs
        $results = [];
        foreach ($options as $option) {
            // Remove prefix to make key cleaner (optional)
            $key = str_replace('nsi_mapping_', '', $option->option_name);
            $results[$key] = $option->option_value;
        }

        // Merge defaults for missing keys
        foreach (self::DEFAULTS as $key => $value) {
            if (!array_key_exists($key, $results)) {
                $results[$key] = $value;
            }
        }

        // Convert to [{ "KEY": "VALUE" }, ... ]
        $formatted = [];
        foreach ($results as $key => $value) {
            $obj = new stdClass();
            $obj->key = $key;
            $obj->value = $value;
            $formatted[] = $obj;
        }

        return $formatted;
    }

    public static function upsert($key, $value) {
        if (array_key_exists($key, self::$VALUES)) {
            self::$VALUES[$key] = $value;
        } else {
            self::$VALUES[$key] = $value;
        }

        update_option("nsi_mapping_$key", $value);
        wp_cache_flush();
    }

    public static function remove($key) {
        if (array_key_exists($key, self::$VALUES)) {
            unset(self::$VALUES[$key]);
        }

        delete_option("nsi_mapping_$key");
    }
}
