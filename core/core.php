<?php
if (!defined("IN_TWIMI_PHP")) die('{"status":"forbidden access"}');
include APP_PATH . "core/Database.php";
include APP_PATH . "core/View.php";
include APP_PATH . "core/Model.php";
include APP_PATH . "core/Config.php";

function tp_route()
{
    if (isset($_REQUEST['mod'])) {
        $mod = $_REQUEST['mod'];
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
        return ['mod' => $mod, 'action' => $action, 'method' => strtolower($_SERVER['REQUEST_METHOD'])];
    } else {
        $request_url = $_SERVER['REQUEST_URI'];
        $position = strpos($request_url, '?');
        $request_url = ($position === false) ? $request_url : substr($request_url, 0, $position);
        $request_url = trim($request_url, '/');

        $url_array = explode('/', $request_url);
        $url_array = array_filter($url_array);
        $mod = $url_array ? $url_array[0] : null;
        array_shift($url_array);
        if ($mod == 'api') {
            define("IN_TWIMI_API", "True", TRUE);
            $mod = $url_array ? $url_array[0] : null;
            array_shift($url_array);
        }
        $action = $url_array ? $url_array[0] : null;
        array_shift($url_array);
        $param = $url_array ? $url_array : array();
        return ['mod' => $mod, 'action' => $action, 'method' => strtolower($_SERVER['REQUEST_METHOD']), 'param' => $param];
    }
}

function tp_call_action($action)
{
    if (is_callable($action)) {
        try {
            $func = new ReflectionFunction($action);
            if ($func->getNumberOfParameters() > 0) {
                $params = $func->getParameters();
                $value = [];
                foreach ($params as $param) {
                    $type = '' . $param->getType();
                    if (substr($type, -7) == "Service") {
                        include APP_PATH . "service/$type.php";
                        $value[] = new $type();
                    }
                }
                call_user_func_array($action, $value);
            } else {
                call_user_func($action);
            }
        } catch (ReflectionException $e) {
            call_user_func($action);
        }
    } else {
        View::error(['ret' => '-2', 'status' => 'action not exists', 'tp_error_msg' => "Action不存在"]);
    }
}

function tp_call_class($mod, $action)
{
    $modClass = ucfirst($mod) . 'Mod';
    if (!class_exists($modClass)) {
        View::error(['ret' => '-1', 'status' => 'mod not exists', 'tp_error_msg' => "模块${mod}不存在"]);
    } else {
        $controller = new $modClass();
        if (method_exists($controller, 'ac_' . $action)) {
            call_user_func_array([$controller, 'ac_' . $action], []);
        } else {
            View::error(['ret' => '-2', 'status' => 'action not exists', 'tp_error_msg' => "Action不存在"]);
        }
    }
}

function tp_run()
{
    $route = tp_route();
    $mod = $route['mod'];
    $action = $route['action'];
    if (isset($mod)) {
        if (file_exists(APP_PATH . "mod/$mod.php")) {
            include APP_PATH . "mod/$mod.php";
            if ($action != null) {
                tp_call_action('ac_' . $action);
            } else if ($action == null) {
                tp_call_action("ac_index");
            }
        } elseif (file_exists(APP_PATH . "mod/${mod}Mod.php")) {
            include APP_PATH . "mod/${mod}Mod.php";
            if ($action != null) {
                tp_call_class($mod, $action);
            } else if ($action == null) {
                tp_call_class($mod, "ac_index");
            }
        } elseif (file_exists(APP_PATH . "controller/OtherMod.php")) {
            include APP_PATH . "mod/OtherMod.php";
            if ($action != null) {
                tp_call_class($mod, $action);
            } else if ($action == null) {
                tp_call_class($mod, "ac_index");
            }
        } else {
            View::error(['ret' => '-1', 'status' => 'mod not exists', 'tp_error_msg' => "模块${mod}不存在"]);
        }
    }
}