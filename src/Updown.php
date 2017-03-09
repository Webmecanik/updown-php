<?php
namespace Foinikas\Updown;

class Updown
{

    const BASE_URL = 'https://updown.io/api/checks';

    /**
     *
     * @var string User's API KEY.
     */
    private $api_key;

    /**
     *
     * @param
     *            $api_key
     */
    public function __construct($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * List all your checks
     *
     * @return mixed
     */
    public function checks()
    {
        $url = self::BASE_URL;
        
        return $this->_getRequest($url, 'GET');
    }

    /**
     * Show a single project's checks
     *
     * @param
     *            $token
     * @return mixed
     */
    public function check($token)
    {
        $url = self::BASE_URL . '/' . $token;
        
        return $this->_getRequest($url, 'GET');
    }

    /**
     * Give tocken of given URL
     *
     * @param string $url            
     * @return string | null
     */
    public function getToken($url)
    {
        $checks = json_decode($this->checks(), true);
        
        foreach ($checks as $check) {
            if (strpos($check['url'], $url, 0) !== false) {
                return $check['token'];
            }
        }
        return null;
    }

    /**
     *
     * @param string $url            
     * @param array $option            
     * @return boolean
     */
    public function addCheck($url, $option = array())
    {
        $param = array_merge(array(
            'url' => $url
        ), $option);
        
        $return = json_decode($this->_getRequest(self::BASE_URL, 'POST', $param), true);
        
        if ($return === false || isset($return['error'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * delete check of given URL
     *
     * @param string $url            
     * @return boolean
     */
    public function deleteCheck($url)
    {
        $token = $this->getToken($url);
        if (is_null($token)) {
            return false;
        }
        
        $apiUrl = self::BASE_URL . '/' . $token;
        
        $return = json_decode($this->_getRequest($apiUrl, 'DELETE'), true);
        if ($return !== false && isset($return['deleted']) && $return['deleted'] === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $url            
     * @param array $param            
     * @return boolean
     */
    public function updateCheck($url, $param)
    {
        $token = $this->getToken($url);
        if (is_null($token)) {
            return false;
        }
        
        $apiUrl = self::BASE_URL . '/' . $token;
        
        $return = json_decode($this->_getRequest($apiUrl, 'PUT', $param), true);
        if ($return === false && isset($return['error'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * @param string $url            
     * @return boolean
     */
    public function pauseCheck($url)
    {
        return $this->updateCheck($url, array(
            'enabled' => false
        ));
    }

    /**
     *
     * @param string $url            
     * @return boolean
     */
    public function activeCheck($url)
    {
        return $this->updateCheck($url, array(
            'enabled' => true
        ));
    }

    /**
     * Get all the downtimes of a check
     *
     * @param
     *            $token
     * @param array $params            
     * @return mixed
     */
    public function downtimes($token, $params = [])
    {
        $url = self::BASE_URL . '/' . $token . '/downtimes';
        
        return $this->_getRequest($url, 'GET', $params);
    }

    /**
     * Get detailed metrics about the check
     *
     * @param
     *            $token
     * @param array $params            
     * @return mixed
     */
    public function metrics($token, $params = [])
    {
        $url = self::BASE_URL . '/' . $token . '/metrics';
        
        return $this->_getRequest($url, 'GET', $params);
    }

    /**
     * Make GET Request with CURL
     *
     * @param string $url            
     * @param string $protocol            
     * @param array $params            
     * @return mixed
     */
    private function _getRequest($url, $protocol, $params = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'X-API-KEY: ' . $this->api_key
        ));
        // $params['api-key']=$this->api_key;
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        if ($protocol === 'GET') {
            curl_setopt($curl, CURLOPT_URL, empty($params) ? $url : $url . '?' . http_build_query($params));
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $protocol);
        }
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }
}