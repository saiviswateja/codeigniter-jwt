<?php
// Store your secret key here
// Make sure you use better, long, more random key than this
$config['key'] = "secretkey"; // secret key
$config['iss'] = "http://localhost/codeigniter-jwt/"; // domain name
$config['aud'] = "http://localhost/codeigniter-jwt/"; // domain name
$config['iat'] = time(); // current time
$config['nbf'] = $config['iat'] + 30; // not using before 30 sec
$config['exp'] = $config['iat'] + 1*60; // valid for 1 min after generate