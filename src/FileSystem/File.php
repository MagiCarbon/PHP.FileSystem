<?php

namespace MC\FileSystem;

use MC\FileSystem\Interfaces\File as FileInterface;

class File implements FileInterface
{
    protected $path;
    protected $handle;
    protected $locked = false;

    /**
     * @param string $path [description]
     *
     * @return MC\FileSystem\File [description]
     */
    public function __construct($path)
    {
        $this->path = $path;

        return $this;
    }

    public function __destruct()
    {
        $this->unlock();
        $this->close();
    }

    /**
     * open file.
     *
     * @param string $mode [description]
     *
     * @return MC\FileSystem\File [description]
     */
    public function open($mode = 'r+')
    {
        $this->handle = fopen($this->path, $mode);

        return $this;
    }

    public function close()
    {
        if ($this->handle) {
            return fclose($this->handle);
        }

        return;
    }

    //lock with block
    public function lock($block = false)
    {
        if ($block) {
            $this->locked = flock($this->handle, LOCK_EX);
        } else {
            $this->locked = flock($this->handle, LOCK_EX | LOCK_NB);
        }

        return $this->locked;
    }

    public function lockWithBlock()
    {
        return $this->lock(true);
    }

    //unlock this file
    public function unlock()
    {
        if ($this->locked) {
            $this->locked = false;

            return flock($this->handle, LOCK_UN);
        }

        return;
    }

    //if is successfully locked by myself
    public function locked()
    {
        return $this->locked;
    }

    // return original fopen handle
    public function originalHandle()
    {
        return $this->handle;
    }

    // callback if file does not exist
    public function ifNotExist(callable $callback)
    {
        if (!file_exists($this->path)) {
            $callback($this);
        }

        return $this;
    }

    // call lock(true)
    public function lockForUpdate($content = null, $length = null)
    {
        $this->lockWithBlock();
        if (!is_null($content)) {
            $this->update($content, $length);
            $this->unlock();
        }

        return $this;
    }

    /**
     * get file content.
     *
     * @return mixed string or bool
     */
    public function content()
    {
        flock($this->handle, LOCK_SH);
        $contents = '';
        while (! feof($this->handle)) {
            $contents .= fread($this->handle, 1048576);
        }
        flock($this->handle, LOCK_UN);
        return $contents;
    }

    public function update($content, $length = null)
    {
        rewind($this->handle);
        ftruncate($this->handle, 0);

        return $this->write($content, $length);
    }

    public function append($content, $length = null)
    {
        fseek($this->handle, 0, SEEK_END);

        return $this->write($content, $length);
    }

    /**
     * write into file.
     *
     * @param string $content [description]
     * @param [type] $length  [description]
     *
     * @return mixed returns the number of bytes written, or FALSE on error.
     */
    private function write($content, $length = null)
    {
        if (!is_null($length)) {
            return fwrite($this->handle, $content, $length);
        } else {
            return fwrite($this->handle, $content);
        }
    }
}
