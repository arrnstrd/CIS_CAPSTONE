<?php



namespace App\Http\Controllers\SchoolAdmin\Teachers;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;


class TeacherManagementController extends Controller
{
    private function validationRules(?User $user = null): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($user?->id),
            ],


        ];
    }


    public function index(Request $request)
    {
        $teachers = Teacher::with([
            'user:id,employee_id,first_name,last_name,email'
        ])
            ->search($request->search)
            ->filterStatus($request->status)
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pov.school-admin.teachers.teachers', compact('teachers'));
    }


    public function show(Teacher $teacher)
    {
        $teacher->load([
            'user',
            'teachingAssignments.subject',
            'teachingAssignments.section',
            'teachingAssignments.schoolYear',
            'advisedSections',
        ]);

        $subjects = Subject::orderBy('name')->get();
        $sections = Section::where('status', 'active')->orderBy('name')->get();
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();

        return view('pov.school-admin.teachers.show', compact(
            'teacher',
            'subjects',
            'sections',
            'schoolYears',
        ));
    }


    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->validationRules()
        );

        try {

            $teacher = DB::transaction(function () use ($validated) {

                $user = User::create([
                    'first_name' => ucwords(strtolower(trim($validated['first_name']))),
                    'last_name' => ucwords(strtolower(trim($validated['last_name']))),
                    'email' => strtolower(trim($validated['email'])),
                    'role' => 'teacher',
                    'status' => 'pending',
                    'password' => null,
                ]);

                // Generate invitation (plaintext token exists only in memory)
                $invitationService = app(InvitationService::class);
                $result = $invitationService->generate($user);

                // Create teacher within same transaction
                return [
                    'user' => $user,
                    'teacher' => Teacher::create([
                        'user_id' => $user->id,
                        'status' => 'pending',
                    ]),
                    'result' => $result,
                ];
            });

            // Send invitation email AFTER transaction commits successfully
            $result = $teacher['result'];
            $setupLink = $this->buildSetupLink($result['plainToken']);
            $expiresAt = $result['invitation']->expires_at->format('Y-m-d H:i:s');

            Mail::to($teacher['user']->email)->send(new InvitationMail(
                $teacher['user']->first_name . ' ' . $teacher['user']->last_name,
                $setupLink,
                $expiresAt
            ));

            return response()->json([
                'message' => 'Teacher created successfully. Invitation sent to email.',
                'data' => $teacher['teacher']->fresh()->load('user'),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Teacher creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $isDuplicate = str_contains(strtolower($e->getMessage()), 'unique') || str_contains(strtolower($e->getMessage()), 'duplicate') || ($e instanceof QueryException && (string)$e->getCode() === '23505');
            $msg = $isDuplicate
                ? 'A teacher with this email address or employee number already exists. Please check the existing records.'
                : 'Unable to create teacher. Please verify the provided information and try again.';

            return response()->json([
                'message' => $msg,
            ], 422);
        }
    }



    public function update(Request $request, string $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);

        $validated = $request->validate(
            $this->validationRules($teacher->user)
        );

        try {

            DB::transaction(function () use ($teacher, $validated) {

                $userData = [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                ];

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $teacher->user->update($userData);

                $teacher->update([
                    'employee_number' => $validated['employee_number'] ?? null,
                    'phone_number' => $validated['phone_number'] ?? null,
                    'specialization' => $validated['specialization'] ?? null,
                ]);
            });

            return response()->json([
                'message' => 'Teacher updated successfully.',
                'data' => $teacher->fresh()->load('user'),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Teacher update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $isDuplicate = str_contains(strtolower($e->getMessage()), 'unique') || str_contains(strtolower($e->getMessage()), 'duplicate') || ($e instanceof QueryException && (string)$e->getCode() === '23505');
            $msg = $isDuplicate
                ? 'A teacher with this email address or employee number already exists. Please check the existing records.'
                : 'Unable to update teacher. Please verify the information and try again.';

            return response()->json([
                'message' => $msg,
            ], 422);
        }
    }


    public function destroy(string $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);

        if ($teacher->status === 'inactive') {
            return response()->json([
                'message' => 'Teacher is already inactive.',
            ], 422);
        }

        try {

            DB::transaction(function () use ($teacher) {

                $teacher->update([
                    'status' => 'inactive',
                ]);

                $teacher->user->update([
                    'status' => 'inactive',
                ]);
            });

            return response()->json([
                'message' => 'Teacher archived successfully.',
                'data' => $teacher->fresh()->load('user'),
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to archive teacher.',
            ], 500);
        }
    }


    public function restore(string $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);

        if ($teacher->status === 'active') {
            return response()->json([
                'message' => 'Teacher is already active.',
            ], 422);
        }

        try {

            DB::transaction(function () use ($teacher) {

                $teacher->update([
                    'status' => 'active',
                ]);

                $teacher->user->update([
                    'status' => 'active',
                ]);
            });

            return response()->json([
                'message' => 'Teacher restored successfully.',
                'data' => $teacher->fresh()->load('user'),
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to restore teacher.',
            ], 500);
        }
    }

    private function buildSetupLink(string $plainToken): string
    {
        $appUrl = config('app.url');

        if (empty($appUrl) || str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $baseUrl = 'https://cis-capstone.onrender.com';
        } else {
            $baseUrl = rtrim($appUrl, '/');
        }

        return $baseUrl . '/setup/' . $plainToken;
    }
}
