<?php



namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Teacher;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
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

        return view('admin-modules.management.teacher', compact('teachers'));
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
        } catch (QueryException $e) {

            return response()->json([
                'message' => 'Unable to create teacher.',
                'error' => $e->getMessage()
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

                $teacher->user->update([
                    'first_name' => ucwords(strtolower(trim($validated['first_name']))),
                    'last_name' => ucwords(strtolower(trim($validated['last_name']))),
                    'email' => strtolower(trim($validated['email'])),
                ]);
            });

            return response()->json([
                'message' => 'Teacher updated successfully.',
                'data' => $teacher->fresh()->load('user'),
            ]);
        } catch (QueryException $e) {

            return response()->json([
                'message' => 'Unable to update teacher.',
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
