# Resource Checker for Stress Testing
# This script monitors CPU and RAM usage and kills the k6 process if thresholds are exceeded.

$CPU_THRESHOLD = 80
$RAM_THRESHOLD = 80
$PROCESS_NAME = "k6"

Write-Host "Monitoring system resources. Thresholds: CPU $CPU_THRESHOLD%, RAM $RAM_THRESHOLD%" -ForegroundColor Cyan

while ($true) {
    # Get CPU Usage
    $cpuUsage = (Get-WmiObject Win32_Processor | Measure-Object -Property LoadPercentage -Average).Average
    
    # Get RAM Usage
    $computerSystem = Get-WmiObject Win32_ComputerSystem
    $os = Get-WmiObject Win32_OperatingSystem
    $totalRam = $computerSystem.TotalPhysicalMemory
    $freeRam = $os.FreePhysicalMemory * 1024
    $ramUsage = [math]::Round((($totalRam - $freeRam) / $totalRam) * 100, 2)

    Write-Host "CPU: $cpuUsage% | RAM: $ramUsage%"
    
    if ($cpuUsage -gt $CPU_THRESHOLD -or $ramUsage -gt $RAM_THRESHOLD) {
        Write-Host "CRITICAL: Resource threshold exceeded! Aborting k6..." -ForegroundColor Red
        $k6Process = Get-Process -Name $PROCESS_NAME -ErrorAction SilentlyContinue
        if ($k6Process) {
            $k6Process | Stop-Process -Force
            Write-Host "k6 process terminated." -ForegroundColor Yellow
        }
        break
    }
    
    Start-Sleep -Seconds 2
}
