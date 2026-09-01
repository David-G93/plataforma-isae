$ErrorActionPreference = 'Stop'

$mysqlRoot = Join-Path $env:LOCALAPPDATA 'Programs\MySQL\mysql-8.4.11'
$mysql = Join-Path $mysqlRoot 'bin\mysql.exe'
$envFile = Join-Path $PSScriptRoot '..\..\.env'

if (-not (Test-Path $mysql)) {
    throw "MySQL no está instalado en $mysqlRoot"
}

if (-not (Get-NetTCPConnection -LocalPort 3307 -State Listen -ErrorAction SilentlyContinue)) {
    Write-Host 'MySQL local no está corriendo en 127.0.0.1:3307.'
    exit 1
}

$envMap = @{}
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^\s*([A-Z0-9_]+)=(.*)$') {
        $value = $matches[2].Trim('"')
        $envMap[$matches[1]] = $value
    }
}

try {
    $env:MYSQL_PWD = $envMap.DB_PASSWORD
    & $mysql --no-defaults --host=127.0.0.1 --port=3307 -u $envMap.DB_USERNAME -D $envMap.DB_DATABASE -e "SELECT VERSION() AS version, DATABASE() AS db, @@port AS port;"
} finally {
    Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
}
