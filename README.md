Schedule CIS

elementary
7:15 - 11:50 am
12:50- 3:30pm

late threshold 
8:00 am
1:20 pm


----------------

hs and shs

morning session
6:00 - 11:50am

afternoon session
12:50 - 5:50pm

late threshold
6:40 am - morning
1:30 pm - afternoon




-----
controllers
✅ ScanController (gate scan)
✅ EmailLog
AttendanceLog
FlaggedScan
QrCode
ScheduleConfig
Student
User
Guardian
Teacher
Enrollment 




scancontroller 
1. find qr
2. get student
3. determine/validate if
    -late
    -duplicate
    -valid
    -early out
4. then call:
    -AttendanceLogController
    -FlaggedScanController
    -EmailLogController





===============================
to do

polish the blades of student management
    -double check the input fields
    -dont forget guardians

create a controller for student
create route
connect to frontend
add student to db


reminder
hash qr into 32-64 strings
================================







---------------------------------------------------
input fields for student (personal info) (blade)
first name
last name
sex
address
birthdate
status

info to show for the table in student management
last name
first name
lrn (leave blank for now)
sex
action (view, edit , delete)
    -view cta -> student profile
--------------------------------------------------