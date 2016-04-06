<?php

namespace MC\FileSystem\Interfaces;

interface File
{

    public function __construct(string $path);

    // call fopen()
    public function open(string $mode='r+');

    // call fclose()
    public function close();

    // lock with block
    public function lock(bool $block=false);

    // call $this->lock(true);
    public function lockWithBlock();

    // unlock this file
    public function unlock();

    // if is successfully locked by myself
    public function locked();

    // return original fopen handle
    public function originalHandle();

    //callback if file does not exist
    public function ifNotExist(callable $callback);

    // call lock(true), if $content not null then call update()
    public function lockForUpdate(string $content = null, int $length = null);

    // get file content
    public function content();

    // update file( truncate, then write)
    public function update(string $content, int $length = null);

    // append content to file
    public function append(string $content, int $length = null);
}
