<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function report()
    {
        $report_count = Report::selectRaw('user_id, count(user_id) AS total')->groupBy('user_id')->get();
        $reports = Report::with(['users', 'shop'])->withCount('users')->get();
        if (count($reports)) return response()->json(['status' => true, 'Message' => 'Reports found', 'reports' => $reports ?? [], 'count' => $report_count ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Reports not found', 'reports' => $reports ?? []]);
    }


    public function addReport(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'message' => 'required',
            'shop_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $report = new Report();
        $report->user_id = auth()->user()->id;
        $report->shop_id = $request->shop_id;
        $report->reason = $request->message;
        if (!$report->save()) return response()->json(['status' => false, 'Message' => 'Report not Added']);
        else return response()->json(['status' => true, 'Message' => 'Report Added', 'reports' => $report ?? []], 200);
    }


    public function deleteReport($id)
    {
        $report = Report::where('id', $id)->first();
        if (!empty($report)) {
            if ($report->delete()) return response()->json(['status' => true, 'Message' => 'Successfully deleted Report'], 200);
            else return response()->json(["status" => false, 'Message' => 'Report not deleted']);
        } else return response()->json(["status" => false, 'Message' => 'Report not found']);
    }
}
