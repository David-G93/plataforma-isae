$ErrorActionPreference = 'Stop'

$mysqlRoot = Join-Path $env:LOCALAPPDATA 'Programs\MySQL\mysql-8.4.11'
$dataRoot = Join-Path $env:LOCALAPPDATA 'MySQL\plataforma-isae'
$dataDir = Join-Path $dataRoot 'data'
$logDir = Join-Path $dataRoot 'logs'
$runDir = Join-Path $dataRoot 'run'
$mysqld = Join-Path $mysqlRoot 'bin\mysqld.exe'
$mysql = Join-Path $mysqlRoot 'bin\mysql.exe'
$logFile = Join-Path $logDir 'mysql-error.log'
$pidFile = Join-Path $runDir 'mysqld.pid'
$envFile = Join-Path $PSScriptRoot '..\..\.env'

if (-not (Test-Path $mysqld)) {
    throw "MySQL no está instalado en $mysqlRoot"
}

if (-not (Test-Path $envFile)) {
    throw "No se encontró el archivo .env del proyecto."
}

$envMap = @{}
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^\s*([A-Z0-9_]+)=(.*)$') {
        $value = $matches[2].Trim('"')
        $envMap[$matches[1]] = $value
    }
}

New-Item -ItemType Directory -Force -Path $logDir, $runDir | Out-Null

if (Get-NetTCPConnection -LocalPort 3307 -State Listen -ErrorAction SilentlyContinue) {
    Write-Host 'MySQL local ya estaba corriendo en 127.0.0.1:3307.'
} else {
    Start-Process -FilePath $mysqld -ArgumentList @(
        "--basedir=$mysqlRoot",
        "--datadir=$dataDir",
        '--bind-address=127.0.0.1',
        '--port=3307',
        '--mysqlx=0',
        '--character-set-server=utf8mb4',
        '--collation-server=utf8mb4_unicode_ci',
        "--log-error=$logFile",
        "--pid-file=$pidFile"
    ) -WindowStyle Hidden | Out-Null
}

$ready = $false
for ($attempt = 0; $attempt -lt 20; $attempt++) {
    try {
        $env:MYSQL_PWD = $envMap.DB_PASSWORD
        & $mysql --no-defaults --host=127.0.0.1 --port=3307 -u $envMap.DB_USERNAME -D $envMap.DB_DATABASE -e "SELECT 1;" | Out-Null
        $ready = $true
        break
    } catch {
        Start-Sleep -Seconds 1
    } finally {
        Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    }
}

if (-not $ready) {
    throw "MySQL no respondió a tiempo. Revisá $logFile"
}

Write-Host 'MySQL local listo en 127.0.0.1:3307.'
