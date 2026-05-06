<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    //

    public function storeEmail(Request $request)
    {
        $emailLog=$request->validate([
           'email' => ['string' , 'required'],
           'scan_type'=> ['required' ,'in:IN, OUT. RE_ENTRY, RE_EXIT' ],
           'status' => ['required' , 'in:pending, sent, failed']
        ]);


        $emailLog = EmailLog::where('student_id' , $request->student->id)
                ->where('active')->first();

        return view ('email');


        




    
    }
}
