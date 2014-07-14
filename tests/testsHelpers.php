<?php


function testAllHelpers()
{

    testFilePathParse("/users/", true, false);
    testFilePathParse("/users/bob", true, false);
    testFilePathParse("/users/bob.csv", true, true);
    testFilePathParse("users", true, false);
    testFilePathParse("users/", true, false);
    testFilePathParse("/users.csv", true, true);
    testFilePathParse("users.csv", false, true);
    testFilePathParse("/users/bob/tim.csv", true, true);
    testFilePathParse("/users/bob/tim", true, false);
    testFilePathParse("/", true, false);

    testFilePathParse("./users/", true, false);
    testFilePathParse("./users/bob", true, false);
    testFilePathParse("./users/bob.csv", true, true);
    testFilePathParse("../", true, false);
    testFilePathParse("./", true, false);
    testFilePathParse("./users.csv", true, true);
    testFilePathParse("../users.csv", false, true);
    testFilePathParse("./users/bob/tim.csv", true, true);
    testFilePathParse("./users/bob/tim", true, false);
    testFilePathParse("/users/../tim", true, false);
    testFilePathParse("./users/../tim.csv", true, true);



    testStrScrub();
}

function testStrScrub()
{

    $str = "Location:US, WA, Seattle
                                                                    Team Category:
                                    Global Corporate Teams
                                                                Short Description:
                                Amazon is looking for an energetic and enthusiastic candidate to join the fast paced world of Financial Operations. We’re not an average retailer and this is definitely not your average finance...";
    $ret = \Scooper\strScrub($str, ADVANCED_TEXT_CLEANUP);
    assert(strlen($ret) > 0);

}



function testFilePathParse($strPath, $fShouldHaveDir, $fShouldHaveFile)
{
    print("Testing fileParse of " . $strPath . PHP_EOL);
    $details = \Scooper\parseFilePath($strPath);
    if($fShouldHaveDir == true)
    {
        assert($details['has_directory'] == true);
        assert(strlen($details['directory']) > 0);
        if(!$fShouldHaveFile)
            assert(strlen($details['full_file_path']) == 0);
    }

    if($fShouldHaveFile == true)
    {
        assert($details['has_file'] == true);
        assert(strlen($details['file_name']) > 0);
        assert(strlen($details['file_name_base']) > 0);
        assert(strlen($details['file_extension']) > 0);

        if($fShouldHaveFile && $fShouldHaveDir)
            assert(strlen($details['full_file_path']) > 0);
    }

}