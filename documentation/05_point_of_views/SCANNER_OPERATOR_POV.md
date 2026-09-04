# Point of View: Scanner Operator

The **Scanner Operator** is a dedicated role account designed to run the self-service QR scanning station kiosk and monitor daily time-in and time-out logs.

> [!NOTE]
> **Role Definition:** The Scanner Operator is **not a guard or security personnel**. The physical scanning terminal is completely **self-service** (students scan their own QR badges). The operator account exists solely to log into the terminal hardware, launch the fullscreen kiosk view, and provide an administrative view of daily attendance activity.

---

## 1. Interface & Navigation

The Scanner Operator interface is streamlined for terminal operations and log inspection:

```text
SCANNER OPERATOR INTERFACE
├── Dashboard                     ➔ route('scanner_operator.dashboard') ➔ /scanner/dashboard
├── QR Scan Station (Kiosk)       ➔ route('qr-station.index')           ➔ /qr-station
└── In/Out History & Analytics     ➔ route('time-in-time-out-history.index') ➔ /time-in-time-out-history
```

---

## 2. Controller & Route Directory Structure

| Operational View | HTTP Route | Controller Class | Blade View |
|---|---|---|---|
| **Kiosk Dashboard** | `GET /scanner/dashboard` | `ScannerOperator\Dashboard\DashboardController@index` | `scanner-operator/dashboard/index.blade.php` |
| **Fullscreen QR Station** | `GET /qr-station` | `ScannerOperator\QrStation\QrStationController@index` | `scanner-operator/scan-station/index.blade.php` |
| **Process Self-Scan** | `POST /qr-station/scan` | `ScannerOperator\QrStation\ScanController@scan` | Returns JSON status payload |
| **Daily In/Out History** | `GET /time-in-time-out-history` | `ScannerOperator\TimeInTimeOutHistory\AttendanceLogController@index` | `scanner-operator/history/index.blade.php` |
| **Attendance Analytics** | `GET /time-in-time-out-history/analytics` | `ScannerOperator\TimeInTimeOutHistory\AttendanceLogController@analytics` | Analytics Blade |
| **Export Log PDF** | `GET /time-in-time-out-history/download-pdf` | `ScannerOperator\TimeInTimeOutHistory\AttendanceLogController@downloadPdf` | Binary stream (`.pdf`) |

---

## 3. Operational Guarantees & Constraints

1. **Self-Service Station Automation:**
   The kiosk interface maintains constant optical autofocus on camera feeds and accepts USB hardware barcode scanners with automatic submit on newline (`Enter`), requiring no physical operator touch per student.
2. **Access Isolation:**
   Scanner Operator accounts are strictly limited to the kiosk and time-in/out log inspection. Attempts to access teacher gradebooks, student academic records, administrative settings, or user management immediately return an HTTP `403 Forbidden`.
3. **Audited Logging:**
   All logs recorded while the operator station is active record the session metadata for audit traceability.
