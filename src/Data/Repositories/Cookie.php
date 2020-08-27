<?php

namespace PragmaRX\Tracker\Data\Repositories;

use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use PragmaRX\Support\Config;
use Ramsey\Uuid\Uuid as UUID;

class Cookie extends Repository
{
    private $config;

    private $request;

    private $cookieJar;

    public function __construct($model, Config $config, Request $request, CookieJar $cookieJar)
    {
        $this->config = $config;

        $this->request = $request;

        $this->cookieJar = $cookieJar;

        parent::__construct($model);
    }

    public function getId()
    {
        if (!$this->config->get('store_cookie_tracker')) {
            return;
        }
        
        $cookie = (string) UUID::uuid4();
        if ($this->request->cookie($this->config->get('tracker_cookie_name'))) {
            $this->cookieJar->queue($this->config->get('tracker_cookie_name'), $cookie, 0);
            $tracker_cookie = $this->where('uuid','LIKE',$cookie)->first();
        }
        
        if(isset($tracker_cookie) && $tracker_cookie->result) {
            return $tracker_cookie->result->id;
        } else {
            return $this->create(['uuid' => $cookie])->id;
        }
    }
}
