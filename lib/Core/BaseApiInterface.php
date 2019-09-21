<?php
namespace Lib\Core;

interface BaseApiInterface
{
    public function run();
    public function responseJson($result, $httStatus = 200);
}