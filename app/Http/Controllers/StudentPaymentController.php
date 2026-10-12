<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SchoolSession;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentPaymentController extends Controller
{
    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->role === 'student', 403);
    }

    protected function getCurrentSessionId()
    {
        $latest = SchoolSession::latest()->first();

        return $latest ? $latest->id : 0;
    }

    public function index()
    {
        $payments = Payment::where('student_id', Auth::id())
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        $totalPaid = $payments->where('status', Payment::STATUS_SUCCESS)->sum('amount');

        return view('payments.my.index', [
            'payments' => $payments,
            'totalPaid' => $totalPaid,
            'paystackConfigured' => (new PaystackService)->isConfigured(),
        ]);
    }

    public function create()
    {
        return view('payments.my.create', [
            'categories' => Payment::CATEGORIES,
            'paystackConfigured' => (new PaystackService)->isConfigured(),
        ]);
    }

    public function initialize(Request $request, PaystackService $paystack)
    {
        $validate = Validator::make($request->all(), [
            'category' => ['required', 'string', 'in:'.implode(',', Payment::CATEGORIES)],
            'amount' => ['required', 'numeric', 'min:1'],
            'paid_for' => ['required', 'string'],
        ]);

        if ($validate->fails()) {
            return redirect()->route('student.payments.create')->withErrors($validate)->withInput();
        }

        if (! $paystack->isConfigured()) {
            return redirect()->route('student.payments.create')
                ->with('error', 'Online payments are not configured yet. Please contact the school office.');
        }

        $user = Auth::user();
        $reference = 'ptms_'.Str::uuid()->toString();

        $payment = Payment::create([
            'student_id' => $user->id,
            'category' => $request->category,
            'session_id' => $this->getCurrentSessionId(),
            'recorded_by' => $user->id,
            'amount' => $request->amount,
            'payment_method' => 'Card',
            'gateway' => 'paystack',
            'status' => Payment::STATUS_PENDING,
            'reference' => $reference,
            'paid_for' => $request->paid_for,
            'payment_date' => now()->toDateString(),
        ]);

        $result = $paystack->initialize(
            $user->email,
            (float) $payment->amount,
            $reference,
            route('student.payments.callback'),
            ['payment_id' => $payment->id, 'student_id' => $user->id]
        );

        if (! $result['ok']) {
            $payment->update(['status' => Payment::STATUS_FAILED]);

            return redirect()->route('student.payments.create')
                ->with('error', data_get($result, 'body.message', 'Could not start the payment. Please try again.'));
        }

        return redirect()->away(data_get($result, 'body.data.authorization_url'));
    }

    public function callback(Request $request, PaystackService $paystack)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (! $reference) {
            return redirect()->route('student.payments.list')->with('error', 'Missing payment reference.');
        }

        $payment = Payment::where('reference', $reference)
            ->where('student_id', Auth::id())
            ->first();

        if (! $payment) {
            return redirect()->route('student.payments.list')->with('error', 'Payment record not found.');
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return redirect()->route('student.payments.list')->with('status', 'Payment already confirmed.');
        }

        $result = $paystack->verify($reference);
        $verifiedStatus = data_get($result, 'body.data.status');

        if ($result['ok'] && $verifiedStatus === 'success') {
            $payment->update(['status' => Payment::STATUS_SUCCESS]);

            return redirect()->route('student.payments.list')->with('status', 'Payment successful. Thank you!');
        }

        $payment->update(['status' => Payment::STATUS_FAILED]);

        return redirect()->route('student.payments.list')->with('error', 'Payment could not be verified. If you were charged, please contact the school office.');
    }
}
