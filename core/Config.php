<?php

class Config
{
    const MODE_CONST = 0;
    const MODE_ARRAY = 1;

    public static function load($name)
    {
        if (file_exists(APP_PATH . "config/{$name}.php")) {
            return require APP_PATH . "config/{$name}.php";
        } else {
            return [];
        }
    }

    public static function make($configs = [], $type = self::MODE_CONST)
    {
        $config_text = "<?php\r\n";
        if ($type == self::MODE_CONST) {
            foreach ($configs as $k => $v) {
                $config_text .= "define(\"{$k}\",\"{$v}\");\r\n";
            }
        } else {
            $config_text .= "return [\r\n";
            foreach ($configs as $k => $v) {
                $config_text .= "\"{$k}\"=>";
                if (is_array($v)) {
                    $config_text .= "\t[\r\n";
                    foreach ($v as $k2 => $v2) {
                        $config_text .= "\t\"{$k2}\"=>\"{$v2}\",\r\n";
                    }
                    $config_text .= "],\r\n";
                } else {
                    $config_text .= "\"{$v}\",\r\n";
                }
            }
            $config_text .= ']';
        }
        return $config_text;
    }
}