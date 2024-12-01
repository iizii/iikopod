@servers(['web' => 'root@212.22.85.35'])

@task('deploy', ['on' => 'web'])
    cd /var/www/communicationModule
    echo -e "\033[32mFetching project from GIT\033[0m"
    git fetch --all
    git reset --hard origin/main
    echo -e "\033[32mInstall composer packages\033[0m"
    export COMPOSER_ALLOW_SUPERUSER=1
    composer install --no-dev --optimize-autoloader
    echo -e "\033[32mClear application caches\033[0m"
    composer optimize:clear
    echo -e "\033[32mOptimize application\033[0m"
    composer optimize
    echo -e "\033[32mMigrate database\033[0m"
    php artisan migrate --force
@endtask

