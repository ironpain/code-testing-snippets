<?php

/**
 * HttpHelper for response
 */
class HttpHelper
{
    #region getter/ setter
    private int $_status_code = 200;

    public function setStatusCode(int $value)
    {
        $this->_status_code = $value;
    }

    public function getStatusCode()
    {
        return $this->_status_code;
    }
    #endregion

    /**
     * HttpHelper
     */
    public function __construct()
    {
        if (php_sapi_name() === 'cli') {
            throw new Exception('This Class is not for CLI usage' . PHP_EOL);
        }
    }

    public function __destruct()
    {
    }

    /**
     * Wrapper for http_response_code
     *
     * @link https://www.php.net/manual/en/function.http-response-code.php http_response_code
     * @param integer $code
     * @return int|boolean
     */
    public function httpStatusCode(int $code): int | bool
    {
        if (!function_exists('http_response_code')) {
            function http_response_code(int|null $code = null)
            {
                $prev_code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

                if ($code === null) {
                    return $prev_code;
                }

                switch ($code) {
                    case 100:
                        $text = 'Continue';
                        break;
                    case 101:
                        $text = 'Switching Protocols';
                        break;
                    case 200:
                        $text = 'OK';
                        break;
                    case 201:
                        $text = 'Created';
                        break;
                    case 202:
                        $text = 'Accepted';
                        break;
                    case 203:
                        $text = 'Non-Authoritative Information';
                        break;
                    case 204:
                        $text = 'No Content';
                        break;
                    case 205:
                        $text = 'Reset Content';
                        break;
                    case 206:
                        $text = 'Partial Content';
                        break;
                    case 300:
                        $text = 'Multiple Choices';
                        break;
                    case 301:
                        $text = 'Moved Permanently';
                        break;
                    case 302:
                        $text = 'Moved Temporarily';
                        break;
                    case 303:
                        $text = 'See Other';
                        break;
                    case 304:
                        $text = 'Not Modified';
                        break;
                    case 305:
                        $text = 'Use Proxy';
                        break;
                    case 400:
                        $text = 'Bad Request';
                        break;
                    case 401:
                        $text = 'Unauthorized';
                        break;
                    case 402:
                        $text = 'Payment Required';
                        break;
                    case 403:
                        $text = 'Forbidden';
                        break;
                    case 404:
                        $text = 'Not Found';
                        break;
                    case 405:
                        $text = 'Method Not Allowed';
                        break;
                    case 406:
                        $text = 'Not Acceptable';
                        break;
                    case 407:
                        $text = 'Proxy Authentication Required';
                        break;
                    case 408:
                        $text = 'Request Time-out';
                        break;
                    case 409:
                        $text = 'Conflict';
                        break;
                    case 410:
                        $text = 'Gone';
                        break;
                    case 411:
                        $text = 'Length Required';
                        break;
                    case 412:
                        $text = 'Precondition Failed';
                        break;
                    case 413:
                        $text = 'Request Entity Too Large';
                        break;
                    case 414:
                        $text = 'Request-URI Too Large';
                        break;
                    case 415:
                        $text = 'Unsupported Media Type';
                        break;
                    case 500:
                        $text = 'Internal Server Error';
                        break;
                    case 501:
                        $text = 'Not Implemented';
                        break;
                    case 502:
                        $text = 'Bad Gateway';
                        break;
                    case 503:
                        $text = 'Service Unavailable';
                        break;
                    case 504:
                        $text = 'Gateway Time-out';
                        break;
                    case 505:
                        $text = 'HTTP Version not supported';
                        break;
                    default:
                        trigger_error('Unknown http status code ' . $code, E_USER_ERROR); // exit('Unknown http status code "' . htmlentities($code) . '"');
                        return $prev_code;
                }

                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                header($protocol . ' ' . $code . ' ' . $text);
                $GLOBALS['http_response_code'] = $code;

                // original function always returns the previous or current code
                return $prev_code;
            }
        }

        return http_response_code($code);
    }

    /**
     * Prints AJAX JSON 'Content-Type: application/json'
     *
     * @param   mixed  $data   PHP var content to print
     * @return  void
     */
    public function jsonAJAXResponse(mixed $data)
    {
        $json_data = json_encode($data);

        $this->_sendResponse($json_data);
        unset($json_data, $data);
        return;
    }

    /**
     * Prints AJAX html 'Content-Type: text/html'
     *
     * @param   string  $html   HTML content to print
     * @return  void
     */
    public function htmlAJAXResponse(string $html)
    {
        $this->_sendResponse($html, 'text/html');
        unset($html);
        return;
    }

    /**
     * Prints AJAX text 'Content-Type: text/plain'
     *
     * @param   string  $text   Text conten to print
     * @return  void
     */
    public function textAJAXResponse(string $text): void
    {
        $this->_sendResponse($text, 'text/plain');
        unset($text);
        return;
    }

    private function _sendResponse(string $data, string $contentType = 'application/json', string $charset = 'utf-8'): void
    {
        $content_lenght = strlen($data);
        header_remove();
        $this->httpStatusCode($this->_status_code);
        header('Content-Type: ' . $contentType . '; charset:' . $charset);
        header('Content-Length: ' . $content_lenght);
        echo $data;
        unset($data, $content_lenght);
        return;
    }
}
