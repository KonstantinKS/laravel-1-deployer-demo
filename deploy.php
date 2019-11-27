<?php

namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'my_project');

// Project repository
set('repository', 'git@github.com:KonstantinKS/laravel-1-deployer-demo.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_dirs', [
    'storage',
]);
add('shared_files', [
    '.env'
]);

// Writable dirs by web server
add('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);

// Hosts

host('127.0.0.1')
    ->stage('production')
    ->user('deployer')
    ->set('deploy_path', '/sites/laravel-1-deployer-demo/www');

// Tasks

desc('Restart PHP-FPM service');
task('php-fpm:restart', function () {
    // The user must have rights for restart service
    // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl restart php-fpm.service
    run('sudo systemctl restart php7.3-fpm.service');
});
after('deploy:symlink', 'php-fpm:restart');

task('upload:env', function () {
    upload('.env.production', '{{deploy_path}}/shared/.env');
})->desc('Environment setup');

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

// before('deploy:symlink', 'artisan:migrate');

task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'upload:env',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:view:clear',
    'artisan:cache:clear',
    'artisan:config:cache',
//    'artisan:migrate',
    'deploy:symlink',
    'php-fpm:restart',
    'deploy:unlock',
    'cleanup',
]);

