<?php
if (!defined("IN_TWIMI_PHP")) die('{"status":"forbidden access"}');
include APP_PATH . "core/database.php";
include APP_PATH . "core/view.php";

function tp_route()
{
    if (isset($_REQUEST['mod'])) {
        $mod = $_REQUEST['mod'];
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
        return ['mod' => $mod, 'action' => $action];
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
        return ['mod' => $mod, 'action' => $action, 'param' => $param];
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
        view::error(['ret' => '-2', 'status' => 'action not exists', 'tp_error_msg' => "Action不存在"]);
    }
}

function tp_call_class($mod, $action)
{
    $controller = ucfirst($mod) . 'Controller';
    if (!class_exists($controller)) {
        view::error(['ret' => '-1', 'status' => 'mod not exists', 'tp_error_msg' => "模块${mod}不存在"]);
    } else {

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
        } elseif (file_exists(APP_PATH . "controller/${mod}Controller.php")) {
            include APP_PATH . "controller/${mod}Controller.php";
            if ($action != null) {
                tp_call_class($mod, $action);
            } else if ($action == null) {
                tp_call_class($mod, "ac_index");
            }
        } else {
            view::error(['ret' => '-1', 'status' => 'mod not exists', 'tp_error_msg' => "模块${mod}不存在"]);
        }
    }
}