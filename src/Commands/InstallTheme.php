<?php

namespace Avnsh1111\ThemeInstaller\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class InstallTheme extends Command
{
    protected $signature = 'theme:install {name}';

    protected $description = 'Install the specified admin theme';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Installing theme...');

        // Get the theme name and theme path
        $themeName = $this->argument('name');
        $this->info("You selected : '{$themeName}'");

        // Current Dir
        $currentDir =__DIR__;

        $this->info("Copying controllers...");
        // Copy Controllers
        $controllerFolder = $currentDir."/../../Themes/Common/Controllers";
        $controllerDestinationFolder = app_path().'/Http/Controllers';
        $this->copyFolder($controllerFolder, $controllerDestinationFolder);
        $this->info("Controller copied successfully.");

        $this->info("Copying middlewares...");
        // Copy Middleware
        $middlewareFolder = $currentDir."/../../Themes/Common/Middleware";
        $middlewareDestinationFolder = app_path().'/Http/Middleware';
        $this->copyFolder($middlewareFolder, $middlewareDestinationFolder);
        $this->info("Middlewares copied successfully.");

        $this->info("Copying migrations...");
        // Copy Migrations
        $migrationFolder = $currentDir."/../../Themes/Common/database/migrations";
        $destinationMigrationFolder = app_path().'/../database/migrations';
        $this->copyFolder($migrationFolder, $destinationMigrationFolder);
        $this->info("Migrations copied successfully.");


        $this->info("Copying Seeder...");
        // Copy Seeder
        $seedersFolder = $currentDir."/../../Themes/Common/database/seeders";
        $destinationSeedersFolder = app_path().'/../database/seeders';
        $this->copyFolder($seedersFolder, $destinationSeedersFolder);
        $this->info("Seeders copied successfully.");

        $this->info("Copying Assets...");
        // Copy Assets
        $assetsFolder = $currentDir."/../../Themes/".$themeName."/public";
        $assetsDestinationFolder = app_path().'/../public';
        $this->copyFolder($assetsFolder, $assetsDestinationFolder);
        $this->info("Assets copied successfully.");

        $this->info("Copying Views...");
        // Copy Views
        $viewsFolder = $currentDir."/../../Themes/".$themeName."/views";
        $viewDestinationFolder = app_path().'/../resources/views';
        $this->copyFolder($viewsFolder, $viewDestinationFolder);
        $this->info("Views copied successfully.");


        $this->updateMiddleware();
        $this->addNewGuard('admin','session','users');
        $this->addRoutes();

        Artisan::call('migrate');
        Artisan::call('db:seed --class=AdminSeeder');

        // Display a message to inform the user that the theme was installed successfully
        $this->info("Theme '{$themeName}' has been installed successfully.");
    }

    private function copyFolder($src, $dst) {

        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyFolder($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    private function updateMiddleware() {

        // Path to the app/Http/Kernel.php file
        $kernelFilePath = app_path().'/Http/Kernel.php';

        // Read the contents of the kernel.php file
        $kernelContent = file_get_contents($kernelFilePath);

        // The custom middlewares alias and class you want to add as an array
        $customMiddlewares = [
            [
                'alias' => 'admin.auth',
                'class' => '\\App\\Http\\Middleware\\Admin\\AdminMiddleware::class',
            ],
            [
                'alias' => 'admin.guest',
                'class' => '\\App\\Http\\Middleware\\Admin\\RedirectIfAuthenticated::class',
            ],
        ];

        // Regular expression to find the protected $middlewareAliases array
        $middlewareAliasesRegex = '/(protected \$middlewareAliases\s?=\s?\[)([^\]]*)(\])/';

        // Search for the middlewareAliases array in the kernel content
        preg_match($middlewareAliasesRegex, $kernelContent, $matches);

        if (!empty($matches)) {
            // Get the last element of the middlewareAliases array
            $middlewareAliasesArray = $matches[2];

            // Iterate over the custom middlewares and add them to the end of the array
            foreach ($customMiddlewares as $middleware) {
                $alias = $middleware['alias'];
                $class = $middleware['class'];
                $middlewareAliasesArray .= '' . '    \'' . $alias . '\' => ' . $class . ','.PHP_EOL;
            }

            $middlewareAliasesArray .= '    ';

            // Replace the old middlewareAliases array with the updated one
            $updatedKernelContent = preg_replace($middlewareAliasesRegex, "$1$middlewareAliasesArray$3", $kernelContent);

            // Write the updated contents back to the kernel.php file
            file_put_contents($kernelFilePath, $updatedKernelContent);

            echo "Custom middlewares added successfully!\n";
        } else {
            echo "Middleware Aliases array not found in kernel.php\n";
        }

    }

    private function addNewGuard($guardName, $driver, $provider)
    {
        // Path to the config/auth.php file
        $configFilePath = app_path().'/../config/auth.php';
        // Read the contents of the auth.php file
        $updatedConfigContent = file_get_contents(__DIR__.'/config_sample.php');
        file_put_contents($configFilePath, $updatedConfigContent);
        echo "Guard '{$guardName}' added successfully!\n";

    }

    private function addRoutes() {
        // Define the code to be appended
        $code = <<<CODE

        Route::group(['prefix'=>'admin','as'=>'admin.'], function(){
        
            Route::group(['middleware' => 'admin.auth'], function() {
                Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
            });
        
            Route::group(['middleware' => 'admin.guest'], function() {
                Route::get('/login', [\App\Http\Controllers\Admin\LoginController::class,'login'])->name('login');
                Route::post('/login', [\App\Http\Controllers\Admin\LoginController::class,'checkLogin'])->name('checkLogin');
            });
        
        });
        CODE;

        // Define the file path
        $filePath = app_path().'/../routes/web.php';

        // Check if the file exists
        if (file_exists($filePath)) {
            // Append the code to the end of the file
            file_put_contents($filePath, $code, FILE_APPEND);
            echo "Code added successfully.";
        } else {
            echo "File not found: $filePath";
        }
    }

}
