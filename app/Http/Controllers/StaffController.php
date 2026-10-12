<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->canManageStaff(), 403);
    }

    /**
     * Permissions granted per designation, drawn from the app's existing
     * permission catalog (see database/seeders/PermissionSeeder.php).
     */
    protected function permissionsFor(string $designation): array
    {
        $adminPermissions = [
            'create school sessions', 'update browse by session', 'create semesters', 'edit semesters',
            'assign teachers', 'create courses', 'view courses', 'edit courses',
            'create classes', 'view classes', 'edit classes',
            'create sections', 'view sections', 'edit sections',
            'create exams', 'view exams', 'create exams rule', 'edit exams rule', 'delete exams rule', 'view exams rule',
            'create routines', 'view routines', 'edit routines', 'delete routines',
            'view marks', 'view academic settings', 'update marks submission window',
            'create users', 'edit users', 'view users', 'promote students',
            'update attendances type', 'view attendances', 'take attendances',
            'create grading systems', 'view grading systems', 'edit grading systems', 'delete grading systems',
            'create grading systems rule', 'view grading systems rule', 'edit grading systems rule', 'delete grading systems rule',
            'create notices', 'view notices', 'edit notices', 'delete notices',
            'create events', 'view events', 'edit events', 'delete events',
            'create syllabi', 'view syllabi', 'edit syllabi', 'delete syllabi',
            'view assignments',
        ];

        return match ($designation) {
            User::ROLE_PROPRIETOR => $adminPermissions,
            User::ROLE_HEADTEACHER => [
                'assign teachers', 'create courses', 'view courses', 'edit courses',
                'create classes', 'view classes', 'edit classes',
                'create sections', 'view sections', 'edit sections',
                'create exams', 'view exams', 'create exams rule', 'edit exams rule', 'delete exams rule', 'view exams rule',
                'create routines', 'view routines', 'edit routines', 'delete routines',
                'view marks', 'view academic settings', 'update marks submission window',
                'create users', 'edit users', 'view users', 'promote students',
                'update attendances type', 'view attendances',
                'create grading systems', 'view grading systems', 'edit grading systems', 'delete grading systems',
                'create grading systems rule', 'view grading systems rule', 'edit grading systems rule', 'delete grading systems rule',
                'create notices', 'view notices', 'edit notices', 'delete notices',
                'create events', 'view events', 'edit events', 'delete events',
                'create syllabi', 'view syllabi', 'edit syllabi', 'delete syllabi',
                'view assignments',
            ],
            User::ROLE_SECRETARY => [
                'create users', 'edit users', 'view users',
                'view attendances', 'take attendances',
                'create notices', 'view notices', 'edit notices', 'delete notices',
            ],
            default => [],
        };
    }

    public function index()
    {
        $staff = User::whereIn('role', User::STAFF_DESIGNATIONS)
            ->orderBy('role')
            ->orderBy('first_name')
            ->get();

        return view('staff.list', ['staff' => $staff]);
    }

    public function create()
    {
        return view('staff.add');
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'designation' => ['required', 'string', 'in:'.implode(',', User::STAFF_DESIGNATIONS)],
            'gender' => ['required', 'string'],
            'nationality' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'zip' => ['required', 'string'],
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                $imageUrl = null;
                if ($request->hasFile('photo')) {
                    $photo = $request->file('photo');
                    $ext = $photo->getClientOriginalExtension();
                    $image = uniqid().'.'.$ext;
                    $imageUrl = 'upload/staff/'.$image;
                    $photo->move(public_path().'/upload/staff/', $image);
                }

                $staff = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'gender' => $request->gender,
                    'nationality' => $request->nationality,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'address2' => $request->address2,
                    'city' => $request->city,
                    'zip' => $request->zip,
                    'photo' => $imageUrl,
                    'role' => $request->designation,
                    'password' => Hash::make($request->password),
                ]);

                $staff->syncPermissions($this->permissionsFor($request->designation));
            });

            return redirect()->route('staff.list')->with('status', 'Staff member added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $staff = User::whereIn('role', User::STAFF_DESIGNATIONS)->findOrFail($id);

        return view('staff.edit', ['staff' => $staff]);
    }

    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'staff_id' => ['required', 'integer'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email,'.$request->staff_id],
            'designation' => ['required', 'string', 'in:'.implode(',', User::STAFF_DESIGNATIONS)],
            'gender' => ['required', 'string'],
            'nationality' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'zip' => ['required', 'string'],
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        try {
            $staff = User::whereIn('role', User::STAFF_DESIGNATIONS)->findOrFail($request->staff_id);

            $staff->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'phone' => $request->phone,
                'address' => $request->address,
                'address2' => $request->address2,
                'city' => $request->city,
                'zip' => $request->zip,
                'role' => $request->designation,
            ]);

            $staff->syncPermissions($this->permissionsFor($request->designation));

            return redirect()->route('staff.list')->with('status', 'Staff member updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}
