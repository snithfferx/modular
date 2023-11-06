<?php

declare(strict_types=1);
/**
 * Clase para el envio de mensajes
 * @description Class for sending messages
 * @category Helper
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package app\core\helpers\Messenger
 * @version 1.2.5 rev. 1
 * @date 2023-05-03
 * @time 16:20:00
 */

namespace app\core\helpers;

class Messenger
{
    /**
     * Construye un mensaje
     * @param string $tobuild
     * @param array|string $values
     * 
     * Creaando un mensaje:
     * $values = [type, code, values, data, message, extra]
     * 
     * Creando un error:
     * $values = [code, message, data, extra]
     * 
     * Creando un alert:
     * $values = [type, message, data]
     * 
     * Puede revibir un texto y este lo devolvera en una estructura de mensaje plano.
     * @return mixed
     */
    public function build(string $tobuild, array|string $values): array
    {
        switch ($tobuild) {
            case 'message':
                $result = $this->message($values);
                break;
            case 'error':
                $result = $this->error($values);
                break;
            case 'alert':
                $result = $this->alert($values);
                break;
            default:
                $result = $this->messageBuilder('message', $values);
                break;
        }
        return $result;
    }
    /**
     * Crea vista de mensaje
     * @param string $type Tipo de mensaje
     * @param array $data ['message','title','subtitle','image','image_alt','icon','type','extra']
     * @param string $driver Tipo de vista
     * 
     * @return array
     */
    public function messageBuilder(string $type, array $data, string $driver = 'view')
    {
        $message = (isset($data['message'])) ? $data['message'] : "Something went wrong!";
        $title = (isset($data['title'])) ? $data['title'] : "ERROR";
        if ($type == "message") {
            $errorType = (isset($data['type'])) ? $data['type'] : "other";
            switch ($errorType) {
                case 'error':
                    $errorTypo = [
                        'name' => "Execution alert!",
                        'code' => (isset($data['code'])) ? $data['code'] : "500",
                    ];
                    $titleColor = "danger";
                    $titleIcon = "ban";
                    break;
                case 'warning':
                    $errorTypo = [
                        'name' => "Execution alert!",
                        'code' => (isset($data['code'])) ? $data['code'] : "204",
                    ];
                    $titleColor = "warning";
                    $titleIcon = "exclamation-triangle";
                    break;
                case 'info':
                    $errorTypo = [
                        'name' => "Execution notice!",
                        'code' => (isset($data['code'])) ? $data['code'] : "100",
                    ];
                    $titleColor = "muted";
                    $titleIcon = "info";
                    break;
                case 'success':
                    $errorTypo = [
                        'name' => "Execution success!",
                        'code' => 200
                    ];
                    $titleColor = "success";
                    $titleIcon = "check-circle";
                    break;
                default:
                    $errorTypo = [
                        'name' => "Notice!",
                        'code' => (isset($data['code'])) ? $data['code'] : "409",
                    ];
                    $titleColor = "primary";
                    $titleIcon = "exclamation-circle";
                    break;
            }
            $extra = "";
            if (isset($data['extra']) && is_array($data['extra'])) {
                foreach ($data['extra'] as $i => $v) {
                    if (!is_array($v)) {
                        $extra .= "<em>$i : </em> $v<br>";
                    } else {
                        $extra .= "<em>$i : </em><br>&nbsp;&nbsp;&nbsp;&nbsp;";
                        foreach ($v as $in => $val) {
                            if (!is_array($val)) {
                                $extra .= "<em>$in : </em> $val<br>";
                            } else {
                                $extra .= "<em>$in : </em><br>&nbsp;&nbsp;&nbsp;&nbsp;";
                                $extra .= print_r($val, true);
                            }
                        }
                    }
                }
            } else {
                if (isset($data['extra']) && !is_null($data['extra']) && !empty($data['extra'])) $extra .= $data['extra'];
            }
            if ($driver == "view") {
                $response = [
                    'title'  => [
                        'color' => $titleColor,
                        'text' => $title,
                        'icon' => $titleIcon
                    ],
                    'header' => $errorTypo,
                    'mensaje' => $message,
                    'extra'  => $extra
                ];
            } elseif ($driver == "band") {
                $response = [
                    'type'   => $titleColor,
                    'header' => [
                        'title' => $title,
                        'icon'  => $titleIcon
                    ],
                    'text'   => $message,
                    'extra'  => $extra
                ];
            } else {
                $response = ['text' => $message, 'color' => $titleColor, 'icon' => $titleIcon];
            }
        } elseif ($type == "alert") {
            $subtitle = (isset($data['subtitle'])) ? $data['subtitle'] : "";
            $image = (isset($data['image'])) ? $data['image'] : "";
            $image_alt = (isset($data['image_alt'])) ? $data['image_alt'] : "";
            $icon = (isset($data['icon'])) ? "fas $data[icon]" : "fa-info-circle";
            switch ($data['type']) {
                case "error":
                    $tipo = "error";
                    $color = "bg-danger";
                    break;
                case "warning":
                    $tipo = "warning";
                    $color = "bg-warning";
                    break;
                case "information":
                    $tipo = "info";
                    $color = "bg-info";
                    break;
                default:
                    $tipo = "success";
                    $color = "bg-success";
                    break;
            }
            if (isset($data['data']) && is_array($data['data'])) {
                $message .= "<br>";
                foreach ($data['data'] as $i) {
                    $message .= $i . "<br>";
                }
            } else {
                $message .= (isset($data['data'])) ? "<br>" . $data['data'] : "";
            }
            $response = [
                'title'     => $title,
                'autohide'  => true,
                'delay'     => 7500,
                'class'     => $color,
                'subtitle'  => $subtitle,
                'icon'      => $icon,
                'body'      => $message,
                'type'      => $tipo
            ];
            if (!empty($image)) {
                $response['image'] = $image;
                $response['imageAlt'] = $image_alt;
            }
        } else {
            $response = [
                'title'     => $title,
                'autohide'  => true,
                'delay'     => 7500,
                'class'     => $data['color'],
                'subtitle'  => ($data['subtitle']) ?? null,
                'icon'      => $data['icon'],
                'body'      => $message,
                'type'      => $data['type']
            ];
            if (isset($data['image']) && !empty($data['image'])) {
                $response['image'] = ($data['image']) ?? null;
                $response['imageAlt'] = ($data['image_alt']) ?? null;
            }
        }
        return $response;
    }
    /**
     * Creador de correos
     * @return void
     */
    public function mailBuilder()
    {
    }
    /**
     * Creador de mensajes
     * @param array|string $values Valores del mensaje
     * @return array
     */
    protected function message(array|string $values): array
    {
        $extra = "";
        if (is_array($values)) {
            if (!empty($values['type'])) {
                switch ($values['type']) {
                    case 'success':
                        $response = [
                            'name' => "Execution successful",
                            'code' => (isset($values['code'])) ? $values['code'] : "100",
                            'type' => "success",
                            'title' => "Success!"
                        ];
                        break;
                    case 'warning':
                        $response = [
                            'name' => "Execution alert",
                            'code' => (isset($values['code'])) ? $values['code'] : "204",
                            'type' => "warning",
                            'title' => "Alert!"
                        ];
                        break;
                    case 'error':
                        $response = [
                            'name' => "Execution error",
                            'code' => (isset($values['code'])) ? $values['code'] : "500",
                            'type' => "error",
                            'title' => "Error!"
                        ];
                        break;
                    default:
                        $response = [
                            'name' => "Application Notice",
                            'code' => (isset($values['code'])) ? $values['code'] : "409",
                            'type' => "info",
                            'title' => "Notice!"
                        ];
                        break;
                }
            } else {
                $response = [
                    'name' => "Execution alert",
                    'code' => "204",
                    'type' => "alert",
                    'title' => "Alert!"
                ];
            }
            if (isset($values['values']) && is_array($values['values'])) {
                foreach ($values['values'] as $i) {
                    $extra .= $i . "<br>";
                }
            } else {
                if (isset($values['values']))
                    $extra .= "<br>" . $values['values'];
            }
            if (isset($values['data']) && is_array($values['data'])) {
                foreach ($values['data'] as $i) {
                    $extra .= $i . "<br>";
                }
            } else {
                if (isset($values['data']))
                    $extra .= "<br>" . $values['data'];
            }
            $response['message'] = (isset($values['message'])) ? $values['message'] : "";
            $response['extra'] = $extra;
        } else {
            $response = [
                'name' => "Execution alert",
                'code' => "204",
                'type' => "alert",
                'title' => "Alert!"
            ];
            $response['message'] = $values;
        }
        return $response;
    }
    /**
     * Produces an error message
     * @param array|string $code
     * $values = [code, message, data, extra]
     * @return array
     */
    protected function error(array|string $values): array
    {
        $path = _CONF_ . "Errors.json";
        /* echo "<pre>";
        var_dump($values);
        echo "<pre>"; */
        $extra = "";
        $response = [];
        $errorList = json_decode(file_get_contents($path), true);
        if (is_array($values)) {
            if (isset($values['data']) && is_array($values['data'])) {
                foreach ($values['data'] as $i) {
                    $extra .= $i . "<br>";
                }
            } else {
                if (isset($values['data'])) {
                    $extra .= "<br>" . $values['data'];
                }
            }
            if (isset($values['extra']) && is_array($values['extra'])) {
                foreach ($values['extra'] as $i) {
                    $extra .= $i . "<br>";
                }
            } else {
                if (isset($values['extra'])) {
                    $extra .= "<br>" . $values['extra'];
                }
            }
            foreach ($errorList as $error => $value) {
                if (isset($values['code'])){
                if ($error == intval($values['code'])) {
                    $message = $values['message'];
                    $response = [
                        'name' => "Execution error",
                        'code' => $error,
                        'type' => ($value['type']) ?? "error",
                        'title' => ($value['title']) ?? "Error!",
                        'message' => $value['message'] . '<br>' . $message . '<br>',
                        'extra' => $extra
                    ];
                }}
            }
        } else {
            foreach ($errorList as $error => $value) {
                if ($error = $values) {
                    $response = [
                        'name' => "Execution error",
                        'code' => $error,
                        'type' => ($value['type']) ?? "error",
                        'title' => ($value['title']) ?? "Error!",
                        'message' => $value['message'],
                        'extra' => null
                    ];
                }
            }
        }
        return $response;
    }
    /**
     * Prdouces an alert message
     * @param array|string $values
     * $values = [type, message, data]
     * @return array
     */
    protected function alert(array|string $values): array
    {
        $extra = "";
        if (is_array($values)) {
            $response = [
                'name' => "Execution alert",
                'code' => ($values['code']) ?? 204,
                'type' => ($values['type']) ?? "alert",
                'title' => "Alert!",
                'message' => ($values['message']) ?? "Oops! Something went wrong.",
            ];
            if (isset($values['data']) && is_array($values['data'])) {
                foreach ($values['data'] as $i) {
                    $extra .= $i . "<br>";
                }
            } else {
                if (isset($values['data']))
                    $extra .= "<br>" . $values['data'];
            }
            $response['extra'] = $extra;
        } else {
            $response = [
                'name' => "Execution alert",
                'code' => "204",
                'type' => "alert",
                'title' => "Alert!",
                'message' => $values
            ];
        }
        return $response;
    }
}
