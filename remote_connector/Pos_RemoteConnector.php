<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 5/30/15
 * Time: 2:02 PM
 */

class Pos_RemoteConnector
{
    protected $_url;
    protected $_remoteFile;
    protected $_error;
    protected $_status;
    protected $_urlParts;

    public function __construct($url)
    {
        $this->_url = $url;
        $this->checkURL();
        if (ini_get('allow_url_fopen')) {
            $this->accessDirect();
        } elseif (function_exists('curl_init')) {
            $this->useCurl();
        }
        else {
            $this->useSocket();
        }
    }

    public function __toString()
    {
        if (!$this->_remoteFile) {
            $this->_remoteFile = '';
        }
        return $this->_remoteFile;
    }

    public function getErrorMessage()
    {
        if (!is_null($this->_error)) {
            $this->setErrorMessage();
        }
        return $this->_error;
    }

    protected function checkURL()
    {
        $flags = FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED;
        $urlOK = filter_var($this->_url, FILTER_VALIDATE_URL, $flags);
        $this->_urlParts = parse_url($this->_url);
        $domainOK = preg_match('/^[^.]+?\.\w{2}/', $this->_urlParts['host']);
        if (!$urlOK || $this->_urlParts['scheme'] != 'http' || !$domainOK) {
            throw new Exception($this->_url . ' is not a valid URL ');
        }
    }

    protected function accessDirect()
    {
        echo 'Im accessing direct. <br>';
        $this->_remoteFile = @ file_get_contents($this->_url);
        $headers = @ get_headers($this->_url);
        if ($headers) {
            preg_match('/\d{3}/', $headers[0], $m);
            $this->_status = $m[0];
        }
    }

    protected function useCurl()
    {
        echo 'Im using cURL. <br>';
        if ($session = curl_init($this->_url)) {
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            $this->_remoteFile = curl_exec($session);
            $this->_status = curl_getinfo($session, CURLINFO_HTTP_CODE);
            curl_close($session);
        } else {
            $this->_error = 'Cannot establish cURL session.';
        }
    }

    protected function useSocket()
    {
        $port = isset($this->_urlParts['port']) ? $this->_urlParts['port'] : 80;
        $remote = @ fsockopen($this->_urlParts['host'], $port, $errno, $errstr, 30);
        if (!$remote) {
            $this->_remoteFile = false;
            $this->_error = "Couldn't create a socket connection: ";
            if ($errstr) {
                $this->_error .= $errstr;
            } else {
                $this->_error .= 'check the domain name or IP address.';
            }
        } else {
            // Add the query string to the path, if it exists
            if (isset($this->_urlParts['query'])) {
                $path = $this->_urlParts['path'] . '?' . $this->_urlParts['query'];
            } else {
                $path = $this->_urlParts['path'];
            }
            // Create the request headers
            $out = "GET $path HTTP/1.1\r\n";
            $out .= "Host: {$this->_urlParts['host']}\r\n";
            $out .= "Connection: Close\r\n\r\n";
            // Send the headers
            fwrite($remote, $out);
            // Capture the response
            $this->_remoteFile = stream_get_contents($remote);
            fclose($remote);
            if ($this->_remoteFile) {
                $this->removeHeaders();
            }
            if ($this->_error) {
                throw new Exception($this->getErrorMessage());
            }
        }
    }


    protected function removeHeaders()
    {
        $parts = preg_split('#\r\n\r\n|\n\n#', $this->_remoteFile);
        if (is_array($parts)) {
            $headers = array_shift($parts);
            $file = implode("\n\n", $parts);
            if (preg_match('#HTTP/1\.\d\s+(\d{3})#', $headers, $m)) {
                $this->_status = $m[1];
            }
            if (preg_match('#Content-Type: ([^\r\n]+)#', $headers, $m)) {
                if (strpos($m[1], 'xml') == false || strpos($m[1], 'html' !== false)) {
                    if (preg_match('/<.+>/s', $file, $m)) {
                        $this->_remoteFile = $m[0];
                    } else {
                        $this->_remoteFile = trim($file);
                    }
                } else {
                    $this->_remoteFile = trim($file);
                }
            }
        }
    }

    protected function setErrorMessage()
    {
        if ($this->_status == 200 && $this->_remoteFile) {
            $this->_error = '';
        } else {
            switch ($this->_status) {
                case 200:
                case 204:
                    $this->_error = 'Connection OK, but file is empty.';
                    break;
                case 301:
                case 302:
                case 303:
                case 307:
                case 410:
                    $this->_error = 'File gas been moved or does not exist.';
                    break;
                case 305:
                    $this->_error = 'File mus be accessed through a proxy.';
                    break;
                case 400:
                    $this->_error = 'Malformed request.';
                    break;
                case 401:
                case 403:
                    $this->_error = 'You are not authorized to access this page.';
                    break;
                case 404:
                    $this->_error = 'File not found.';
                    break;
                case 407:
                    $this->_error = 'Proxy requires authentication.';
                    break;
                case 408:
                    $this->_error = 'Request time out.';
                    break;
                case 500:
                    $this->_error = 'The remonte server encountered an internal error.';
                    break;
                case 503:
                    $this->_error = 'The server cannot handle the request at the moment.';
                    break;
                default:
                    $this->error = 'Undefined error. Check URL and domain name.';
                    break;
            }
        }
    }

    public function getStatus() {
        return $this->_status;
    }

    public function getUrlParts() {
        return $this->_urlParts;
    }

    public function __destruct() {
        $error_msg = $this->getErrorMessage();
        echo $error_msg;
    }
}