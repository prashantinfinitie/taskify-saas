<?php

use Alexusmai\LaravelFileManager\Services\ConfigService\DefaultConfigRepository;
use Alexusmai\LaravelFileManager\Services\ACLService\ConfigACLRepository;

return [
    /**
     * Set Config repository - Use our custom repository for dynamic disk creation
     */
    'configRepository' => \App\Services\CustomConfigRepository::class,

    /**
     * ACL rules repository - Using default since we disabled ACL
     */
    'aclRepository' => ConfigACLRepository::class,

    /**
     * LFM Route prefix
     */
    'routePrefix' => 'file-manager',

    /**
     * Default disk list - Will be overridden by CustomConfigRepository
     */
    'diskList' => ['file-manager'],

    'leftDisk' => null,
    'rightDisk' => null,
    'leftPath' => null,
    'rightPath' => null,

    /**
     * Image cache
     */
    'cache' => 60,

    /**
     * File manager windows configuration
     */
    'windowsConfig' => 2,

    'maxUploadFileSize' => null,
    'allowFileTypes' => [],
    'hiddenFiles' => false,

    /**
     * Middleware - Ensure only authenticated users can access
     */
    'middleware' => ['web', 'auth'],

    /**
     * ACL mechanism - DISABLED for disk-level isolation
     */
    'acl' => false,
    'aclHideFromFM' => false,
    'aclStrategy' => 'blacklist',
    'aclRulesCache' => null,

    'slugifyNames' => false,

    /**
     * ACL rules - Not used since ACL is disabled
     */
    'aclRules' => [],
];