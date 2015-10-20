<?php

namespace Minors\SavesImages;

use Illuminate\Contracts\Auth\Guard;
use Intervention\Image\ImageManager;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;

trait SavesImages
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    private $files;

    /**
     * @param string $image
     * @param Authenticatable|null $creator
     * @param callable|null $callback
     *
     * @return string
     */
    protected function storeImage($image, Authenticatable $creator = null, $callback = null)
    {
        if (is_null($creator)) {
            $creator = app(Guard::class)->user();
        }

        $path = ($creator) ? substr(md5($creator->getAuthIdentifier()), 0, 10) : 'common';
        $files = $this->getFilesystem();

        if (!$files->exists('images/'.$path)) {
            $files->makeDirectory('images/'.$path);
        }

        $path .= '/'.uniqid().'.jpg';
        $image = app(ImageManager::class)->make($image);

        if ($callback) {
            call_user_func($callback, $image);
        }

        $files->put('images/'.$path, $image->encode('jpg', 75));
        return $path;
    }

    /**
     * @param string $image
     */
    protected function removeImage($image)
    {
        $files = $this->getFilesystem();
        if ($files->exists('images/'.$image)) {
            $files->delete('images/'.$image);
        }
    }

    /**
     * Get application filesystem handler.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    private function getFilesystem()
    {
        if (is_null($this->files)) {
            $this->files = app(Filesystem::class);
        }

        return $this->files;
    }
}
