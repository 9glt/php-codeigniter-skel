<?php

function layout($layout='main', $view, $vars=array())
{
    $ci = get_instance();
    $content = $ci->load->view($view, $vars, true);
    $ci->load->view($layout, array('content'=>$content));
}

function pr($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function prd($data)
{
    pr($data);
    die;
}

function flash($messages=array())
{
    $ci = get_instance();
    $ci->session->set_userdata('smessages', $messages);
    $ci->session->mark_as_flash('smessages');
}

function flashd($messages=array())
{
    $ci = get_instance();
    $ci->session->set_userdata('dmessages', $messages);
    $ci->session->mark_as_flash('dmessages');
}

function to_list($data, $key, $value)
{
    $return = array();
    foreach ($data as $item) {
        $return[$item[$key]] = $item[$value];
    }
    return $return;
}

function to_list_array($data, $key, $value)
{
    $fields = explode('.', $key);
    $table  = $fields[0];
    if (count($fields) > 1) {
        $table = $fields[0];
        $field = $fields[1];
    }
    $return = array();
    foreach ($data as $item) {
        if (is_null($value)) {
            if (empty($field)) {
                $return[$item[$table]][] = $item;
            } else {
                $return[$item[$table][$field]][] = $item;
            }
        } else {
            if (empty($field)) {
                $return[$item[$key]][] = $item[$table];
            } else {
                $return[$item[$key]][] = $item[$table][$field];
            }
        }
    }
    return $return;
}

function view($view, $params=array(), $return = true)
{
    return get_instance()->load->view($view, $params, $return);
}

function history($url, $name)
{
    if (empty($name)) {
        return;
    }
    if (!isset($_SESSION['history'])) {
        $_SESSION['history'] = array();
    }
    foreach ($_SESSION['history'] as $k=>$item) {
        if (!is_array($item)) {
            unset($_SESSION['history'][$k]);
        }
    }
    // $_SESSION['history'][] = array($url, $name);
    $found = false;
    foreach ((array)$_SESSION['history'] as $k=>$item) {
        if (@$item[1] == $name) {
            $found = $k;
        }
    }
    if ($found === false) {
        $_SESSION['history'][] = array($url, $name);
    } else {
        $item = $_SESSION['history'][$found];
        unset($_SESSION['history'][$found]);
        $_SESSION['history'][] = $item;
    }
    if (count($_SESSION['history']) > 10) {
        array_shift($_SESSION['history']);
    }
}


function my_config($item)
{
    static $_config;

    if (empty($_config)) {
        // references cannot be directly assigned to static variables, so we use an array
        $_config[0] =& get_config();
    }
    $parts = explode('.', $item);
    $key = $parts[0];
    $subkey = null;
    if (count($parts) > 1) {
        $key = $parts[1];
        $subkey = $parts[0];
    }

    if ($subkey == null) {
        return isset($_config[0][$key]) ? $_config[0][$key] : null;
    }
    return isset($_config[0][$subkey][$key]) ? $_config[0][$subkey][$key] : null;
}


function whitelist($data, $fields= array())
{
    $return = array();
    foreach ($data as $k=>$v) {
        if (in_array($k, $data)) {
            $return[] = array($k=>$v);
        }
    }
    return $return;
}

function blacklist($data, $fields = array())
{
    $return = array();
    foreach ($data as $k=>$v) {
        if (!in_array($k, $fields)) {
            $return[] = array($k=>$v);
        }
    }
    return $return;
}
