@echo off

set file-path=%~dp0\
set conf-file=%cd%\config.php
:init_arg
set args=

:get_arg
shift
if "%0"=="" goto :finish_arg
set args=%args% %0
goto :get_arg

:finish_arg

php "%file-path%myo.php" %args% --conf-file %conf-file%