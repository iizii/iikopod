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
    echo -e "\033[32mStop all queue workers\033[0m"
    pgrep -f "php artisan queue:work" | xargs -r kill

@for($i = 0; $i < 5; $i++)
    echo -e "\033[32mStarting queue worker\033[0m"
    nohup php artisan queue:work --queue=integrations -v > /dev/null 2>&1 &
@endfor
@endtask

