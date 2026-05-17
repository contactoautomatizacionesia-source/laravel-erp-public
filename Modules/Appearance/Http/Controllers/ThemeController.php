<?php

namespace Modules\Appearance\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use \Modules\Appearance\Services\ThemeService;
use Exception;
use ZipArchive;
use Illuminate\Support\Str;
use \Modules\Appearance\Entities\Theme;
use App\Traits\UploadTheme;
use Brian2694\Toastr\Facades\Toastr;
use Modules\UserActivityLog\Traits\LogActivity;

class ThemeController extends Controller
{
    use UploadTheme;

    private const THEME_SUBDIR = '/theme/';

    private const ZIP_MAX_FILES        = 1000;
    private const ZIP_MAX_UNCOMPRESSED = 524288000; // 500 MB
    private const ZIP_MAX_RATIO        = 10;

    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
        $this->middleware('maintenance_mode');
    }

    public function index()
    {
        try {
            $activeTheme = $this->themeService->activeOne();
            $ThemeList = $this->themeService->getAllActive();
            return view('appearance::theme.index', compact('ThemeList', 'activeTheme'));
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }
    public function create()
    {
        try {
            return view('appearance::theme.components.create');
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }
    public function active(Request $request)
    {
        try {
            $this->themeService->isActive($request->only('id'), $request->id);
            $notification = array(
                'messege' => 'Theme Change Successfully.',
                'alert-type' => 'success'
            );
            LogActivity::successLog('Theme activated.');
            return redirect(route('appearance.themes.index'))->with($notification);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'themeZip' => 'required|mimes:zip'
        ],[
            "themeZip.required" => "Theme file is required",
            "themeZip.mimes" => "Only Zip file supported to upload",
        ]);
        try {
            if ($request->hasFile('themeZip')) {
                $random_dir = Str::random(10);
                $json = $this->extractAndReadThemeConfig($request, $random_dir);
                $this->ensureThemeDirectories($json);
                $this->copyThemeFiles($json, $random_dir);
                $this->saveThemeRecord($json);
            }
            if (is_dir('theme') || is_dir('temp')) {
                $this->delete_directory(storage_path('app/theme'));
                $this->delete_directory(storage_path('app/temp'));
            }
            Toastr::success("New Theme Upload Successfully.", 'Success');
            return redirect(route('appearance.themes.index'));
        } catch (Exception $e) {
            if (is_dir('theme') || is_dir('temp')) {
                $this->delete_directory(storage_path('app/theme'));
                $this->delete_directory(storage_path('app/temp'));
            }
            LogActivity::errorLog($e->getMessage());
        }
    }

    private function extractAndReadThemeConfig(Request $request, string $random_dir): array
    {
        $dir = 'theme';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $path = $request->themeZip->store('theme');

        $zip = new ZipArchive;
        $res = $zip->open(storage_path('app/' . $path));

        if ($res === true) {

            $this->validateZipEntries($zip);

            $themeDir = basename(trim($zip->getNameIndex(0), '/'));

            $extractPath = storage_path('app/temp/' . $random_dir . '/theme');

            $zip->extractTo($extractPath);
            $zip->close();
        }

        $str = @file_get_contents(
            storage_path('app/temp/') .
            $random_dir .
            self::THEME_SUBDIR .
            $themeDir .
            '/config.json'
        );

        return json_decode($str, true);
    }

    private function ensureThemeDirectories(array $json): void
    {
        if (empty($json['files'])) {
            return;
        }
        foreach ($json['files'] as $key => $directory) {
            if ($key == 'asset_path' && !is_dir($directory)) {
                mkdir(base_path($directory), 0777, true);
            }
            if ($key == 'view_path' && !is_dir($directory)) {
                mkdir(base_path($directory), 0777, true);
            }
        }
    }

    private function copyThemeFiles(array $json, string $random_dir): void
    {
        if (empty($json['files'])) {
            return;
        }
        foreach ($json['files'] as $key => $file) {
            if ($key == 'asset_path') {
                $src = base_path('storage/app/temp/' . $random_dir . self::THEME_SUBDIR . $json['folder_path'] . '/asset');
                $this->recurse_copy($src, base_path($file));
            }
            if ($key == 'view_path') {
                $src = base_path('storage/app/temp/' . $random_dir . self::THEME_SUBDIR . $json['folder_path'] . '/view');
                $this->recurse_copy($src, base_path($file));
            }
        }
    }

    private function validateZipEntries(ZipArchive $zip): void
    {
        if ($zip->numFiles > self::ZIP_MAX_FILES) {
            $zip->close();
            abort(422, 'Invalid archive: too many entries.');
        }

        $totalUncompressed = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat  = $zip->statIndex($i);
            $entry = $stat['name'];

            if (str_contains($entry, '..') || str_starts_with($entry, '/')) {
                $zip->close();
                abort(422, 'Invalid archive: path traversal detected.');
            }

            $totalUncompressed += $stat['size'];
            if ($totalUncompressed > self::ZIP_MAX_UNCOMPRESSED) {
                $zip->close();
                abort(422, 'Invalid archive: uncompressed size exceeds limit.');
            }

            if ($stat['comp_size'] > 0 && ($stat['size'] / $stat['comp_size']) > self::ZIP_MAX_RATIO) {
                $zip->close();
                abort(422, 'Invalid archive: suspicious compression ratio.');
            }
        }
    }

    private function saveThemeRecord(array $json): void
    {
        $alreadyHas = Theme::where('name', $json['name'])->first();
        if (!$alreadyHas) {
            Theme::create([
                'user_id'     => auth()->user()->id,
                'name'        => $json['name'],
                'title'       => $json['title'],
                'image'       => $json['image'],
                'version'     => $json['version'],
                'folder_path' => $json['folder_path'],
                'live_link'   => $json['live_link'],
                'description' => $json['description'],
                'is_active'   => $json['is_active'],
                'status'      => $json['status'],
                'item_code'   => $json['item_id'],
                'tags'        => $json['tags'],
            ]);
        } else {
            $alreadyHas->description = $json['description'];
            $alreadyHas->version     = $json['version'];
            $alreadyHas->live_link   = $json['live_link'];
            $alreadyHas->tags        = $json['tags'];
            $alreadyHas->save();
        }
    }
    public function show($id)
    {
        try {
            $theme = $this->themeService->showById($id);
            return view('appearance::theme.components.show', compact('theme'));
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }


    public function destroy(Request $request)
    {
        try {
            $this->themeService->delete($request->id);
            LogActivity::successLog('Theme deleted.');
            Toastr::success('Theme Deleted Successfully.', __('common.success'));
            return redirect(route('appearance.themes.index'));
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }
}
