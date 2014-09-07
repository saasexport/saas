<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/report/saas_export/locallib.php');

$syscontext = context_system::instance();
$may_export = has_capability('report/saas_export:export', $syscontext);

$message = '';

if(isset($_POST['map_polos']) && isset($_POST['save']) && $may_export) {
    $mapped = $DB->get_records('saas_map_catcourses_polos', array('type'=>'category'), null, 'instanceid, id, polo_id');
    $saved = false;
    foreach($_POST['map_polos'] AS $categoryid=>$polo_id) {
        if(isset($mapped[$categoryid]) && empty($polo_id)) {
            $DB->delete_records('saas_map_catcourses_polos', array('id'=>$mapped[$categoryid]->id));
            $saved = true;
        }
    }
    foreach($_POST['map_polos'] AS $categoryid=>$polo_id) {
        if(isset($mapped[$categoryid]) && !empty($polo_id)) {
            if($polo_id != $mapped[$categoryid]->polo_id) {
                $obj = new stdClass();
                $obj->id = $mapped[$categoryid]->id;
                $obj->polo_id = $polo_id;
                $DB->update_record('saas_map_catcourses_polos', $obj);
                $saved = true;
            }
        }
    }
    foreach($_POST['map_polos'] AS $categoryid=>$polo_id) {
        if(!isset($mapped[$categoryid]) && !empty($polo_id)) {
            $obj = new stdClass();
            $obj->type = 'category';
            $obj->instanceid = $categoryid;
            $obj->polo_id = $polo_id;
            $DB->insert_record('saas_map_catcourses_polos', $obj);
            $saved = true;
        }
    }
    $message = $saved  ? get_string('saved', 'report_saas_export') : get_string('no_changes', 'report_saas_export');
}

print html_writer::start_tag('DIV', array('align'=>'center'));
print $OUTPUT->heading(get_string('category_to_polo', 'report_saas_export'));
print $OUTPUT->box_start('generalbox boxwidthwide');
print html_writer::tag('P', get_string('category_to_polo_msg1', 'report_saas_export'), array('class'=>'justifiedalign'));
print html_writer::tag('P', get_string('category_to_polo_msg2', 'report_saas_export'), array('class'=>'justifiedalign'));
print $OUTPUT->box_end();
print html_writer::end_tag('DIV');

if($message) {
    print $OUTPUT->heading($message, 4, 'saas_export_message');
}

$categories = saas_get_category_tree_map_categories_polos();
$polos = saas_get_polos_menu();

print html_writer::start_tag('DIV', array('class'=>'category_tree'));
if(empty($categories)) {
    print $OUTPUT->heading('Não foram encontrados mapeamentos de cursos Moodle para ofertas de disciplinas');
} else {
    print html_writer::tag('SPAN', get_string('moodle_categories', 'report_saas_export'), array('class'=>'titleleft'));
    print html_writer::tag('SPAN', get_string('polos_title', 'report_saas_export'), array('class'=>'titleright'));

    print html_writer::start_tag('form', array('method'=>'post', 'action'=>'index.php'));
    print html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'action', 'value'=>'polo_mapping'));

    print html_writer::start_tag('OL', array('class'=>'tree'));
    saas_show_category_tree_map_categories_polos($categories, $polos);
    print html_writer::end_tag('OL');

    print html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'save', 'value'=>s(get_string('save', 'admin'))));
    print html_writer::end_tag('form');
}
print html_writer::end_tag('DIV');