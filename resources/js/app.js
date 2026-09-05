// Master JS Manifest
import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;

// 1. Global Core & Shell
import "./shared/ajax-crud.js";
import "./sidebar.js";
import "./layout.js";
import "./echo";

// 2. POV: School Admin
import "./pov/school-admin/academic/subject-modal.js";
import "./pov/school-admin/academic/section.js";
import "./pov/school-admin/teaching-assignments/teaching-assignments.js";
import "./pov/school-admin/students/student.js";
import "./pov/school-admin/teachers/teacher.js";
import "./pov/school-admin/schedule-configuration/schedule-config.js";
import "./pov/school-admin/settings/settings.js";
import "./pov/school-admin/bulk-import/import.js";
import "./pov/school-admin/qr-generation/qr-generation.js";
import "./pov/school-admin/time-in-time-out-history/download-excel.js";

// 3. POV: Scanner Operator
import "./pov/scanner-operator/qr-station/qr-station.js";

// 4. POV: Teacher
import "./pov/teacher/attendance/room-attendance.js";
