<?php

class Metaselection {

    private array $meta1;

    public function __construct() {
        global $DB;

        $sqlmeta1 = $DB->get_records_sql("SELECT * FROM {config_plugins} WHERE plugin = 'local_isymeta' AND name LIKE '%1_selection%'");

        foreach ($sqlmeta1 as $line) {
            $meta1_raw = explode('<br>', $line->value);
        }

        foreach ($meta1_raw as $part) {
            $this->meta1[]['id'] = strstr($part, ':', true);
            $this->meta1[]['string_de'] = $this->extract_part($part, ':', '|');
            $this->meta1[]['string_en'] = substr($part, strpos($part, '|') + 1);
        }
    }

    public function get_meta() {
        $result = [];

        foreach($this->meta1 as $a) {
            if(array_key_exists('string_de', $a)) {
                $result[] = $a['string_de'];
            } 
        }
        return $result;

    }

    private function extract_part($str, $start_str, $end_str) {
        $substr = strpos($str, $start_str);
        $substr += strlen($start_str);
        $len = strpos($str, $end_str, $substr) - $substr;

        return substr($str, $substr, $len);
    }
}