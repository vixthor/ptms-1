<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SchoolSession;
use App\Repositories\PromotionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->canManagePayments(), 403);
    }

    protected function getCurrentSessionId()
    {
        if (session()->has('browse_session_id')) {
            return session('browse_session_id');
        }

        $latest = SchoolSession::latest()->first();

        return $latest ? $latest->id : 0;
    }

    public function index(Request $request)
    {
        $current_school_session_id = $this->getCurrentSessionId();

        $query = Payment::with(['student', 'recordedBy'])
            ->where('session_id', $current_school_session_id);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->query('student_id'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        $payments = $query->orderByDesc('payment_date')->orderByDesc('id')->get();

        $promotionRepository = new PromotionRepository;
        $students = $current_school_session_id
            ? $promotionRepository->getAllStudentsBySession($current_school_session_id)
            : collect();

        $totalAmount = $payments->where('status', Payment::STATUS_SUCCESS)->sum('amount');

        return view('payments.index', [
            'payments' => $payments,
            'students' => $students,
            'totalAmount' => $totalAmount,
            'current_school_session_id' => $current_school_session_id,
            'categories' => Payment::CATEGORIES,
        ]);
    }

    public function create()
    {
        $current_school_session_id = $this->getCurrentSessionId();

        $promotionRepository = new PromotionRepository;
        $students = $current_school_session_id
            ? $promotionRepository->getAllStudentsBySession($current_school_session_id)
            : collect();

        return view('payments.create', [
            'students' => $students,
            'current_school_session_id' => $current_school_session_id,
            'categories' => Payment::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'student_id' => ['required', 'integer'],
            'category' => ['required', 'string', 'in:'.implode(',', Payment::CATEGORIES)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string'],
            'paid_for' => ['required', 'string'],
            'payment_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        try {
            Payment::create([
                'student_id' => $request->student_id,
                'category' => $request->category,
                'session_id' => $request->session_id,
                'recorded_by' => Auth::id(),
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'gateway' => 'manual',
                'status' => Payment::STATUS_SUCCESS,
                'paid_for' => $request->paid_for,
                'payment_date' => $request->payment_date,
                'note' => $request->note,
            ]);

            return redirect()->route('payments.list')->with('status', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}
