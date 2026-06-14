<?php
namespace   App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller{

    public function index(){
        return AuditLog::orderBy('id', 'desc')->get();
    }

    public function last(){
        return AuditLog::latest()->limit(100)->get();
    }

    public function paginateLast(Request $request){
        $perPage = $request->integer('per_page', 10);
        return AuditLog::latest()->paginate($perPage);
    }
}
