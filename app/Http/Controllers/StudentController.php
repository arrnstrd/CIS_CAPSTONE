<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\QrCode;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class StudentController extends Controller
{
    //

    public function index()
    {
        // $students = Student::where('student_id')->get()->all();
    }

    public function store(Request $request)
    {
        // validate input
        $validatedData = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'sex' => ['required', 'in:female,male'],
            'address' => ['required', 'string'],
            'birthdate' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],

            // guardian input
            'name' => ['required', 'string'],
            'relationship' => ['required', 'in:mother,father,sibling,guardian'],
            'email' => ['required', 'email']
        ]);


        $student = DB::transaction(function () use ($validatedData) {

            // 1. create student first (without student_number)
            $student = Student::create([
                'first_name' => strip_tags($validatedData['first_name']),
                'last_name' => strip_tags($validatedData['last_name']),
                'sex' => $validatedData['sex'],
                'address' => strip_tags($validatedData['address']),
                'birthdate' => $validatedData['birthdate'],
                'status' => $validatedData['status'],
            ]);

            // 2. generate student number using real DB ID
            $studentNumber = 'STU-' . now()->year . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);

            $student->update([
                'student_number' => $studentNumber
            ]);

            // 3. create guardian linked to student
            Guardian::create([
                'student_id' => $student->id,
                'name' => strip_tags($validatedData['name']),
                'relationship' => $validatedData['relationship'],
                'email' => strip_tags($validatedData['email']),
            ]);

            //4. generate Unique qr code 
            do {
                $qrCode = Str::upper(Str::random(10));
            }while (QrCode::where('code' , $qrCode)->exists());

            //5/ stoer qr

            QrCode::create([
                'student_id' => $student->id,
                'code' => $qrCode,
                'is_active' => true
            ]);


            return $student;
        });

        

        return response()->json([
            'message' => 'Student and guardian created successfully',
            'student' => $student,
            // 'qr_code' => $qrCode
        ]);
    }
}
