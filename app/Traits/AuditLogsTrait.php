<?php

namespace App\Traits;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Browser;

trait AuditLogsTrait {
    public function auditLogs($username,$ipAddress,$location,$access_from,$activity)
    {
        $insert_auditLog=AuditLog::create([
            'username' => $username,
            'ip_address' => $ipAddress,
            'location' => $location,
            'access_from' => $access_from,
            'activity' => $activity
        ]);
    }
    public function auditLogsShort($activity)
    {
        AuditLog::create([
            'username' => auth()->user()->email,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'location' => '0',
            'access_from' => Browser::browserName(),
            'activity' => $activity,
        ]);
    }
}