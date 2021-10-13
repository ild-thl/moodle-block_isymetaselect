<?php

class Metastring {

    private array $meta_de, $meta_en;

    public function __construct() {
        global $DB;

        $rawresults = $DB->get_records_sql("SELECT * FROM {config_plugins} WHERE plugin = 'local_isymeta' AND name LIKE '%metastring%'");

        foreach ($rawresults as $res) {
            if(strpos($res->name, '_de')) {
                $this->meta_de[] = $res->value;
            } else {
                $this->meta_en[] = $res->value;
            }
        }
    }

    // returns string based on prefered user language and given array position
    public function get($arr_pos) {
        global $USER;
        
        if($USER->lang == 'de' || $USER->lang == 'de_du') {
            return $this->meta_de[$arr_pos];

        } else {
            return $this->meta_en[$arr_pos];
        }
    }
}