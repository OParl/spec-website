<?php
/**
 * Created by PhpStorm.
 * User: sgraupner
 * Date: 23/11/2016
 * Time: 10:26.
 */

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DownloadVersion
{
    protected $fs = null;

    protected $path = '';

    protected $version = '';

    /**
     * @var Collection available files for the identifier and version
     */
    protected $files = null;

    public function __construct(Filesystem $fs, string $identifier, string $version)
    {
        $this->fs = $fs;
        $this->path = "downloads/{$identifier}/{$version}";
        $this->version = $version;

        if (!$fs->exists($this->path)) {
            throw new FileNotFoundException("No downloads available for identifier {$identifier}");
        }

        $this->files = collect($fs->files("downloads/{$identifier}/{$version}"))
            ->map(function ($filename) use ($fs) {
                return new Download($fs, $filename);
            });
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     *
     * @return bool
     */
    public function isVersion(string $version)
    {
        return strcmp($this->version, $version) === 0;
    }

    /**
     * Return the number of days this download version exists.
     *
     * @return int
     */
    public function getAge()
    {
        $fileCTime = Carbon::createFromTimestamp(filectime(storage_path("app/{$this->path}")));

        return $fileCTime->diffInDays();
    }

    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param string $extension
     *
     * @throws FileNotFoundException
     *
     * @return Download
     */
    public function getFileForExtension(string $extension)
    {
        $file = $this->files->filter(function (Download $file) use ($extension) {
            return Str::endsWith($file->getFilename(), $extension);
        });

        if ($file->count() !== 1) {
            throw new FileNotFoundException("No file found for extension {$extension}");
        }

        return $file->first();
    }
}
