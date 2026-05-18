<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operator;
use App\Models\Business;
use Illuminate\Support\Str;

class OperatorController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $operators = Operator::orderBy('full_name')->paginate(20);
        return view('admin.operators.index', compact('operators'));
    }

    public function create()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $businesses = Business::orderBy('legal_name')->pluck('legal_name', 'id');
        return view('admin.operators.create', compact('businesses'));
    }

    public function store(Request $request)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:operators,email',
            'business_id' => 'required|exists:businesses,id',
            'is_owner' => 'nullable|in:yes,no',
            'account_status' => 'required|in:pending_verification,active,suspended,archived',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $op = new Operator();
        $op->operator_id = uniqid('OP');
        $op->user_type = 'Operator';
        $op->full_name = $request->full_name;
        $op->email = $request->email;
        $op->business_id = $request->business_id;
        $op->is_owner = $request->is_owner ?? 'no';
        $op->account_status = $request->account_status;
        if ($request->password) {
            $op->password_hash = bcrypt($request->password);
        }
        $op->save();

        return redirect()->route('admin.operators.index')->with('success', 'Operator created.');
    }

    public function edit(Operator $operator)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $businesses = Business::orderBy('legal_name')->pluck('legal_name', 'id');
        return view('admin.operators.edit', compact('operator', 'businesses'));
    }

    public function update(Request $request, Operator $operator)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:operators,email,' . $operator->id,
            'business_id' => 'required|exists:businesses,id',
            'is_owner' => 'nullable|in:yes,no',
            'account_status' => 'required|in:pending_verification,active,suspended,archived',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $operator->full_name = $request->full_name;
        $operator->email = $request->email;
        $operator->business_id = $request->business_id;
        $operator->is_owner = $request->is_owner ?? 'no';
        $operator->account_status = $request->account_status;
        if ($request->password) {
            $operator->password_hash = bcrypt($request->password);
        }
        $operator->save();

        return redirect()->route('admin.operators.index')->with('success', 'Operator updated.');
    }

    public function select(Operator $operator)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        session(['admin_selected_operator_id' => $operator->id]);
        return redirect()->route('admin.accommodation.index')
            ->with('success', 'Selected operator ' . $operator->full_name . ' for admin accommodation and activity management.');
    }

    public function destroy(Operator $operator)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $operator->delete();
        return redirect()->route('admin.operators.index')->with('success', 'Operator deleted.');
    }
}
