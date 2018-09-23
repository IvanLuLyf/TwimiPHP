<?php

class view
{
    public static function get_url($mod, $action, $params = [])
    {
        $query = http_build_query($params);
        if (TP_SITE_REWRITE)
            return "/${mod}/${action}?${query}";
        return "/index.php?mod=${mod}&action=${action}&${query}";
    }

    public static function redirect($url, $action = null, $params = [])
    {
        if ($action == null) {
            header("Location: $url");
        } else {
            $query = http_build_query($params);
            if (TP_SITE_REWRITE) {
                header("Location: /${url}/${action}?${query}");
            } else {
                header("Location: /index.php?mod=$url&action=$action&$query");
            }
        }
    }

    public static function render($view, $context = [])
    {
        if (defined("IN_TWIMI_API") or defined("IN_TWIMI_AJAX")) {
            echo json_encode($context);
        } else {
            extract($context);
            if (file_exists("template/$view")) {
                include "template/$view";
            } else if ($view == '' || $view == null) {

            } else {
                self::error(['ret' => '-3', 'status' => 'template not exists', 'tp_error_msg' => "模板${view}不存在"]);
            }
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