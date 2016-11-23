<?php

/**
 * Copyright (c) 2016, Pustak Sadan
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Pustak Sadan <pustaksadan.india@gmail.com>
 * @package coverinfo.classes
 */
class WEBAPI_CLASS_GroupsApiBridge
{
    const PLUGIN_KEY = 'groups';
    
    private $service;

    /**
     * Class instance
     *
     * @var COVERINFO_CLASS_PhotoBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return COVERINFO_CLASS_PhotoBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {
        $this->service = GROUPS_BOL_Service::getInstance();
    }
    
    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive(self::PLUGIN_KEY);
    }
    
    
    public function init()
    {
        if ( !$this->isActive() )
        {
            return;
        }
    }
}