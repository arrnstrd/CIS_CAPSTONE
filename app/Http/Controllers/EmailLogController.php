<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailLogController extends Controller
{
    //

    public function index(){
        $logs = EmailLog::latest()->get();
        return response()->json($logs);
    }



    public function store(Request $request)
    {
        //valiadting data
        $data = $request->validate([
            ' student_id' => ['required' , 'integer' ],
            'email' => ['required' , 'string'],
            'scan_type'=> ['required' ,  'in:IN,OUT,RE_ENTRY, RE_EXIT'],
            'status' => ['required' , 'in:pending,sent, failed']
        ]);


        $emailLog = EmailLog::create($data);

        return response()->json([
            'messaage' => 'Email log created succesfully',
            'data' => $emailLog
        ]);        
    }




    //for resend  feature
    private function resendEmail($emailLog){
        try{
             Mail::raw(
                    "Student ID{$emailLog->student_id} gate attendance  recorded at " . now(),
                    function ($message) use ($emailLog){
                            $message->to($emailLog->email)
                                ->subject ('CIS Gate Scan Notification');
                    }
                );

            //upate
            $emailLog->update([
                'status' => 'sent',
                'attmpt_count' => $emailLog->attmpt_count + 1
            ]);


            return true;           
        }

        //failed
        catch(\Exception $e){
            $emailLog->update([
                'status' => 'failed',
                'attempt_count'=> $emailLog->attempt_count + 1
            ]);

        return false;
        }
    }




    // for resend single email
    public function retry($id){
        $emailLog= EmailLog::find($id);

        if(!$emailLog){
            return response()->json([
                'message' => 'Email log not found'
            ], 404);
        }

        $this->resendEmail($emailLog);


        return response()->json([
            'message' => 'Retry attempted',
            'data' => $emailLog
        ]);
            
    }



    //for resend all

      public function retryAll(){
        $failedLogs = EmailLog::where('status' , 'failed')->get();
        

        $success = 0;
        $failed = 0 ;

        foreach($failedLogs as $log){
            $result = $this->resendEmail($log);

            if($result){
                $success++;
            }else{
                $failed++;
            }
        }

        return response()->json([
            'message'=> 'Retry process completed',
            'success_count' => $success,
            'failed_count' =>$failed
        ]);
    }
}