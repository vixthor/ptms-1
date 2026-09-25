<?php

namespace App\Http\Controllers;

use App\Models\FeeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FeeItemController extends Controller
{
    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->canManageWebsite(), 403);
    }

    public function index()
    {
        $fees = FeeItem::orderBy('sort_order')->get();

        return view('website_admin.fee_items.index', ['fees' => $fees]);
    }

    protected function validatePayload(Request $request)
    {
        return Validator::make($request->all(), [
            'program_name' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'string', 'in:Termly,Annually,One-time'],
            'note' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }

    public function store(Request $request)
    {
        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        FeeItem::create([
            'program_name' => $request->program_name,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'note' => $request->note,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('website-admin.fee-items.index')->with('status', 'Fee added successfully.');
    }

    public function update(Request $request, FeeItem $feeItem)
    {
        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $feeItem->update($validate->validated());

        return redirect()->route('website-admin.fee-items.index')->with('status', 'Fee updated successfully.');
    }

    public function destroy(FeeItem $feeItem)
    {
        $feeItem->delete();

        return redirect()->route('website-admin.fee-items.index')->with('status', 'Fee deleted.');
    }
}
