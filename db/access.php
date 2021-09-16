<?php
/**
 *
 * @package        block_isymetaselect
 * @author        Dustin Neß <dustin.ness@th-luebeck.de>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'block/isymetaselect:editmeta' => array(
        'riskbitmask' => RISK_SPAM | RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            #'kursmanager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        )
    ),

    'block/isymetaselect:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW,
            #'kursmanager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        )

    ),

    'block/isymetaselect:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_CONFIG,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            #'kursmanager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        ),
    ),
);

