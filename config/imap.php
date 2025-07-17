<?php

return [
    'host' => env('IMAP_HOST', 'imap.kinghost.net'),
    'port' => env('IMAP_PORT', 143),
    'username' => env('IMAP_USERNAME', 'intimacoes@previdia.com'),
    'password' => env('IMAP_PASSWORD', 'Nova365@'),
    'protocol' => env('IMAP_PROTOCOL', 'imap'),
    'encryption' => env('IMAP_ENCRYPTION', 'tls'),
    'validate_cert' => env('IMAP_VALIDATE_CERT', false),
]; 