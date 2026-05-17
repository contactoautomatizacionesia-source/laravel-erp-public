<?php

namespace App\Services\IdentityReader;

use Illuminate\Http\UploadedFile;
use Symfony\Component\Process\Process;

class IdentityReaderService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('data_sheets', [
            'python_bin'    => '',
            'runner_script' => '',
            'timeout'       => 120,
            'max_image_size' => 10 * 1024 * 1024,
        ]);
    }
    protected function buildEnv(): array
    {
        $venvBase = $this->resolvePackageDir() . '/data_sheets/venv';

        $systemPath = getenv('PATH') ?: '';

        $path = PHP_OS_FAMILY === 'Windows'
            ? $venvBase . '/Scripts;' . $systemPath
            : $venvBase . '/bin:' . $systemPath;

        $env = [
            'PATH'             => $path,
            'PYTHONHASHSEED'   => '0',
            'PYTHONIOENCODING' => 'utf-8',
            'PYTHONUTF8'       => '1',
        ];

        if (PHP_OS_FAMILY === 'Windows') {
            $env = array_merge($env, $this->buildWindowsEnv());
        }

        return $env;
    }

    protected function buildWindowsEnv(): array
    {
        return [
            'SYSTEMROOT'  => getenv('SYSTEMROOT')  ?: 'C:\\Windows',
            'WINDIR'      => getenv('WINDIR')      ?: 'C:\\Windows',
            'USERNAME'    => getenv('USERNAME')    ?: 'default',
            'USERPROFILE' => getenv('USERPROFILE') ?: 'C:\\Users\\default',
            'APPDATA'     => getenv('APPDATA')     ?: 'C:\\Users\\default\\AppData\\Roaming',
            'LOCALAPPDATA'=> getenv('LOCALAPPDATA')?: 'C:\\Users\\default\\AppData\\Local',
            'TEMP'        => getenv('TEMP')        ?: 'C:\\Windows\\Temp',
            'TMP'         => getenv('TMP')         ?: 'C:\\Windows\\Temp',
        ];
    }

    /**
     * Run OCR process and ALWAYS return a structured response.
     */
    public function executeOcr(string $imagePath, ?string $secondImagePath = null): array
    {
        try {
            $validationError = $this->validateOcrPaths($imagePath, $secondImagePath);
            if ($validationError) {
                return $validationError;
            }

            $runnerScript = $this->resolveRunnerScript();
            $command = [$this->resolvePythonBin(), $runnerScript, $imagePath];
            if ($secondImagePath !== null) {
                $command[] = $secondImagePath;
            }

            $process = new Process(
                command: $command,
                timeout: $this->config['timeout'] ?? 60,
                env: $this->buildEnv(),
            );

            $process->run();

            return $this->parseProcessOutput($process);

        } catch (\Throwable $e) {
            return $this->errorResponse(500, 'UNEXPECTED_ERROR', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function validateOcrPaths(string $imagePath, ?string $secondImagePath): ?array
    {
        $checks = [
            [!file_exists($imagePath),                                     400, 'IMAGE_NOT_FOUND'],
            [$secondImagePath !== null && !file_exists($secondImagePath),  400, 'SECOND_IMAGE_NOT_FOUND'],
        ];

        foreach ($checks as [$failed, $status, $code]) {
            if ($failed) {
                return $this->errorResponse($status, $code);
            }
        }

        return null;
    }

    protected function parseProcessOutput(Process $process): array
    {
        $output = trim($process->getOutput());

        if ($output === '') {
            return $this->errorResponse(500, 'PROCESS_FAILED', [
                'stderr' => trim($process->getErrorOutput()),
            ]);
        }

        $decoded = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->errorResponse(500, 'INVALID_JSON', [
                'raw_output' => $output,
            ]);
        }

        return $decoded;
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    protected function errorResponse(int $errorStatus, string $code = '', array $data = []): array
    {
        return [
            'status' => $errorStatus,
            'error_code' => $code,
            'data' => !empty($data) ? $data : null,
        ];
    }

    protected function resolvePythonBin(): string
    {
        if (!empty($this->config['python_bin'])) {
            return $this->config['python_bin'];
        }

        $venvBase = $this->resolvePackageDir() . '/data_sheets/venv';

        $bin = PHP_OS_FAMILY === 'Windows'
            ? $venvBase . '/Scripts/python.exe'
            : $venvBase . '/bin/python';

        if (file_exists($bin)) {
            return $bin;
        }

        return 'python3';
    }

    protected function resolveRunnerScript(): string
    {
        if (!empty($this->config['runner_script'])) {
            return $this->config['runner_script'];
        }

        return $this->resolvePackageDir() . '/data_sheets/ocr_runner.py';
    }

    protected function deleteFile(?string $path): void
    {
        if ($path && file_exists($path)) {
            unlink($path);
        }
    }

    protected function resolvePackageDir(): string
    {
        return __DIR__;
    }

    protected function validateImageFile(UploadedFile $file): ?array
    {
        $maxBytes = $this->config['max_image_size'] ?? 10 * 1024 * 1024;
        $allowed  = ['image/jpeg', 'image/png'];

        if ($file->getSize() > $maxBytes) {
            return $this->errorResponse(400, 'IMAGE_TOO_LARGE');
        }

        if (!in_array($file->getMimeType(), $allowed, true)) {
            return $this->errorResponse(400, 'IMAGE_INVALID_FORMAT');
        }

        return null;
    }

    protected function validateUploadedFiles(?UploadedFile $frontalFile, ?string $frontalFilePath, ?UploadedFile $reversoFile): ?array
    {
        if (!$frontalFile && !$frontalFilePath) {
            return $this->errorResponse(400, 'NOT_IMAGES_FOUND');
        }

        foreach (array_filter([$frontalFile, $reversoFile]) as $file) {
            $error = $this->validateImageFile($file);
            if ($error) {
                return $error;
            }
        }

        return null;
    }

    public function processImage(?UploadedFile $frontalFile = null, ?string $frontalFilePath = null, ?UploadedFile $reversoFile = null): array
    {
        $validationError = $this->validateUploadedFiles($frontalFile, $frontalFilePath, $reversoFile);
        if ($validationError) {
            return $validationError;
        }

        $frenteAbsolute = null;
        $frentePathForView = null;

        // 1. Manejo del Frente
        if ($frontalFile) {
            $frentePath = $frontalFile->store('temp', 'local');
            $frenteAbsolute = storage_path('app/' . $frentePath);
            $frentePathForView = $frentePath;
        } elseif ($frontalFilePath) {
            $allowedBase = storage_path('app/temp');
            $resolved    = realpath($allowedBase . '/' . $frontalFilePath);

            if ($resolved === false || !str_starts_with($resolved, $allowedBase . DIRECTORY_SEPARATOR)) {
                return $this->errorResponse(400, 'IMAGE_INVALID_PATH');
            }

            $frenteAbsolute    = $resolved;
            $frentePathForView = $frontalFilePath;
        }

        // 2. Manejo del Reverso
        $reversoAbsolute = null;
        if ($reversoFile) {
            $reversoPath = $reversoFile->store('temp', 'local');
            $reversoAbsolute = storage_path('app/' . $reversoPath);
        }

        // 3. Procesar con la API
        try {
            $resultado  = $this->executeOcr($frenteAbsolute, $reversoAbsolute);
            $errorCode  = $resultado['error_code'] ?? null;

            if ($errorCode === 'BACK_ID_NOT_FOUND') {
                $resultado['savedFrente'] = $frentePathForView;
            } else {
                $this->deleteFile($frenteAbsolute);
            }

            return $resultado;
        } finally {
            // El reverso siempre se limpia, ocurra lo que ocurra
            $this->deleteFile($reversoAbsolute);
        }
    }
}
