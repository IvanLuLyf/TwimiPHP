<?php

class view
{
    public static function get_url($mod, $action, $params = [])
    {
        $query = http_build_query($params);
        return "index.php?mod=$mod&action=$action&$query";
    }

    public static function redirect($url, $action = null, $params = [])
    {
        if ($action == null) {
            header("Location: $url");
        } else {
            $query = http_build_query($params);
            header("Location: index.php?mod=$url&action=$action&$query");
        }
    }

    public static function render($view, $context = [])
    {
        if (defined("IN_TWIMI_API") or defined("IN_TWIMI_AJAX")) {
            echo json_encode($context);
        } else {
            extract($context);
            include "template/$view";
        }
    }

    public static function error($context = [])
    {
        if (defined("IN_TWIMI_API") or defined("IN_TWIMI_AJAX")) {
            echo json_encode($context);
        } else {
            echo "<h1>Error</h1>";
            echo $context['tp_error_msg'];
        }
    }
}