<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\NotiSend;
use Error;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function report()
    {
        $report_count = Report::with('users:id,username')->selectRaw('user_id, count(user_id) AS total')->groupBy('user_id')->get();
        if (count($report_count)) return response()->json(['status' => true, 'Message' => 'Reports found', 'count' => $report_count ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Reports not found', 'count' => $report_count ?? []]);
    }

    public function reports($id)
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);
        $reports = Report::with(['users', 'shop'])->where('user_id', $id)->get();
        if (count($reports)) return response()->json(['status' => true, 'Message' => 'Reports found', 'reports' => $reports ?? []], 200);
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
        try {
            DB::beginTransaction();
            $report = new Report();
            $report->user_id = auth()->user()->id;
            $report->shop_id = $request->shop_id;
            $report->reason = $request->message;
            if (!$report->save()) throw new Error("Report not added!");
            $user = User::whereRelation('role', 'name', 'admin')->first();
            $title = 'NEW REPORT';
            $message = 'You have recieved new report';
            $appnot = new AppNotification();
            $appnot->user_id = $user->id;
            $appnot->notification = $message;
            $appnot->navigation = $title;
            $appnot->save();
            NotiSend::sendNotif($user->device_token, $title, $message);
            DB::commit();
            return response()->json(['status' => false, 'Message' => 'Report not Added']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function deleteReport($id)
    {
        $report = Report::where('id', $id)->first();
        if (!empty($report)) {
            if ($report->delete()) return response()->json(['status' => true, 'Message' => 'Successfully deleted Report'], 200);
            else return response()->json(["status" => false, 'Message' => 'Report not deleted']);
        } else return response()->json(["status" => false, 'Message' => 'Report not found']);
    }

    public function deleteAllUserReport($user_id)
    {
        $report = Report::where('id', $user_id)->get();
        if (count($report)) {
            foreach ($report as $key => $value) {
                $value->delete();
            }
            return response()->json(['status' => true, 'Message' => 'Successfully deleted User Reports'], 200);
        } else return response()->json(["status" => false, 'Message' => 'Report not found']);
    }

    public function deleteAllReport()
    {
        $report = Report::all();
        if (count($report)) {
            foreach ($report as $key => $value) {
                $value->delete();
            }
            return response()->json(['status' => true, 'Message' => 'Successfully deleted Reports'], 200);
        } else return response()->json(["status" => false, 'Message' => 'Report not found']);
    }
}
