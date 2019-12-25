<?php

namespace Denis303\CodeIgniter\NotRememberMe;

use Config\App;
use Config\Services;
use Exception;

abstract class BaseCookie
{

    public $name;

    public $secure = false; // Whether to only send the cookie through HTTPS

    public $httpOnly = false; // Whether to hide the cookie from JavaScript

    public $cookieDomain;

    public $cookiePath;

    public $cookiePrefix;

    public function __construct($name)
    {
        $this->name = $name;

        $config = config(App::class);

        if (!$config)
        {
            throw new Exception('Config not found.');
        }

        $this->cookieDomain = $config->cookieDomain;

        $this->cookiePath = $config->cookiePath;

        $this->cookiePrefix = $config->cookiePrefix;
    }

    public function generateToken()
    {
        return md5(time() . rand(0, PHP_INT_MAX)); 
    }

    public function getCookie()
    {
        helper(['cookie']);

        return get_cookie($this->name);
    }

    /**
     *  Set "not remember me" cookie
     *
     *  Not working in Chrome, where:
     *
     *  1. On Startup = Continue where you left off
     *  2. Continue running background apps when Google Chrome is closed = On
     *
     */
    public function setCookie($token = null)
    {
        helper(['cookie']);

        return return set_cookie(
            $this->name,
            $token,
            0,
            $this->cookieDomain,
            $this->cookiePath,
            $this->cookiePrefix,
            $this->secure, // only send over HTTPS
            $this->httpOnly // hide from Javascript
        );
    }

    public function deleteCookie(bool $deleteSession = true)
    {
        helper(['cookie']);

        return delete_cookie(
            $this->name, 
            $this->cookieDomain, 
            $this->cookiePath, 
            $this->cookiePrefix
        );
    }

    public function setToken($token = null)
    {
        if (!$token)
        {
            $token = $this->generateToken();
        }

        $this->setSession($token);

        $this->setCookie($token);
    }

    public function validateToken()
    {
        $token = $this->getSession();

        if ($token)
        {
            $cookie = $this->getCookie();
        
            if ($cookie == $token)
            {
                return true;
            }
            else
            {
                $this->deleteCookie();

                $this->deleteSession();

                return false;
            }
        }

        return true;
    }

    public function getSession()
    {
        $session = Services::session();

        return $session->get($this->name);
    }

    public function setSession(string $token)
    {
        $session = Services::session();

        return $session->set($this->name, $token);
    }

    public function deleteSession()
    {
        $session = Services::session();

        return $session->remove($this->name);
    }

}