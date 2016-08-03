<?php
$config = require_once 'config.php';

try
{
    $payload = json_decode($_POST['payload']);
}
catch(Exception $e)
{
    exit(0);
}

//log the request
file_put_contents('github_vision.log', print_r($payload, TRUE), FILE_APPEND);


/*if ($payload->ref === 'refs/heads/master')
{
    # path to your site deployment script
    $output = shell_exec('/usr/local/bin/github_visionigniter');
    file_put_contents('github.log', print_r($output, TRUE), FILE_APPEND);
}*/