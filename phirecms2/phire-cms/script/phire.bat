@echo off
REM
REM Phire CMS 2.0 BASH CLI script
REM

SETLOCAL ENABLEDELAYEDEXPANSION
SET SCRIPT_DIR=%~dp0

IF NOT "%1" == "sql" (
    php %SCRIPT_DIR%phire.php %*
) ELSE (
    SET DB_CLI=
    SET DB_CLI_DUMP=
    SET DB_CLI_PARAMS=
    SET DB_CLI_DUMP_PARAMS=
    SET DB_INTERFACE=
    SET DB_TYPE=
    SET DB_NAME=
    SET DB_USER=
    SET DB_PASS=
    SET DB_HOST=

    FOR /f "delims=" %%a in ('findstr "DB_INTERFACE" ..\..\config.php') DO SET DB_INTERFACE=%%a
    FOR /f "delims=" %%a in ('findstr "DB_TYPE" ..\..\config.php') DO SET DB_TYPE=%%a
    FOR /f "delims=" %%a in ('findstr "DB_NAME" ..\..\config.php') DO SET DB_NAME=%%a
    FOR /f "delims=" %%a in ('findstr "DB_USER" ..\..\config.php') DO SET DB_USER=%%a
    FOR /f "delims=" %%a in ('findstr "DB_PASS" ..\..\config.php') DO SET DB_PASS=%%a
    FOR /f "delims=" %%a in ('findstr "DB_HOST" ..\..\config.php') DO SET DB_HOST=%%a

    SET DB_INTERFACE=!DB_INTERFACE:~24,-3!
    SET DB_TYPE=!DB_TYPE:~19,-3!
    SET DB_NAME=!DB_NAME:~19,-3!
    SET DB_USER=!DB_USER:~19,-3!
    SET DB_PASS=!DB_PASS:~19,-3!
    SET DB_HOST=!DB_HOST:~19,-3!

    SET MYSQL=false
    SET PGSQL=false
    SET SQLITE=false

    IF "!DB_INTERFACE!" == "Mysqli" SET MYSQL=true
    IF "!DB_TYPE!" == "mysql" SET MYSQL=true

    IF "!DB_INTERFACE!" == "Pgsql" SET PGSQL=true
    IF "!DB_TYPE!" == "pgsql" SET PGSQL=true

    IF "!DB_INTERFACE!" == "Sqlite" SET SQLITE=true
    IF "!DB_TYPE!" == "sqlite" SET SQLITE=true

    IF NOT "!DB_INTERFACE!" == "" (
        IF "!MYSQL!" == "true" (
            REM echo mysql
            FOR /f "delims=" %%a in ('where mysql') DO SET DB_CLI=%%a
            FOR /f "delims=" %%a in ('where mysqldump') DO SET DB_CLI_DUMP=%%a

            IF "%2" == "cli" (
                IF NOT "!DB_CLI!" == "" (
                    "!DB_CLI!" --database=!DB_NAME! --user=!DB_USER! --password=!DB_PASS! --host=!DB_HOST!
                ) ELSE (
                    echo.
                    echo   That database CLI client was not found.
                    echo.
                )
            )
            IF "%2" == "dump" (
                IF NOT "!DB_CLI_DUMP!" == "" (
                    SET TIMESTAMP=%DATE:~-4%-%DATE:~4,2%-%DATE:~7,2%_%TIME:~0,2%-%TIME:~3,2%
                    echo.
                    echo   Dumping Phire CMS 2 Database...
                    "!DB_CLI_DUMP!" --user=!DB_USER! --password=!DB_PASS! --host=!DB_HOST! !DB_NAME! > !DB_NAME!_!TIMESTAMP!.mysql.sql
                    echo   Done!
                    echo.
                ) ELSE (
                    echo.
                    echo   That database CLI dump client was not found.
                    echo.
                )
            )
        )

        IF "!PGSQL!" == "true" (
            FOR /f "delims=" %%a in ('where psql') DO SET DB_CLI=%%a
            FOR /f "delims=" %%a in ('where pg_dump') DO SET DB_CLI_DUMP=%%a
            IF "%2" == "cli" (
                IF NOT "!DB_CLI!" == "" (
                    "!DB_CLI!" --dbname=!DB_NAME! --username=!DB_USER!
                ) ELSE (
                    echo.
                    echo   That database CLI client was not found.
                    echo.
                )
            )
            IF "%2" == "dump" (
                IF NOT "!DB_CLI_DUMP!" == "" (
                    SET TIMESTAMP=%DATE:~-4%-%DATE:~4,2%-%DATE:~7,2%_%TIME:~0,2%-%TIME:~3,2%
                    echo.
                    echo   Dumping Phire CMS 2 Database...
                    "!DB_CLI_DUMP!" --username=!DB_USER! !DB_NAME!> !DB_NAME!_!TIMESTAMP!.pgsql.sql
                    echo   Done!
                    echo.
                ) ELSE (
                    echo.
                    echo   That database CLI dump client was not found.
                    echo.
                )
            )
        )

        IF "!SQLITE!" == "true" (
            FOR /f "delims=" %%a in ('where sqlite3') DO SET DB_CLI=%%a
            FOR /f "delims=" %%a in ('where sqlite3') DO SET DB_CLI_DUMP=%%a
            IF "%2" == "cli" (
                IF NOT "!DB_CLI!" == "" (
                    SET DB_NAME=!DB_NAME:~10!
                    "!DB_CLI!" "../..!DB_NAME!"
                ) ELSE (
                    echo.
                    echo   That database CLI client was not found.
                    echo.
                )
            )
            IF "%2" == "dump" (
                IF NOT "!DB_CLI_DUMP!" == "" (
                    SET DB_NAME=!DB_NAME:~10!
                    SET TIMESTAMP=%DATE:~-4%-%DATE:~4,2%-%DATE:~7,2%_%TIME:~0,2%-%TIME:~3,2%
                    echo.
                    echo   Dumping Phire CMS 2 Database...
                    "!DB_CLI_DUMP!" "../..!DB_NAME!" .dump > phirecms_!TIMESTAMP!.sqlite.sql"
                    echo   Done!
                    echo.
                ) ELSE (
                    echo.
                    echo   That database CLI dump client was not found.
                    echo.
                )
            )
        )
    ) ELSE (
        echo.
        echo Phire CMS 2 CLI
        echo ===============
        echo.
        echo   Phire CMS 2 does not appear to be installed. Please check the config file or install the application.
        echo.
    )
)

ENDLOCAL
