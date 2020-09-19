<?php

function layout($layout='main', $view, $vars=array()) {

    $ci = get_instance();
    $content = $ci->load->view($view, $vars, true);
    $ci->load->view($layout, array('content'=>$content));
}

function pr($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function prd($data) {
    pr($data); die;
}

function flash($messages=array()) {
    $ci = get_instance();
    $ci->session->set_userdata('smessages', $messages);
    $ci->session->mark_as_flash('smessages');
}

function flashd($messages=array()) {
    $ci = get_instance();
    $ci->session->set_userdata('dmessages', $messages);
    $ci->session->mark_as_flash('dmessages');
}

function to_list($data, $key, $value) {
    $return = array();
    foreach($data as $item) {
        $return[$item[$key]] = $item[$value];
    }
    return $return;
}

function to_list_array($data, $key, $value) {
    $return = array();
    foreach($data as $item) {
        if(is_null($value)) {
            $return[$item[$key]][] = $item;
        } else {
            $return[$item[$key]][] = $item[$value];
        }
    }
    return $return;
}