<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    //

    public function index()
    {
        $students = Student::where('student_id')->get()->all();
    }

    public function store(Request $request)
    {
        //validate data in input field
        $validatedData = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' =>  ['required', 'string'],
            'sex' => ['required', 'in:female,male'],
            'address' => ['required', 'string'],
            'birthdate' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],

            //for guardian input
            'name' => ['required', 'string'],
            'relationship' => ['required', 'in:mother,father,sibling,guardian'],
            'email' => ['required', 'email']
        ]);


        $studentData = [
            'first_name' => strip_tags($validatedData['first_name']),
            'last_name' => strip_tags($validatedData['last_name']),
            'sex' => $validatedData['sex'],
            'address' => strip_tags($validatedData['address']),
            'birthdate' => $validatedData['birthdate'],
            'status' => $validatedData['status'],
        ];



        $student =  DB::transaction(function () use ($studentData, $validatedData) {
            $student = Student::create($studentData);



            $guardianData = [
                'student_id' => $student->id,
                'name' => strip_tags($validatedData['name']),
                'relationship' => ($validatedData['relationship']),
                'email' => strip_tags($validatedData['email']),
            ];

            Guardian::create($guardianData);

            return $student;
        });

        return response()->json([
            'message' => 'Student and guardian created successfully',
            'student' => $student
        ]);
    }


    public function update(Request $request) {

    
    }




    public function destroy() {}
}
