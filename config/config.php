<?php

$routes = [
    'home'       => 'default:home',
    'register'   => 'user:register',
    'login'      => 'user:login',
    'logout'     => 'user:logout',
    'upload'     => 'file:upload',
    'download'   => 'file:download',
    'rename'     => 'file:rename',
    'replace'    => 'file:replace',
    'remove'     => 'file:remove',
    'add_folder' => 'file:addFolder',
    'move'       => 'file:move',
    'show'       => 'file:show',
    'write'      => 'file:write',
    'open'       => 'nav:open',
    'to_parent'  => 'nav:toParent',
];

require('config/private.php');