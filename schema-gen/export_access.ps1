<#
  旧ASP(Access)の .mdb から、load_data.php が読み込むCSV群を出力する。
  一度きりの移行支援ツール。PowerShell 5.1 (Windows PowerShell) で実行すること
  （ADODB COM を使うため。pwsh 7 でも動くが 32/64bit と ACE プロバイダの
   ビット数を合わせる必要がある）。

  使い方:
    powershell -ExecutionPolicy Bypass -File schema-gen\export_access.ps1 -OutDir <出力先>
#>
param(
  [string]$OutDir = "C:\Users\user\AppData\Local\Temp\claude\C--inetpub-wwwroot-cto-asp\afb0ac0d-48b5-45c1-8098-a0c7626ea39c\scratchpad",
  [string]$DataDir = "C:\inetpub\wwwroot\cto-asp\data"
)

$ErrorActionPreference = "Stop"

$businessTables = @(
  "category","Change","Content","ContentComment","ContentSort","custom","faq","files","filetag",
  "guestbook","guestbookc","link","log","log_OKNG","message","news","otoi","problem","product",
  "relation","risk","RoutineWork","RoutineWorkList","status","Survey","SurveyChoice",
  "SurveyChoiceResult","surveyReplyList","todo","topmenu","wbs"
)

$jobs = @(
  @{ mdb = "www.mdb";     sub = "export_www";     tables = $businessTables },
  @{ mdb = "demo.mdb";    sub = "export_demo";    tables = $businessTables },
  @{ mdb = "miraipm.mdb"; sub = "export_miraipm"; tables = $businessTables },
  @{ mdb = "UserDB-now.mdb"; sub = "export_userdb"; tables = @("room","lebel","memberroom","member") }
)

function Format-Csv-Value($v) {
  if ($v -eq $null -or $v -is [System.DBNull]) { return "" }
  if ($v -is [datetime]) { return $v.ToString("yyyy-MM-dd HH:mm:ss") }
  if ($v -is [bool])     { return $(if ($v) { "1" } else { "0" }) }
  $s = [string]$v
  if ($s -match '[",\r\n]') { return '"' + ($s -replace '"','""') + '"' }
  return $s
}

$provider = "Microsoft.ACE.OLEDB.16.0"

foreach ($job in $jobs) {
  $mdbPath = Join-Path $DataDir $job.mdb
  $destDir = Join-Path $OutDir $job.sub
  New-Item -ItemType Directory -Force -Path $destDir | Out-Null

  $conn = New-Object -ComObject ADODB.Connection
  $conn.Open("Provider=$provider;Data Source=$mdbPath;")

  foreach ($t in $job.tables) {
    $rs = New-Object -ComObject ADODB.Recordset
    try {
      $rs.Open("SELECT * FROM [$t]", $conn, 3, 1)  # adOpenStatic, adLockReadOnly
    } catch {
      Write-Output ("{0,-22} SKIP (no such table in {1})" -f $t, $job.mdb)
      continue
    }

    $cols = @()
    foreach ($f in $rs.Fields) { $cols += $f.Name }

    $sw = New-Object System.IO.StreamWriter((Join-Path $destDir "$t.csv"), $false, (New-Object System.Text.UTF8Encoding($false)))
    $sw.WriteLine(($cols | ForEach-Object { Format-Csv-Value $_ }) -join ",")

    $n = 0
    while (-not $rs.EOF) {
      $line = @()
      foreach ($c in $cols) { $line += (Format-Csv-Value $rs.Fields.Item($c).Value) }
      $sw.WriteLine($line -join ",")
      $n++
      $rs.MoveNext()
    }
    $sw.Close()
    $rs.Close()
    Write-Output ("{0,-22} {1,6} rows -> {2}" -f $t, $n, (Join-Path $job.sub "$t.csv"))
  }

  $conn.Close()
}

Write-Output "export 完了: $OutDir"
