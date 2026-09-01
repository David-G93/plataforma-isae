$ErrorActionPreference = 'Stop'

$mysqlRoot = Join-Path $env:LOCALAPPDATA 'Programs\MySQL\mysql-8.4.11'
$mysqlAdmin = Join-Path $mysqlRoot 'bin\mysqladmin.exe'
$rootPasswordFile = Join-Path $env:LOCALAPPDATA 'MySQL\plataforma-isae\root-password.txt'

if (-not (Test-Path $mysqlAdmin)) {
    throw "MySQL no está instalado en $mysqlRoot"
}

if (-not (Get-NetTCPConnection -LocalPort 3307 -State Listen -ErrorAction SilentlyContinue)) {
    Write-Host 'MySQL local ya estaba detenido.'
    exit 0
}

if (-not (Test-Path $rootPasswordFile)) {
    throw "No se encontró el archivo de contraseña root en $rootPasswordFile"
}

try {
    $env:MYSQL_PWD = (Get-Content -Raw $rootPasswordFile).Trim()
    & $mysqlAdmin --no-defaults --host=127.0.0.1 --port=3307 -u root shutdown
} finally {
    Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
}

Write-Host 'MySQL local detenido.'
