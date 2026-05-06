<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Models\Guardian;
use App\Models\QrCode;
use App\Models\ScheduleConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        // validate input
        $request->validate([
            'code' => 'required|string'
        ]);

        // find QR
        $qr = QrCode::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$qr) {
            return response()->json([
                'message' => 'Invalid QR Code'
            ], 404);
        }

        // get student safely
        $student = $qr->student;



        //resolve student
        if (!$student) {
            return response()->json([
                'message' => 'Student not found for this QR'
            ], 404);
        }

        // get schedule config and check if late or not
        $schedule = ScheduleConfig::where('level', $student->level ?? 'hs')
            ->where('session_type', $student->session_type ?? 'morning')
            ->first();

        // late check (safe + readable)
        $isLate = false;

        if ($schedule) {
            $isLate = now()->format('H:i:s') > $schedule->late_threshold;
        }



        //get guardian
        $guardian = Guardian::where('student_id' , $student->id)->first();




        //create email log
        if($guardian && $guardian->email){
            $emailLog = EmailLog::create([
                'student_id' => $student->id,
                'email' => $guardian->email,
                'scan_type' > 'IN',
                'status' => 'pending'

            ]);
      

            //send email   (for revision for the message)
            try{
                Mail::raw(
                    "Student {$student->first_name} scanned at " . now(),
                    function ($message) use ($guardian){
                            $message->to($guardian->email)
                                ->subject ('CIS Gate Scan Notification');
                    }
                );


                // update current status
                $emailLog->upate(['status'=>'sent']);

                }
                
                catch(\Exception $e){
                    $emailLog->update(['status' => 'failed']);
                }
                
                
            
         }//if closing




        // response only (no storage logic here)
        return response()->json([
            'message' => 'Scan successful',
            'student' => $student,
            'late' => $isLate
        ]);
    }
}