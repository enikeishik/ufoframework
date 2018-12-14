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
            'dbless' => true, 
        ], 
        'disabled' => true, 
    ], 
    '/asd/qwe' => [
        'module' => [
            'id' => 2, 
            'name' => 'Documents', 
            'callback' => '', 
            'dbless' => true, 
            'disabled' => true, 
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
                return 'content of callback for section ' . $container->section->path;
            }, 
            'dbless' => true, 
            'disabled' => false, 
        ], 
        'disabled' => false, 
    ], 
    '/qwe/asd/zxc' => [
        'module' => [
            'id' => 4, 
            'name' => 'Documents', 
            'callback' => '', 
        ], 
    ], 
];
