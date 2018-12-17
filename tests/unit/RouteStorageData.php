<?php
return [
    '/' => [
        'title' => 'Main page', 
        'module' => [
            'id' => -1, 
            'name' => 'Mainpage', 
            'dbless' => true, 
        ], 
    ], 
    '/asd' => [
        'title' => 'ASD page', 
        'module' => [
            'id' => 1, 
            'name' => 'Documents', 
            'dbless' => true, 
        ], 
        'disabled' => true, 
    ], 
    '/asd/qwe' => [
        'title' => 'ASD QWE page', 
        'module' => [
            'id' => 2, 
            'name' => 'Documents', 
            'dbless' => true, 
            'disabled' => true, 
        ], 
    ], 
    '/qwe' => [
        'title' => 'QWE page', 
        'module' => [
            'id' => 3, 
            'name' => 'Documents', 
        ], 
    ], 
    '/qwe/asd' => [
        'title' => 'QWE ASD page', 
        'module' => [
            'id' => 333, 
            'name' => 'Simple callback', 
            'callback' => function($container) {
                return 'content of callback for section ' . $container->section->path;
            }, 
            'dbless' => true, 
            'disabled' => false, 
        ], 
        'disabled' => false, 
    ], 
    '/qwe/asd/zxc' => [
        'title' => 'QWE ASD ZXC page', 
        'module' => [
            'id' => 4, 
            'name' => 'Documents', 
        ], 
    ], 
];
