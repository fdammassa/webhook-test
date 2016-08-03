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

$isRelease = isRelease($payload);
$repoConf = findRepoConf($config['repos'], $payload->repository->name, $isRelease);
if(!$repoConf){
    exit(0);
}

if ($repoConf['env']==='dev'){
    // process push event
    if($payload->ref === 'refs/heads/master'){
        $output = shell_exec('./pull.sh '.$repoConf['path']);
        file_put_contents('github_pull.log', print_r($output, TRUE), FILE_APPEND);
    }
} else if($repoConf['env']==='prod') {
    // process release event
    if($isRelease){
        if(in_array($payload->release->author->login, $repoConf['allowed_users'])){
            $output = shell_exec('./pull.sh '.$repoConf['path']);
            file_put_contents('github_pull.log', print_r($output, TRUE), FILE_APPEND);
        }
    }
}

function findRepoConf($repos, $repoName, $isRelease = false){
    foreach($repos as $repo){
        if($repo['name']===$repoName){
            if($isRelease && $repo['env']=='prod')
                return $repo;

            if(!$isRelease && $repo['env']=='dev')
                return $repo;
        }
    }
    return false;
}

function isRelease($payload){
    if(isset($payload->action) && $payload->action=='published')
        return true;
    else
        return false;
}