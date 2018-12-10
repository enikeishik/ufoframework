<?php
return [
    '/' => [
        'module' => [
            'id' => -1, 
            'name' => 'Mainpage', 
            'callback' => '', 
            'dbless' => true, 
        ], 
    ], 
    '/asd' => [
        'module' => [
            'id' => 1, 
            'name' => 'Documents', 
            'callback' => '', 
        ], 
    ], 
    '/asd/qwe' => [
        'module' => [
            'id' => 2, 
            'name' => 'Documents', 
            'callback' => '', 
        ], 
    ], 
    '/qwe' => [
        'module' => [
            'id' => 3, 
            'name' => 'Documents', 
            'callback' => '', 
        ], 
    ], 
    '/qwe/asd' => [
        'module' => [
            'id' => 333, 
            'name' => 'ASD qwe', 
            'callback' => function($container) {
                return new \Ufo\Core\Result('content of callback for section ' . $container->section->path);
            }, 
            'dbless' => true, 
        ], 
    ], 
    '/qwe/asd/zxc' => [
        'module' => [
            'id' => 4, 
            'name' => 'Documents', 
            'callback' => '', 
        ], 
    ], 
];
