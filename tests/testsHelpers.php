<?php


function testAllHelpers()
{
    $str = "Location:US, WA, Seattle
                                                                    Team Category:
                                    Global Corporate Teams
                                                                Short Description:
                                Amazon is looking for an energetic and enthusiastic candidate to join the fast paced world of Financial Operations. We’re not an average retailer and this is definitely not your average finance...";
    $ret = \Scooper\strScrub($str, ADVANCED_TEXT_CLEANUP);
    assert(strlen($ret) > 0);
}

function testStrScrub($str)
{


}