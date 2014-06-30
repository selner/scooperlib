<?php
/**
 * Created by PhpStorm.
 * User: bryan
 * Date: 6/29/14
 * Time: 9:01 PM
 */

namespace Scooper;


class ScooperFileInfo {

    private $fileDetails = array ('full_file_path' => '', 'directory' => '', 'file_name' => '', 'file_name_base' => '', 'file_extension' => '');



    function getFullPathFromFileDetails($arrFileDetails, $strPrependToFileBase = "", $strAppendToFileBase = "")
    {
        return $arrFileDetails['directory'] . getFileNameFromFileDetails($arrFileDetails, $strPrependToFileBase, $strAppendToFileBase);

    }

    function getFileNameFromFileDetails($arrFileDetails, $strPrependToFileBase = "", $strAppendToFileBase = "")
    {
        return $strPrependToFileBase . $arrFileDetails['file_name_base'] . $strAppendToFileBase . "." . $arrFileDetails['file_extension'];
    }

    function __construct()
    {
        $this->fileDetails = array ('full_file_path' => '', 'directory' => '', 'file_name' => '', 'file_name_base' => '', 'file_extension' => '');
    }

    function parseFilePath($strFilePath, $fFileMustExist = false)
    {

        if(strlen($strFilePath) > 0)
        {
            if(is_dir($strFilePath))
            {
                $this->$fileDetails['directory'] = $strFilePath;
            }
            else
            {

                // separate into elements by '/'
                $arrFilePathParts = explode("/", $strFilePath);

                if(count($arrFilePathParts) <= 1)
                {
                    $this->$fileDetails['directory'] = ".";
                    $this->$fileDetails['file_name'] = $arrFilePathParts[0];
                }
                else
                {
                    // pop the last element (the file name + extension) into a string
                    $this->fileDetails['file_name'] = array_pop($arrFilePathParts);

                    // put the rest of the path parts back together into a path string
                    $this->fileDetails['directory']= implode("/", $arrFilePathParts);
                }

                if(strlen($this->fileDetails['directory']) == 0 && strlen($this->fileDetails['file_name']) > 0 && file_exists($this->fileDetails['file_name']))
                {
                    $this->fileDetails['directory'] = dirname($this->fileDetails['file_name']);

                }

                if(!is_dir($this->fileDetails['directory']))
                {
                    __log__('Specfied path '.$strFilePath.' does not exist.', \Scooper\C__LOGLEVEL_WARN__);
                }
                else
                {
                    // since we have a directory and a file name, combine them into the full file path
                    $this->fileDetails['full_file_path'] = $this->fileDetails['directory'] . "/" . $this->fileDetails['file_name'];

                    // separate the file name by '.' to break the extension out
                    $arrFileNameParts = explode(".", $this->fileDetails['file_name']);

                    // pop off the extension
                    $this->fileDetails['file_extension'] = array_pop($arrFileNameParts );

                    // put the rest of the filename back together into a string.
                    $this->fileDetails['file_name_base'] = implode(".", $arrFileNameParts );


                    if($fFileMustExist == true && !is_file($this->fileDetails['full_file_path']))
                    {
                        __log__('Required file '.$this->fileDetails['full_file_path'].' does not exist.', C__LOGLEVEL_WARN__);
                    }
                }
            }
        }

        // Make sure the directory part ends with a slash always
        $strDir = $this->fileDetails['directory'];

        if((strlen($strDir) >= 1) && $strDir[strlen($strDir)-1] != "/")
        {
            $this->fileDetails['directory'] = $this->fileDetails['directory'] . "/";
        }

        return $this->fileDetails;

    }

} 