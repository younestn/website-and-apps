<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use App\Traits\SettingsTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class ThemeService
{

    use SettingsTrait;
    use FileManagerTrait;

    public function getUploadData(object $request): array
    {
        $tempFolderPath = storage_path('app/temp/');
        if (!File::exists($tempFolderPath)) {
            File::makeDirectory($tempFolderPath);
        }

        $file = $request->file('theme_upload');
        $filename = $file->getClientOriginalName();
        $tempPath = $file->storeAs('temp', $filename);

        $zip = new ZipArchive();
        if ($zip->open(storage_path('app/' . $tempPath)) === TRUE) {

            $genFolderName = explode('/', $zip->getNameIndex(0))[0];
            if ($genFolderName === "__MACOSX") {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    if (strpos($zip->getNameIndex($i), "__MACOSX") === false) {
                        $getThemeFolder = explode('/', $zip->getNameIndex($i))[0];
                        break;
                    }
                }
            }
            $getThemeFolder = explode('.', $genFolderName)[0];

            $zip->extractTo(storage_path('app/temp'));
            $infoPath = storage_path('app/temp/' . $getThemeFolder . '/public/addon/info.php');

            if (File::exists($infoPath)) {
                $extractPath = base_path('resources/themes');
                $zip->extractTo($extractPath);
                $zip->close();

                File::chmod($extractPath . '/' . $getThemeFolder . '/public/addon', 0777);
                $status = 'success';
                $message = translate('theme_upload_successfully');
            } else {
                File::cleanDirectory(storage_path('app/temp'));
                $status = 'error';
                $message = translate('invalid_theme');
            }
        } else {
            $status = 'error';
            $message = translate('theme_upload_fail');
        }

        if (File::exists(base_path('resources/themes/__MACOSX'))) {
            File::deleteDirectory(base_path('resources/themes/__MACOSX'));
        }

        File::cleanDirectory(storage_path('app/temp'));

        return [
            'status' => $status,
            'message' => $message
        ];
    }

    public function getPublishData(object $request): array
    {
        $themeInfo = include(base_path('resources/themes/' . $request['theme'] . '/public/addon/info.php'));
        if ($request['theme'] != 'default' && (empty($themeInfo['purchase_code']) || empty($themeInfo['username']) || $themeInfo['is_active'] == '0')) {
            $theme = $request['theme'];
            return [
                'flag' => 'inactive',
                'view' => view('admin-views.system-setup.themes.theme-activate-modal-data', compact('themeInfo', 'theme'))->render(),
            ];
        }

        $currentTheme = theme_root_path();
        $currentThemeRoutes = include(base_path('resources/themes/' . $currentTheme . '/public/addon/theme_routes.php'));
        $this->setEnvironmentValue('WEB_THEME', $request['theme']);

        $reloadAction = 1;
        $informationModal = '';
        if (is_file(base_path('resources/themes/' . $request['theme'] . '/public/addon/theme_routes.php'))) {
            $themeRoutes = include(base_path('resources/themes/' . $request['theme'] . '/public/addon/theme_routes.php'));
            $reloadAction = 0;
            $informationModal = view('admin-views.system-setup.themes.theme-information-modal-data', compact('currentTheme', 'themeInfo', 'themeRoutes', 'currentThemeRoutes'))->render();
        }

        Artisan::call('optimize:clear');
        Artisan::call('view:clear');
        cacheRemoveByType(type: 'banners');

        return [
            'reload_action' => $reloadAction,
            'informationModal' => $informationModal,
        ];
    }

    public function getActivationData(object $request): bool
    {
        $activationStatus = 0;
        $remove = ["http://", "https://", "www."];
        $url = str_replace($remove, "", url('/'));
        $fullData = include(base_path('resources/themes/' . $request['theme'] . '/public/addon/info.php'));

        $post = [
            base64_decode('dXNlcm5hbWU=') => $request['username'],
            base64_decode('cHVyY2hhc2Vfa2V5') => $request['purchase_code'],
            base64_decode('ZG9tYWlu') => $url,
        ];

        $response = Http::post(base64_decode('aHR0cHM6Ly9jaGVjay42YW10ZWNoLmNvbS9hcGkvdjEvZG9tYWluLXJlZ2lzdGVy'), $post)->json();
        $status = base64_decode($response['active']) ?? 1;

        if ((int)$status) {
            $fullData['is_active'] = 1;
            $fullData['username'] = $request['username'];
            $fullData['purchase_code'] = $request['purchase_code'];
            $str = "<?php return " . var_export($fullData, true) . ";";
            file_put_contents(base_path('resources/themes/' . $request['theme'] . '/public/addon/info.php'), $str);
            try {
                if (DOMAIN_POINTED_DIRECTORY == 'public') {
                    file_put_contents(base_path('public/themes/' . $request['theme'] . '/public/addon/info.php'), $str);
                }
            } catch (\Throwable $th) {}
            $activationStatus = 1;
        }

        return $activationStatus;
    }

    public function getNotifySellersData(object $request): array
    {
        return [
            'sent_by' => 'admin',
            'sent_to' => 'seller',
            'title' => 'Theme Changed to ' . ucwords(str_replace('_', ' ', theme_root_path())),
            'description' => 'Theme Changed Description, time - ' . Carbon::now(),
            'image' => $request->has('image') ? $this->upload(dir: 'notification/', format: 'webp', image: $request->file('image')) : null,
            'status' => 1,
            'notification_count' => 1
        ];
    }

    public function deleteTheme(object $request): array
    {
        if (theme_root_path() == $request['theme']) {
            $status = 'error';
            $message = translate("cannot_delete_the_active_theme");
        } else {
            $fullPath = base_path('resources/themes/' . $request['theme']);
            if (File::deleteDirectory($fullPath)) {
                $status = 'success';
                $message = translate("theme_delete_successfully");
            } else {
                $status = 'error';
                $message = translate("theme_delete_fail");
            }
        }

        return [
            'status' => $status,
            'message' => $message
        ];
    }

    function getDirectories(): array
    {
        $scan = scandir(base_path('resources/themes'));
        $themesFolders = array_diff($scan, ['.', '..', '.DS_Store']);

        $themes = [];
        foreach ($themesFolders as $folder) {
            if (file_exists(base_path('resources/themes/' . $folder . '/public/addon/info.php'))) {
                $info = include(base_path('resources/themes/' . $folder . '/public/addon/info.php'));
            } else {
                $info = [];
            }

            $themeRoutes = [];
            if (is_file(base_path('resources/themes/' . $folder . '/public/addon/theme_routes.php'))) {
                $themeRoutes = include(base_path('resources/themes/' . $folder . '/public/addon/theme_routes.php'));
            }
            $info['comfortable_panel_version'] = $themeRoutes['comfortable_panel_version'] ?? '';
            $themes[$folder] = $info;
        }
        return $themes;
    }

}
