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
file_put_contents('github_hook.log', print_r($payload, TRUE), FILE_APPEND);

if ($config['env']==='dev'){
    // process push event
    if($payload->ref === 'refs/heads/master'){
        $repoConf = findRepoConf($payload->repository->name);
        if(!$repoConf){
            exit(0);
        }

        $output = shell_exec('./pull.sh '.$repoConf['path']);
        file_put_contents('github_pull.log', print_r($output, TRUE), FILE_APPEND);
    }
} else {
    // process release event
    if($payload->action=='published'){
        $repoConf = findRepoConf($payload->repository->name);
        if(in_array($payload->release->author->login, $repoConf['allowed_users'])){
            $output = shell_exec('./pull.sh '.$repoConf['path']);
            file_put_contents('github_pull.log', print_r($output, TRUE), FILE_APPEND);
        }
    }
}

/*if ($payload->ref === 'refs/heads/master')
{
    # path to your site deployment script
    $output = shell_exec('/usr/local/bin/github_visionigniter');
    file_put_contents('github.log', print_r($output, TRUE), FILE_APPEND);
}*/

function findRepoConf($repoName){
    foreach($config['repos'] as $repo){
        if($repo['name']===$repoName){
            return $repo;
        }
    }
    return false;
}