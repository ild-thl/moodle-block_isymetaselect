<?php
$functions = array(
    'blocks_ildmetaselect_getfilter' => array(           //web service name (unique in all Moodle)
        'classname'   => 'blocks_ildmetaselect_external', //class containing the function implementation
        'methodname'  => 'get_filter',              //name of the function into the class
        'classpath' => 'blocks/ildmetaselect/externallib.php',
        'description' => 'send feedback',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => false,
    )
);

$services = array(
    'get_filter' => array(
            'functions' => array ('blocks_ildmetaselect_getfilter'), 
            'restrictedusers' => 0, // if 1, the administrator must manually select which user can use this service. 
                                               // (Administration > Plugins > Web services > Manage services > Authorised users)
            'enabled'=>1, // if 0, then token linked to this service won't work
    )
);