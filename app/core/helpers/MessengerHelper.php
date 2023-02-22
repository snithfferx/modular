<?php

namespace app\core\helpers;

use Error;

/**
 * Clase para el envio de mensajes
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @version 1.0.0
 */
class MessengerHelper
{
    public function build(string $type, array $values)
    {
        switch ($type) {
            case 'message':
                $result = $this->message($values);
                break;
            case 'error':
                $result = $this->error($values['code']);
                $result['data'] = $values['message'];
                break;
        }
        return $result;
    }
    /**
     * Crea vista de mensaje
     * @param string $type
     * @param array $data ['message','title','subtitle','image','image_alt','icon','type','data']
     * @param string $driver
     *
     * @return array
     */
    public function messageBuilder(string $type, array $data, string $driver = 'view')
    {
        $message = (isset($data['message'])) ? $data['message'] : "Ha ocurrido un error al ejecutar la consulta solicitada.";
        $title = (isset($data['title'])) ? $data['title'] : "ERROR";
        if ($type == "message") {
            $errorType = (isset($data['type'])) ? $data['type'] : "other";
            switch ($errorType) {
                case 'error':
                    $errorTypo = [
                        'color' => "text-danger",
                        'name' => "Alerta de ejecuci&oacute;n",
                        'code' => (isset($data['code'])) ? $data['code'] : "404",
                        'icon' => "fas fa-exclamation-triangle"
                    ];
                    $titleColor = "danger";
                    $titleIcon = "ban";
                    break;
                case 'warning':
                    $errorTypo = [
                        'color' => "text-warning",
                        'name' => "Alerta de ejecuci&oacute;n",
                        'code' => (isset($data['code'])) ? $data['code'] : "204",
                        'icon' => "fas fa-exclamation-circle"
                    ];
                    $titleColor = "warning";
                    $titleIcon = "exclamation-triangle";
                    break;
                case 'info':
                    $errorTypo = [
                        'color' => "text-info",
                        'name' => "Aviso de ejecuci&oacute;n",
                        'code' => (isset($data['code'])) ? $data['code'] : "100",
                        'icon' => "fas fa-info-circle"
                    ];
                    $titleColor = "muted";
                    $titleIcon = "info";
                    break;
                case 'success':
                    $errorTypo = [
                        'color' => "text-success",
                        'name' => "Aviso de ejecuci&oacute;n",
                        'code' => 200,
                        'icon' => "fas fa-check"
                    ];
                    $titleColor = "success";
                    $titleIcon = "check-circle";
                    break;
                default:
                    $errorTypo = [
                        'color' => "text-primary",
                        'name' => "Aviso",
                        'code' => (isset($data['code'])) ? $data['code'] : "409",
                        'icon' => "fas fa-info"
                    ];
                    $titleColor = "primary";
                    $titleIcon = "exclamation-circle";
                    break;
            }
            $extra = "";
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $i => $v) {
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
                if (isset($data['data']) && !is_null($data['data']) && !empty($data['data'])) $extra .= $data['data'];
            }
            if ($driver == "view") {
                $response = [
                    'breadcrumb' => [
                        'main' => "ERROR Page " . $errorTypo['code'],
                        'routes' => [
                            ['controller' => "Errors", 'method' => "index", 'params' => "", 'text' => "Errors"],
                            ['controller' => "Errors", 'method' => "detalles", 'params' => $errorTypo['code'], 'text' => $errorTypo['code'] . "|" . $title]
                        ]
                    ],
                    'content'    => [
                        'title'  => [
                            'color' => $titleColor,
                            'text' => $title,
                            'icon' => $titleIcon
                        ],
                        'header' => $errorTypo,
                        'mensaje' => $message,
                        'extra'  => $extra
                    ]
                ];
            } elseif ($driver == "band") {
                $response = [
                    'layout'    => [
                        'title' => $errorTypo['code'],
                        'type'  => $errorTypo['color'],
                        'ico'   => $errorTypo['icon']
                    ],
                    'mensaje'   => [
                        'type'   => $titleColor,
                        'header' => [
                            'title' => $title,
                            'icon'  => $titleIcon
                        ],
                        'text'   => $message,
                        'extra'  => $extra
                    ]
                ];
            } else {
                $response = ['text' => $message, 'color' => $titleColor, 'icon' => $titleIcon];
            }
        } elseif ($type == "alert") {
            $subtitle = (isset($data['subtitle'])) ? $data['subtitle'] : "";
            $image = (isset($data['image'])) ? $data['image'] : "";
            $image_alt = (isset($data['image_alt'])) ? $data['image_alt'] : "";
            $icon = (isset($data['icon'])) ? "fas fa-$data[icon]" : "fas fa-info-circle";
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
    public function mailBuilder()
    {
    }
    protected function message($values)
    {
        $extra = "";
        if (!empty($values['type'])) {
            switch ($values['type']) {
                case 'info':
                    $response = [
                        'color' => "text-info",
                        'name' => "Aviso de ejecuci&oacute;n",
                        'code' => (isset($values['code'])) ? $values['code'] : "100",
                        'icon' => "fas fa-info-circle"
                    ];
                    break;
                case 'warning':
                    $response = [
                        'color' => "text-warning",
                        'name' => "Alerta de ejecuci&oacute;n",
                        'code' => (isset($values['code'])) ? $values['code'] : "204",
                        'icon' => "fas fa-exclamation-circle"
                    ];
                    break;
                case 'error':
                    $response = [
                        'color' => "text-danger",
                        'name' => "Alerta de ejecuci&oacute;n",
                        'code' => (isset($values['code'])) ? $values['code'] : "404",
                        'icon' => "fas fa-exclamation-triangle"
                    ];
                    break;
                default:
                    $response = [
                        'color' => "text-primary",
                        'name' => "Aviso",
                        'code' => (isset($values['code'])) ? $values['code'] : "409",
                        'icon' => "fas fa-info"
                    ];
                    break;
            }
        } else {
            $response = ['color' => "text-danger", 'name' => "Error de ejecuci&oacute;n", 'code' => "10-500", 'icon' => "fas fa-exclamation-triangle"];
            $extra = "El servidor a retornado un error en ejecución.<br>Verifique la información he intente nuevamente.";
        }
        if (isset($values['values']) && is_array($values['values'])) {
            foreach ($values['values'] as $i) {
                $extra .= $i . "<br>";
            }
        } else {
            if (isset($values['values'])) $extra .= "<br>" . $values['values'];
        }
        $response['mensaje'] = (isset($values['mensaje'])) ? $values['mensaje'] : "Ha ocurrido un error al realizar su consulta.";
        $response['extra']   = $extra;
        return $response;
    }
    protected function error($code)
    {
        $path = _HELPER_ . "Errors.json";
        $errorList = json_decode(file_get_contents($path), true);
        foreach ($errorList as $error => $values) {
            if ($error = $code) {
                return $values;
            }
        }
    }
}
