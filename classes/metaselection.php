<?php

class Metaselection {

    private array $meta1;

    public function __construct($meta) {
        global $DB;

        $sql = "SELECT * FROM {config_plugins} WHERE plugin = 'local_isymeta' AND name LIKE '%" . $meta . "_selection%'";
        $rawtext = $DB->get_records_sql($sql);

        $sql2 = "SELECT * FROM {config_plugins} WHERE plugin = 'local_isymeta' AND name LIKE '%" . $meta . "_selection%'";
        $rawtext2 = $DB->get_records_sql_menu($sql);

        print_r($rawtext2);


        // echo substr_count($rawtext->value, '<br>');

        foreach ($rawtext as $res) {
            $this->meta1['id'] = strstr($res->value, ':', true);
            $this->meta1['string_de'] = $this->extract_part($res->value, ':', '|');
            $this->meta1['string_en'] = $this->extract_part($res->value, '|', '<br>');
        }

        // echo $c;
        // print_r($this->meta1);
    }

    // public function get_meta1() {
    //     return $meta1;
    // }

    private function extract_part($str, $start_str, $end_str) {
        $substr = strpos($str, $start_str);
        $substr += strlen($start_str);
        
        $len = strpos($str, $end_str, $substr) - $substr;

        return substr($str, $substr, $len);
    }
}


// function string_between_two_string($str, $starting_word, $ending_word)
// {
//     $subtring_start = strpos($str, $starting_word);
//     //Adding the strating index of the strating word to 
//     //its length would give its ending index
//     $subtring_start += strlen($starting_word);  
//     //Length of our required sub string
//     $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;  
//     // Return the substring from the index substring_start of length size 
//     return substr($str, $subtring_start, $size);  
// }