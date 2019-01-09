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
    '/section-disabled' => [
        'title' => 'ASD page', 
        'module' => [
            'id' => 1, 
            'name' => 'Documents', 
            'dbless' => true, 
        ], 
        'disabled' => true, 
    ], 
    '/module/disabled' => [
        'title' => 'ASD QWE page', 
        'module' => [
            'id' => 2, 
            'name' => 'Documents', 
            'dbless' => true, 
            'disabled' => true, 
        ], 
    ], 
    '/document' => [
        'title' => 'Document page', 
        'module' => [
            'id' => 3, 
            'name' => 'Documents', 
            'dbless' => true, 
        ], 
    ], 
    '/section-with/callback' => [
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
    '/some/another/document' => [
        'title' => 'QWE ASD ZXC page', 
        'module' => [
            'id' => 4, 
            'name' => 'Documents', 
            'dbless' => true, 
        ], 
    ], 
];
