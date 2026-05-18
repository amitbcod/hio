<?php
$root = __DIR__;
$controllers = [
    'app/Http/Controllers/Admin/AccommodationController.php',
    'app/Http/Controllers/Admin/ActivityController.php',
];

foreach ($controllers as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!file_exists($path)) {
        echo "MISSING: $path\n";
        continue;
    }
    $content = file_get_contents($path);

    // Namespace
    $content = preg_replace("#namespace App\\\\Http\\\\Controllers\\\\Operator;#", "namespace App\\Http\\Controllers\\Admin;", $content, 1);

    // Add Operator import if missing
    if (!preg_match('#use App\\\\Models\\\\Operator;#', $content)) {
        $content = preg_replace("#(use App\\\\Models\\\\Business;\s*)#", "use App\\Models\\Operator;\n$1", $content, 1);
    }

    // Add selected operator property and middleware constructor after class declaration
    $content = preg_replace(
        '#class AccommodationController extends Controller\s*\{#',
        "class AccommodationController extends Controller\n{\n    protected $selectedOperator;\n\n    public function __construct()\n    {\n        $this->middleware(function ($request, $next) {\n            $operator = $request->route('operator') ?? null;\n            if (!$operator) {\n                $operatorId = $request->session()->get('admin_selected_operator_id') ?: $request->input('operator_id') ?: $request->query('operator_id');\n                if ($operatorId) {\n                    $operator = Operator::find($operatorId);\n                }\n            }\n            if (!$operator) {\n                return redirect()->route('admin.operators.index')->with('error', 'Please select an operator first.');\n            }\n            if ($operator->account_status !== 'active') {\n                return redirect()->route('admin.operators.index')->with('error', 'Selected operator account must be active.');\n            }\n            if (!$operator->business_id) {\n                return redirect()->route('admin.operators.index')->with('error', 'Selected operator must belong to a business.');\n            }\n            if (!$operator->business || $operator->business->status !== 'active') {\n                return redirect()->route('admin.operators.index')->with('error', 'Selected operator business must be active.');\n            }\n            $request->session()->put('admin_selected_operator_id', $operator->id);\n            $this->selectedOperator = $operator;\n            return $next($request);\n        });\n    }\n\n    protected function operator()\n    {\n        return $this->selectedOperator;\n    }\n\n    protected function authorizeAccommodation(Accommodation $accommodation)\n    {\n        if ($accommodation->operator_id !== $this->operator()->id && $accommodation->business_id !== $this->operator()->business_id) {\n            abort(403);\n        }\n    }\n\n    protected function checkPreconditions()\n    {\n        $operator = $this->operator();\n        if (!$operator || $operator->account_status !== 'active') {\n            return redirect()->route('admin.operators.index')\n                ->with('error', 'Selected operator must be active.');\n        }\n        if (!$operator->business_id) {\n            return redirect()->route('admin.operators.index')\n                ->with('error', 'Selected operator must be linked to a business.');\n        }\n        $business = $operator->business;\n        if (!$business || $business->status !== 'active') {\n            return redirect()->route('admin.operators.index')\n                ->with('error', 'Selected operator business must be active.');\n        }\n        return null;
    }\n    #",
        $content,
        1
    );

    // Replace auth()->user usage
    $content = str_replace('$operator = auth()->user();', '$operator = $this->operator();', $content);

    // Route and view replacements for admin accommodation and operator
    $content = str_replace("return view('operator.accommodation.", "return view('admin.accommodation.", $content);
    $content = str_replace("return redirect()->route('operator.accommodation.", "return redirect()->route('admin.accommodation.", $content);
    $content = str_replace("return redirect()->route('operator.profile'", "return redirect()->route('admin.operators.index'", $content);
    $content = str_replace("return redirect()->route('operator.register.step2'", "return redirect()->route('admin.operators.index'", $content);

    // If no operator import exists, ensure it is present
    $content = str_replace("use App\\Models\\Operator;\nuse App\\Models\\Business;", "use App\\Models\\Operator;\nuse App\\Models\\Business;", $content);

    if (strpos($relative, 'ActivityController.php') !== false) {
        // Replace class name match for ActivityController specific insertion
        $content = preg_replace(
            '#class ActivityController extends Controller\s*\{#',
            "class ActivityController extends Controller\n{\n    protected $selectedOperator;\n\n    public function __construct()\n    {\n        $this->middleware(function ($request, $next) {\n            $operator = $request->route('operator') ?? null;\n            if (!$operator) {\n                $operatorId = $request->session()->get('admin_selected_operator_id') ?: $request->input('operator_id') ?: $request->query('operator_id');\n                if ($operatorId) {\n                    $operator = Operator::find($operatorId);\n                }\n            }\n            if (!$operator) {\n                return redirect()->route('admin.operators.index')->with('error', 'Please select an operator first.');\n            }\n            if ($operator->account_status !== 'active') {\n                return redirect()->route('admin.operators.index')->with('error', 'Selected operator account must be active.');\n            }\n            if (!$operator->business_id) {\n                return redirect()->route('admin.operators.index')->with('error', 'Selected operator must belong to a business.');\n            }\n            if (!$operator->business || $operator->business->status !== 'active') {\n                return redirect()->route('admin.operators.index')->with('error', 'Selected operator business must be active.');\n            }\n            $request->session()->put('admin_selected_operator_id', $operator->id);\n            $this->selectedOperator = $operator;\n            return $next($request);\n        });\n    }\n\n    protected function operator()\n    {\n        return $this->selectedOperator;\n    }\n\n    protected function checkOperatorOwnership($activity)\n    {\n        if ($activity->operator_id !== $this->operator()->id) {\n            abort(403);\n        }\n    }\n\n    protected function checkPreconditions()\n    {\n        $operator = $this->operator();\n        if (!$operator || $operator->account_status !== 'active') {\n            return redirect()->route('admin.operators.index')\n                ->with('error', 'Selected operator must be active.');\n        }\n        if (!$operator->business_id) {\n            return redirect()->route('admin.operators.index')\n                ->with('error', 'Selected operator must be linked to a business.');\n        }\n        $business = $operator->business;\n        if (!$business || $business->status !== 'active') {\n            return redirect()->route('admin.operators.index')\n                ->with('error', 'Selected operator business must be active.');\n        }\n        return null;
    }\n    #",
            $content,
            1
        );

        // Replace auth in activity controller
        $content = str_replace('$operator = auth()->user();', '$operator = $this->operator();', $content);
        $content = str_replace("return view('operator.activity.", "return view('admin.activity.", $content);
        $content = str_replace("return redirect()->route('operator.activity.", "return redirect()->route('admin.activity.", $content);
        $content = str_replace("return redirect()->route('operator.profile'", "return redirect()->route('admin.operators.index'", $content);
        $content = str_replace("return redirect()->route('operator.register.step2'", "return redirect()->route('admin.operators.index'", $content);
    }

    if (strpos($relative, 'AccommodationController.php') !== false) {
        // Replace route/view names for accommodation controller
        $content = str_replace("return view('operator.accommodation.", "return view('admin.accommodation.", $content);
        $content = str_replace("return redirect()->route('operator.accommodation.", "return redirect()->route('admin.accommodation.", $content);
    }

    // ensure operator import exists in activity controller imports
    if (strpos($relative, 'ActivityController.php') !== false && !preg_match('#use App\\\\Models\\\\Operator;#', $content)) {
        $content = preg_replace("#(use App\\\\Models\\\\ActivitySeoSocial;\s*)#", "use App\\Models\\Operator;\n$1", $content, 1);
    }

    file_put_contents($path, $content);
    echo "Updated: $path\n";
}
