<?php
namespace Websquare\FileBase;

interface iFileBase
{
    public function init($conf); 
    public function put($key,$resource=null);
    public function get($key); 
    public function rename($key1,$key2); 
    public function delete($key); 
    public function copy($key,$key2); 
    public function is_dir($key); 
    public function file_exists($key); 
    public function getimagesize($key); 
    public function filemtime($key); 
    public function getfilepath($key); 
    public function geturl($key); 
    public function upload($key,$key2); 
    public function getimageresource($type,$key); 
}
