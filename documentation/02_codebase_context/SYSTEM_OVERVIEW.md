# System Overview

**Concepcion Integrated School (CIS) Attendance Station, Classroom Verification, and Student Information System (`CIS_CAPSTONE`)** is an institutional platform designed for junior and senior high school administration in the Philippines.

---

## 1. Core System Pillars

The platform automates school operations across four foundational pillars:

```text
┌─────────────────────────────────────────────────────────────────────────┐
│                           CIS PLATFORM PILLARS                          │
├───────────────────┬───────────────────┬───────────────────┬─────────────┤
│  1. Self-Service  │ 2. Classroom      │ 3. Trimester      │ 4. Official │
│     QR Station    │    Verification   │    Grading Engine │    DepEd SF │
├───────────────────┼───────────────────┼───────────────────┼─────────────┤
│ • Self-service QR │ • Subject-level   │ • DepEd Order 8   │ • DepEd SF1 │
│   scanning kiosk  │   daily roster    │   compliance      │   (School   │
│ • Live IN/OUT     │ • Missing student │ • Subject category│   Register) │
│   state machine   │   attendance flags│   weighting (WW,  │ • DepEd SF9 │
│ • Instant guardian│ • Teaching        │   PT, QA)         │   (Progress │
│   email alerts    │   assignment      │ • At-Risk score   │   Report    │
│ • Scan remarks &  │   reconciliation  │   early warnings  │   Card)     │
│   cooldown shield │                   │                   │             │
└───────────────────┴───────────────────┴───────────────────┴─────────────┘
```

---

## 2. Key Modules & Functional Boundaries

### 1. Self-Service QR Station (Time-In & Time-Out)
* **Kiosk Operation & Access:** Students scan their own QR badges at an autonomous, self-service camera/scanner terminal. The **Scanner Operator** is not a guard; it is simply a dedicated role account designed to log into the terminal, launch the full-screen kiosk interface (`/qr-station`), and monitor daily time-in/time-out records (`/time-in-time-out-history`).
* **Mechanism:** Students present their QR badge to the station. The backend `AttendanceStateMachine` determines sequence (`IN` vs `OUT`), checks schedule boundaries, enforces a cooldown shield with scan remarks, records `AttendanceLog`, and dispatches automated guardian notifications.

### 2. Classroom Subject Verification
* **Actors:** Subject Teachers.
* **Functionality:** Subject-period attendance verification.
* **Mechanism:** Teachers verify students physically present in their assigned classroom periods (`/teacher/attendance`). The system reconciles station check-ins against classroom rosters to identify students who arrived at the school station but were absent in specific classes.

### 3. Trimester Grading Engine
* **Actors:** Subject Teachers, School Admin.
* **Functionality:** Continuous assessment and periodic grading.
* **Mechanism:** Strictly structured around three grading periods (Trimesters) compliant with DepEd standards. Teachers enter assessment scores across Written Works (WW), Performance Tasks (PT), and Quarterly/Term Assessments (QA). The `GradingService` dynamically calculates weighted initial grades and transmutes them using standard DepEd transmutation tables.

### 4. Student Profiling & DepEd Reporting
* **Actors:** School Admin, Super Admin, Teachers.
* **Functionality:** Official government forms and student record tracking.
* **Mechanism:** Generates DepEd School Form 1 (SF-1 School Register) and School Form 9 (SF-9 Progress Report Card) in downloadable PDF format (`DomPdfWrapper`) and tabular Excel exports (`ExcelSpreadsheetService`).

---

## 3. Real-Time Infrastructure (Laravel Reverb)

Live telemetry is broadcast across WebSocket channels:
* **Attendance Station Feeds:** As students self-scan at the station, attendance logs immediately render on administrative and operator monitoring dashboards without page reloads.
* **Real-Time Stats:** Live counters for campus occupancy, present counts, late arrivals, and early departures.
