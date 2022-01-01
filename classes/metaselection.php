<?php

class Metaselection {

    private $meta1;
    private $meta2;
    private $meta3;

    public function __construct() {
        global $DB;

        // Meta 1
        $sqlmeta1 = $DB->get_records_sql("SELECT * FROM {config_plugins} WHERE plugin = 'local_isymeta' AND name LIKE '%meta1_selection%'");

        foreach ($sqlmeta1 as $line) {
            $meta1_raw = explode('<br>', $line->value);
        }

        foreach ($meta1_raw as $part) {
            $this->meta1[]['id'] = strstr($part, ':', true);
            $this->meta1[]['string_de'] = $this->extract_part($part, ':', '|');
            $this->meta1[]['string_en'] = substr($part, strpos($part, '|') + 1);
        }

        // Meta 2
        $sqlmeta2 = $DB->get_records_sql("SELECT * FROM {config_plugins} WHERE plugin = 'local_isymeta' AND name LIKE '%meta2_selection%'");

        foreach ($sqlmeta2 as $line) {
            $meta2_raw = explode('<br>', $line->value);
        }

        foreach ($meta2_raw as $part) {
            $this->meta2[]['id'] = strstr($part, ':', true);
            $this->meta2[]['string_de'] = $this->extract_part($part, ':', '|');
            $this->meta2[]['string_en'] = substr($part, strpos($part, '|') + 1);
        }

        // Meta 6
        $sqlmeta3 = $DB->get_records_sql("SELECT * FROM {config_plugins} WHERE plugin = 'local_isymeta' AND name LIKE '%meta6_selection%'");

        foreach ($sqlmeta3 as $line) {
            $meta3_raw = explode('<br>', $line->value);
        }

        foreach ($meta3_raw as $part) {
            $this->meta3[]['id'] = strstr($part, ':', true);
            $this->meta3[]['string_de'] = $this->extract_part($part, ':', '|');
            $this->meta3[]['string_en'] = substr($part, strpos($part, '|') + 1);
        }
    }

    public function get_meta($num) {
        $result = [];

        if($num == 1) {
            foreach($this->meta1 as $a) {
                if(array_key_exists('string_de', $a)) {
                    $result[] = $a['string_de'];
                } 
            }
        } else if($num == 2) {
            foreach($this->meta2 as $a) {
                if(array_key_exists('string_de', $a)) {
                    $result[] = $a['string_de'];
                } 
            }
        } else {
            foreach($this->meta3 as $a) {
                if(array_key_exists('string_de', $a)) {
                    $result[] = $a['string_de'];
                } 
            }
        }
        
        return $result;

    }

    public function get_meta_array() {
        return $this->meta2;
    }

    private function extract_part($str, $start_str, $end_str) {
        $substr = strpos($str, $start_str);
        $substr += strlen($start_str);
        $len = strpos($str, $end_str, $substr) - $substr;

        return substr($str, $substr, $len);
    }
}