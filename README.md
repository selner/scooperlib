#scooperlib
Shared library of classes & functions for use with Scooper and Jobs_Scooper.  

To use, add the following to your composer.json:
    "repositories":[
        {
            "type":"git",
            "url":"http://github.com/selner/scooperlib"
        },

        {
            "type":"package",
            "package":{
                "name":"selner/scooperlib",
                "version":"master",
                "source":{
                    "type":"git",
                    "url":"http://github.com/selner/scooperlib",
                    "reference":"master"
                }

            }
        }
    ],
    "require": {
        "selner/scooperlib": "master"
   }


#Notes
* Version:  v1.0
* Author:  Bryan Selner (dev at recoilvelocity dot com)
* Platforms tested:
** Mac OS/X 10.9.2 with PHP 5.4.24.
** Ubuntu Linux 14.04 with PHP 5.5.9-1ubuntu4.2 (with E_NOTICE error reporting disabled.)
** Your mileage could definitely vary on any other platform or version.

* Issues/Bugs:  See [https://github.com/selner/scooper/issues](https://github.com/selner/scooper/issues)

##License
This product is licensed under the GPL (http://www.gnu.org/copyleft/gpl.html). It comes with no warranty, expressed or implied.
